<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

/**
 * Restore structure step for mod_upload.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   See LICENSE.md for the full terms.
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Restores upload activity data and optional user submissions.
 */
class restore_upload_activity_structure_step extends restore_activity_structure_step {
    /**
     * Define the restore structure.
     *
     * @return array
     */
    protected function define_structure() {
        $paths = [];
        $paths[] = new restore_path_element('upload', '/activity/upload');
        $paths[] = new restore_path_element('upload_submission', '/activity/upload/submissions/submission');

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Restore the activity configuration.
     *
     * @param array $data
     * @return void
     */
    protected function process_upload($data) {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();
        $newitemid = $DB->insert_record('upload', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Restore one learner submission when user data is included.
     *
     * @param array $data
     * @return void
     */
    protected function process_upload_submission($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $olduploadid = $this->get_old_parentid('upload');
        $data->uploadid = $this->get_mappingid('upload', $olduploadid);
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('upload_submissions', $data);
        $this->set_mapping('upload_submission', $oldid, $newitemid, true);
    }

    /**
     * Restore module intro and submission files after records have been mapped.
     *
     * @return void
     */
    protected function after_execute() {
        $this->add_related_files('mod_upload', 'intro', null);
        $this->add_related_files('mod_upload', 'submission_files', 'upload_submission');
    }
}
