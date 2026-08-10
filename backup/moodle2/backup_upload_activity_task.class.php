<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

/**
 * Backup task for mod_upload.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   See LICENSE.md for the full terms.
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
        global $CFG;

        $base = preg_quote($CFG->wwwroot, '/');
        $content = preg_replace(
            '/(' . $base . '\/mod\/upload\/index.php\?id=)([0-9]+)/',
            '$@UPLOADINDEX*$2@$',
            $content
        );
        $content = preg_replace(
            '/(' . $base . '\/mod\/upload\/view.php\?id=)([0-9]+)/',
            '$@UPLOADVIEWBYID*$2@$',
            $content
        );

        return $content;
    }
}
