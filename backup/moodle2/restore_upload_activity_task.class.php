<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Restore task for mod_upload.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen <andreas.giesen.ext@nagarro.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/upload/backup/moodle2/restore_upload_stepslib.php');

/**
 * Defines the restore task for an upload activity.
 */
class restore_upload_activity_task extends restore_activity_task {
    /**
     * Define activity-specific restore settings.
     *
     * @return void
     */
    protected function define_my_settings() {
        // This activity has no activity-specific restore settings.
    }

    /**
     * Define the restore steps.
     *
     * @return void
     */
    protected function define_my_steps() {
        $this->add_step(new restore_upload_activity_structure_step('upload_structure', 'upload.xml'));
    }

    /**
     * Define encoded content fields that must be decoded after restore.
     *
     * @return array
     */
    public static function define_decode_contents() {
        return [
            new restore_decode_content('upload', ['intro'], 'upload'),
        ];
    }

    /**
     * Define activity link decoding rules.
     *
     * @return array
     */
    public static function define_decode_rules() {
        return [
            new restore_decode_rule('UPLOADINDEX', '/mod/upload/index.php?id=$1', 'course'),
            new restore_decode_rule('UPLOADVIEWBYID', '/mod/upload/view.php?id=$1', 'course_module'),
        ];
    }
}
