<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

namespace mod_upload;
defined('MOODLE_INTERNAL') || die();

/** Native Moodle 5.3 completion and duplicate integration beyond the shipped privacy suite. */
final class completion_integration_test extends \advanced_testcase {
    public function test_native_completion_tracks_file_presence_and_other_learner_isolation(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/upload/locallib.php');
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $activity = $this->getDataGenerator()->create_module('upload', [
            'course' => $course->id, 'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionuploaddone' => 1, 'completionmode' => UPLOAD_COMPLETION_HASFILE,
        ]);
        $alice = $this->getDataGenerator()->create_user();
        $bob = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($alice->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($bob->id, $course->id, 'student');
        $cm = get_fast_modinfo($course)->get_cm($activity->cmid);
        $completion = new \completion_info($course);
        $this->assertEquals(COMPLETION_INCOMPLETE, $completion->get_data($cm, false, $alice->id)->completionstate);
        $custom = new \mod_upload\completion\custom_completion(\cm_info::create($cm, $alice->id), $alice->id);
        $this->assertSame(['completionuploaddone'], $custom->get_available_custom_rules());
        upload_update_user_completion($course, $cm, $alice->id);
        $this->assertEquals(COMPLETION_INCOMPLETE, $completion->get_data($cm, false, $alice->id)->completionstate);
        $submission = upload_get_or_create_submission($activity, $alice->id);
        $context = \context_module::instance($cm->id);
        $file = get_file_storage()->create_file_from_string([
            'contextid' => $context->id, 'component' => 'mod_upload', 'filearea' => 'submission_files',
            'itemid' => $submission->id, 'filepath' => '/', 'filename' => 'synthetic53.txt', 'userid' => $alice->id,
        ], 'Synthetic Moodle 5.3 submission');
        upload_update_user_completion($course, $cm, $alice->id);
        $this->assertEquals(COMPLETION_COMPLETE, $completion->get_data($cm, false, $alice->id)->completionstate);
        $this->assertEquals(COMPLETION_INCOMPLETE, $completion->get_data($cm, false, $bob->id)->completionstate);
        $file->delete();
        $this->assertFalse(upload_submission_has_files($context->id, $submission->id));
        $custom = new \mod_upload\completion\custom_completion(\cm_info::create($cm, $alice->id), $alice->id);
        $this->assertEquals(COMPLETION_INCOMPLETE, $custom->get_state('completionuploaddone'));
        upload_update_user_completion($course, $cm, $alice->id);
        $this->assertEquals(COMPLETION_INCOMPLETE, $completion->get_data($cm, false, $alice->id)->completionstate);
    }

    public function test_native_duplicate_retains_configuration_without_copying_private_submission(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/course/lib.php');
        require_once($CFG->dirroot . '/mod/upload/locallib.php');
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('upload', [
            'course' => $course->id, 'name' => 'Synthetic duplicate', 'maxfiles' => 3,
        ]);
        $alice = $this->getDataGenerator()->create_user();
        $submission = upload_get_or_create_submission($activity, $alice->id);
        $context = \context_module::instance($activity->cmid);
        $file = get_file_storage()->create_file_from_string([
            'contextid' => $context->id, 'component' => 'mod_upload', 'filearea' => 'submission_files',
            'itemid' => $submission->id, 'filepath' => '/', 'filename' => 'private53.txt', 'userid' => $alice->id,
        ], 'Private synthetic submission');
        $cm = get_coursemodule_from_id('upload', $activity->cmid, $course->id, false, MUST_EXIST);
        $duplicate = duplicate_module($course, $cm);
        $this->assertNotEquals($activity->cmid, $duplicate->id);
        $record = $DB->get_record('upload', ['id' => $duplicate->instance], '*', MUST_EXIST);
        $this->assertEquals(3, $record->maxfiles);
        $this->assertEquals(0, $DB->count_records('upload_submissions', ['uploadid' => $duplicate->instance]));
        $this->assertTrue($DB->record_exists('upload_submissions', ['id' => $submission->id]));
        $this->assertNotFalse(get_file_storage()->get_file_by_id($file->get_id()));
    }

    public function test_final_submission_and_reopening_recalculate_completion(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/upload/locallib.php');
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $activity = $this->getDataGenerator()->create_module('upload', [
            'course' => $course->id, 'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionuploaddone' => 1, 'completionmode' => UPLOAD_COMPLETION_FINALSUBMIT,
        ]);
        $learner = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($learner->id, $course->id, 'student');
        $cm = get_fast_modinfo($course)->get_cm($activity->cmid);
        $completion = new \completion_info($course);
        $submission = upload_get_or_create_submission($activity, $learner->id);
        get_file_storage()->create_file_from_string([
            'contextid' => \context_module::instance($cm->id)->id, 'component' => 'mod_upload',
            'filearea' => 'submission_files', 'itemid' => $submission->id,
            'filepath' => '/', 'filename' => 'draft.txt',
        ], 'Synthetic draft');
        upload_update_user_completion($course, $cm, $learner->id);
        $this->assertEquals(COMPLETION_INCOMPLETE, $completion->get_data($cm, false, $learner->id)->completionstate);
        $DB->set_field('upload_submissions', 'finalsubmitted', 1, ['id' => $submission->id]);
        upload_update_user_completion($course, $cm, $learner->id);
        $this->assertEquals(COMPLETION_COMPLETE, $completion->get_data($cm, false, $learner->id)->completionstate);
        $DB->set_field('upload_submissions', 'finalsubmitted', 0, ['id' => $submission->id]);
        upload_update_user_completion($course, $cm, $learner->id);
        $this->assertEquals(COMPLETION_INCOMPLETE, $completion->get_data($cm, false, $learner->id)->completionstate);
    }

    public function test_module_cache_refresh_preserves_description_and_rule_changes(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/upload/lib.php');
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $activity = $this->getDataGenerator()->create_module('upload', [
            'course' => $course->id, 'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionuploaddone' => 1, 'showdescription' => 1,
            'intro' => '<p>Activity description</p>', 'introformat' => FORMAT_HTML,
        ]);
        $cm = get_fast_modinfo($course)->get_cm($activity->cmid);
        $this->assertSame(1, $cm->customdata['customcompletionrules']['completionuploaddone']);
        $this->assertStringContainsString('Activity description', $cm->content);
        $DB->set_field('upload', 'completionuploaddone', 0, ['id' => $activity->id]);
        rebuild_course_cache($course->id, true);
        $cm = get_fast_modinfo($course)->get_cm($activity->cmid);
        $this->assertSame(0, $cm->customdata['customcompletionrules']['completionuploaddone']);
        $custom = new \mod_upload\completion\custom_completion($cm, 2);
        $this->assertSame([], $custom->get_available_custom_rules());
        $DB->set_field('course_modules', 'completion', COMPLETION_TRACKING_MANUAL, ['id' => $cm->id]);
        rebuild_course_cache($course->id, true);
        $cm = get_fast_modinfo($course)->get_cm($activity->cmid);
        $this->assertEmpty($cm->customdata['customcompletionrules'] ?? []);
        $this->assertFalse(upload_get_coursemodule_info((object)['instance' => -1]));
    }
}
