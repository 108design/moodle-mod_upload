<?php
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
// See LICENSE.md for the full terms.

namespace mod_upload;

defined('MOODLE_INTERNAL') || die();

/**
 * Native permission, completion and learner-data restore boundaries.
 *
 * @package mod_upload
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license See LICENSE.md for the full terms.
 */
final class security_test extends \advanced_testcase {
    private function fixture(): array {
        global $CFG;
        require_once($CFG->dirroot . '/mod/upload/lib.php');
        require_once($CFG->dirroot . '/mod/upload/locallib.php');
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['enablecompletion' => 1]);
        $activity = $generator->create_module('upload', [
            'course' => $course->id, 'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionuploaddone' => 1, 'completionmode' => UPLOAD_COMPLETION_HASFILE,
        ]);
        $student = $generator->create_user();
        $teacher = $generator->create_user();
        $editor = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        $generator->enrol_user($teacher->id, $course->id, 'teacher');
        $generator->enrol_user($editor->id, $course->id, 'editingteacher');
        $submission = upload_get_or_create_submission($activity, $student->id);
        $context = \context_module::instance($activity->cmid);
        $file = get_file_storage()->create_file_from_string([
            'contextid' => $context->id, 'component' => 'mod_upload', 'filearea' => 'submission_files',
            'itemid' => $submission->id, 'filepath' => '/', 'filename' => 'synthetic.txt', 'userid' => $student->id,
        ], 'Synthetic private submission');
        return [$course, $activity, $context, $student, $teacher, $editor, $submission, $file];
    }

    public function test_read_only_teacher_cannot_delete_submissions(): void {
        global $DB;
        [$course, $activity, $context, $student, $teacher, $editor, $submission, $file] = $this->fixture();
        $this->setUser($teacher);
        $this->assertTrue(has_capability('mod/upload:viewsubmissions', $context));
        $this->assertFalse(has_capability('mod/upload:deletesubmissions', $context));
        $cm = get_coursemodule_from_id('upload', $activity->cmid);
        try {
            \mod_upload\local\submission_manager::delete($activity, $course, $cm, null);
            $this->fail('A read-only teacher deleted submissions');
        } catch (\required_capability_exception $expected) {
            $this->assertTrue($DB->record_exists('upload_submissions', ['id' => $submission->id]));
            $this->assertNotFalse(get_file_storage()->get_file_by_id($file->get_id()));
        }
    }

    public function test_delete_is_activity_scoped_recalculates_completion_and_logs_each_owner(): void {
        global $DB;
        [$course, $activity, $context, $student, $teacher, $editor, $submission, $file] = $this->fixture();
        $otheractivity = $this->getDataGenerator()->create_module('upload', ['course' => $course->id]);
        $foreign = upload_get_or_create_submission($otheractivity, $student->id);
        $cm = get_coursemodule_from_id('upload', $activity->cmid);
        upload_update_user_completion($course, $cm, $student->id);
        $completion = new \completion_info($course);
        $this->assertEquals(COMPLETION_COMPLETE, $completion->get_data($cm, false, $student->id)->completionstate);
        $this->setUser($editor);
        $sink = $this->redirectEvents();
        $this->assertSame(1, \mod_upload\local\submission_manager::delete(
            $activity, $course, $cm, [$submission->id, $foreign->id]));
        $this->assertFalse($DB->record_exists('upload_submissions', ['id' => $submission->id]));
        $this->assertFalse(get_file_storage()->get_file_by_id($file->get_id()));
        $this->assertTrue($DB->record_exists('upload_submissions', ['id' => $foreign->id]));
        $this->assertEquals(COMPLETION_INCOMPLETE, $completion->get_data($cm, false, $student->id)->completionstate);
        $events = array_values(array_filter($sink->get_events(),
            static fn($event) => $event instanceof \mod_upload\event\submission_deleted));
        $this->assertCount(1, $events);
        $this->assertEquals($student->id, $events[0]->relateduserid);
        $this->assertEquals($submission->id, $events[0]->objectid);
        $this->assertSame(0, \mod_upload\local\submission_manager::delete($activity, $course, $cm, null));
        $sink->close();
    }

    public function test_view_logs_native_event_and_completes_view_condition(): void {
        global $DB;
        [$course, $activity, $context, $student] = $this->fixture();
        $DB->set_field('course_modules', 'completionview', 1, ['id' => $activity->cmid]);
        $DB->set_field('upload', 'completionuploaddone', 0, ['id' => $activity->id]);
        rebuild_course_cache($course->id, true);
        $cm = get_coursemodule_from_id('upload', $activity->cmid);
        $this->setUser($student);
        $sink = $this->redirectEvents();
        upload_view($activity, $course, $cm, $context);
        $events = array_values(array_filter($sink->get_events(),
            static fn($event) => $event instanceof \mod_upload\event\course_module_viewed));
        $this->assertCount(1, $events);
        $this->assertEquals($activity->id, $events[0]->objectid);
        $completion = new \completion_info($course);
        $this->assertEquals(COMPLETION_COMPLETE, $completion->get_data($cm, false, $student->id)->completionstate);
        $sink->close();
    }

    public function test_full_backup_restored_without_users_omits_learner_rows_and_files(): void {
        $this->restore_with_users(false);
    }

    public function test_full_backup_restored_with_users_maps_learner_rows_and_files(): void {
        $this->restore_with_users(true);
    }

    private function restore_with_users(bool $users): void {
        global $CFG, $DB, $USER;
        [$course, $activity, $context, $student, $teacher, $editor, $submission, $file] = $this->fixture();
        $this->setAdminUser();
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
        $CFG->backup_file_logger_level = \backup::LOG_NONE;
        $backup = new \backup_controller(\backup::TYPE_1COURSE, $course->id,
            \backup::FORMAT_MOODLE, \backup::INTERACTIVE_NO, \backup::MODE_IMPORT, $USER->id);
        $backup->get_plan()->get_setting('users')->set_status(\backup_setting::NOT_LOCKED);
        $backup->get_plan()->get_setting('users')->set_value(true);
        $backupid = $backup->get_backupid();
        $backup->execute_plan();
        $backup->destroy();
        $target = $this->getDataGenerator()->create_course();
        $restore = new \restore_controller($backupid, $target->id, \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL, $USER->id, \backup::TARGET_CURRENT_ADDING);
        $restore->get_plan()->get_setting('users')->set_status(\backup_setting::NOT_LOCKED);
        $restore->get_plan()->get_setting('users')->set_value($users);
        $this->assertTrue($restore->execute_precheck());
        $restore->execute_plan();
        $restore->destroy();
        $restored = $DB->get_record('upload', ['course' => $target->id], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('upload', $restored->id);
        $restoredcontext = \context_module::instance($cm->id);
        $rows = $DB->get_records('upload_submissions', ['uploadid' => $restored->id]);
        $files = get_file_storage()->get_area_files($restoredcontext->id, 'mod_upload', 'submission_files', false, 'id', false);
        $this->assertCount($users ? 1 : 0, $rows);
        $this->assertCount($users ? 1 : 0, $files);
        if ($users) {
            $this->assertEquals($student->id, reset($rows)->userid);
            $this->assertSame('Synthetic private submission', reset($files)->get_content());
        }
        $this->assertTrue($DB->record_exists('upload_submissions', ['id' => $submission->id]));
        $this->assertNotFalse(get_file_storage()->get_file_by_id($file->get_id()));
    }
}
