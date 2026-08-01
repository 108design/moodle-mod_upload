<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Backup task for mod_upload.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen <andreas.giesen.ext@nagarro.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/upload/backup/moodle2/backup_upload_stepslib.php');

/**
 * Defines the backup task for an upload activity.
 */
class backup_upload_activity_task extends backup_activity_task {
    /**
     * Define activity-specific backup settings.
     *
     * @return void
     */
    protected function define_my_settings() {
        // This activity has no activity-specific backup settings.
    }

    /**
     * Define the backup steps.
     *
     * @return void
     */
    protected function define_my_steps() {
        $this->add_step(new backup_upload_activity_structure_step('upload_structure', 'upload.xml'));
    }

    /**
     * Encode links to this activity for portable backups.
     *
     * @param string $content
     * @return string
     */
    public static function encode_content_links($content) {
        return self::encode_activity_links($content, '/mod/upload/view.php?id=', 'upload');
    }
}
