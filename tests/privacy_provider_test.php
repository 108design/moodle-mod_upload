<?php
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
// See LICENSE.md for the full terms.

namespace mod_upload;

use mod_upload\privacy\provider;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy regressions for approved-context and learner isolation.
 *
 * @package mod_upload
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license See LICENSE.md for the full terms.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(provider::class)]
final class privacy_provider_test extends \core_privacy\tests\provider_testcase {
    /**
     * Create a submission with an actual Moodle stored file.
     *
     * @param \stdClass $user Learner.
     * @param \stdClass $activity Upload activity.
     * @return array
     */
    private function submission(\stdClass $user, \stdClass $activity): array {
        global $CFG;
        require_once($CFG->dirroot . '/mod/upload/locallib.php');
        $submission = upload_get_or_create_submission($activity, $user->id);
        $context = \context_module::instance($activity->cmid);
        $file = get_file_storage()->create_file_from_string([
            'contextid' => $context->id, 'component' => 'mod_upload',
            'filearea' => 'submission_files', 'itemid' => $submission->id,
            'filepath' => '/', 'filename' => 'learner-' . $user->id . '.txt',
            'userid' => $user->id,
        ], 'Private submission for ' . $user->id);
        return [$submission, $context, $file];
    }

    /** Discovery reports only contexts and users with submission data. */
    public function test_discovery_is_scoped_to_upload_submissions(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $first = $this->getDataGenerator()->create_module('upload', ['course' => $course->id]);
        $second = $this->getDataGenerator()->create_module('upload', ['course' => $course->id]);
        $alice = $this->getDataGenerator()->create_user();
        $bob = $this->getDataGenerator()->create_user();
        [, $context] = $this->submission($alice, $first);
        $this->submission($bob, $second);
        $this->assertEquals([$context->id], provider::get_contexts_for_userid($alice->id)->get_contextids());
        $users = new userlist($context, 'mod_upload');
        provider::get_users_in_context($users);
        $this->assertEquals([$alice->id], $users->get_userids());
        $users = new userlist(\context_course::instance($course->id), 'mod_upload');
        provider::get_users_in_context($users);
        $this->assertEmpty($users->get_userids());
    }

    /** Exports include actual private files, and exclude other users/contexts. */
    public function test_export_only_includes_the_approved_submission(): void {
        global $DB;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('upload', ['course' => $course->id]);
        $other = $this->getDataGenerator()->create_module('upload', ['course' => $course->id]);
        $alice = $this->getDataGenerator()->create_user();
        $bob = $this->getDataGenerator()->create_user();
        [$submission, $context, $file] = $this->submission($alice, $activity);
        $this->submission($bob, $activity);
        [, $othercontext] = $this->submission($alice, $other);
        $DB->set_field('upload_submissions', 'finalsubmitted', 1, ['id' => $submission->id]);
        $DB->set_field('upload_submissions', 'finalsubmittedat', 1700000000, ['id' => $submission->id]);
        provider::export_user_data(new approved_contextlist($alice, 'mod_upload', [$context->id]));
        $path = [get_string('privacy:path', 'upload')];
        $export = writer::with_context($context);
        $this->assertEquals(get_string('yes'), $export->get_data($path)->finalsubmitted);
        $this->assertNotEmpty($export->get_data($path)->finalsubmittedat);
        $files = $export->get_files($path);
        $this->assertCount(1, $files);
        $this->assertEquals($file->get_content(), reset($files)->get_content());
        $this->assertFalse(writer::with_context($othercontext)->has_any_data());
    }

    /** A user's deletion does not remove another learner or unapproved activity. */
    public function test_user_deletion_preserves_other_data(): void {
        global $DB;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('upload', ['course' => $course->id]);
        $other = $this->getDataGenerator()->create_module('upload', ['course' => $course->id]);
        $alice = $this->getDataGenerator()->create_user();
        $bob = $this->getDataGenerator()->create_user();
        [$a, $context, $afile] = $this->submission($alice, $activity);
        [$b, , $bfile] = $this->submission($bob, $activity);
        [$aother, , $otherfile] = $this->submission($alice, $other);
        provider::delete_data_for_user(new approved_contextlist($alice, 'mod_upload', []));
        $this->assertTrue($DB->record_exists('upload_submissions', ['id' => $a->id]));
        provider::delete_data_for_user(new approved_contextlist($alice, 'mod_upload', [$context->id]));
        $this->assertFalse($DB->record_exists('upload_submissions', ['id' => $a->id]));
        $this->assertFalse(get_file_storage()->get_file_by_id($afile->get_id()));
        $this->assertTrue($DB->record_exists('upload_submissions', ['id' => $b->id]));
        $this->assertTrue($DB->record_exists('upload_submissions', ['id' => $aother->id]));
        $this->assertNotFalse(get_file_storage()->get_file_by_id($bfile->get_id()));
        $this->assertNotFalse(get_file_storage()->get_file_by_id($otherfile->get_id()));
    }

    /** Context expiry removes all submissions, retaining unrelated file areas. */
    public function test_context_deletion_preserves_activity_description(): void {
        global $DB;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('upload', ['course' => $course->id]);
        $alice = $this->getDataGenerator()->create_user();
        [$submission, $context, $file] = $this->submission($alice, $activity);
        $intro = get_file_storage()->create_file_from_string([
            'contextid' => $context->id, 'component' => 'mod_upload', 'filearea' => 'intro',
            'itemid' => 0, 'filepath' => '/', 'filename' => 'instructions.txt',
        ], 'Activity instructions');
        provider::delete_data_for_all_users_in_context(\context_course::instance($course->id));
        $this->assertTrue($DB->record_exists('upload_submissions', ['id' => $submission->id]));
        provider::delete_data_for_all_users_in_context($context);
        $this->assertFalse($DB->record_exists('upload_submissions', ['id' => $submission->id]));
        $this->assertFalse(get_file_storage()->get_file_by_id($file->get_id()));
        $this->assertNotFalse(get_file_storage()->get_file_by_id($intro->get_id()));
        $this->assertTrue($DB->record_exists('upload', ['id' => $activity->id]));
    }

    /** Bulk erasure checks both the selected user list and module type. */
    public function test_bulk_deletion_rejects_foreign_contexts(): void {
        global $DB;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('upload', ['course' => $course->id]);
        $label = $this->getDataGenerator()->create_module('label', ['course' => $course->id]);
        $alice = $this->getDataGenerator()->create_user();
        $bob = $this->getDataGenerator()->create_user();
        [$a, $context, $afile] = $this->submission($alice, $activity);
        [$b, , $bfile] = $this->submission($bob, $activity);
        $foreign = \context_module::instance($label->cmid);
        provider::delete_data_for_all_users_in_context($foreign);
        provider::delete_data_for_users(new approved_userlist($foreign, 'mod_upload', [$alice->id]));
        provider::delete_data_for_users(new approved_userlist($context, 'mod_upload', []));
        $this->assertEquals(2, $DB->count_records('upload_submissions'));
        provider::delete_data_for_users(new approved_userlist($context, 'mod_upload', [$alice->id]));
        $this->assertFalse($DB->record_exists('upload_submissions', ['id' => $a->id]));
        $this->assertFalse(get_file_storage()->get_file_by_id($afile->get_id()));
        $this->assertTrue($DB->record_exists('upload_submissions', ['id' => $b->id]));
        $this->assertNotFalse(get_file_storage()->get_file_by_id($bfile->get_id()));
    }
}
