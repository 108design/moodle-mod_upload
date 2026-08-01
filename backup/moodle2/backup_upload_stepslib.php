<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Backup structure step for mod_upload.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen <andreas.giesen.ext@nagarro.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Defines the XML structure for an upload activity backup.
 */
class backup_upload_activity_structure_step extends backup_activity_structure_step {
    /**
     * Define the backup structure.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        $upload = new backup_nested_element('upload', ['id'], [
            'course', 'name', 'intro', 'introformat', 'maxfiles', 'maxbytes', 'allowedtypes',
            'allowresubmission', 'appenduserid', 'appendusername', 'completionmode',
            'completionuploaddone', 'timecreated', 'timemodified',
        ]);
        $submissions = new backup_nested_element('submissions');
        $submission = new backup_nested_element('submission', ['id'], [
            'uploadid', 'userid', 'status', 'finalsubmitted', 'finalsubmittedat', 'timecreated', 'timemodified',
        ]);

        $upload->add_child($submissions);
        $submissions->add_child($submission);

        $upload->set_source_table('upload', ['id' => backup::VAR_ACTIVITYID]);
        if ($userinfo) {
            $submission->set_source_table('upload_submissions', ['uploadid' => backup::VAR_PARENTID]);
        }

        $upload->annotate_files('mod_upload', 'intro', null);
        $submission->annotate_ids('user', 'userid');
        $submission->annotate_files('mod_upload', 'submission_files', 'id');

        return $this->prepare_activity_structure($upload);
    }
}
