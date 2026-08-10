<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

/**
 * Backup structure step for mod_upload.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   See LICENSE.md for the full terms.
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
