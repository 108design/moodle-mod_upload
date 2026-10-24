<?php
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
// See LICENSE.md for the full terms.

namespace mod_upload\event;

defined('MOODLE_INTERNAL') || die();

/**
 * A staff member deleted a learner submission.
 *
 * @package mod_upload
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license See LICENSE.md for the full terms.
 */
final class submission_deleted extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'upload_submissions';
    }

    public static function get_name() {
        return get_string('eventsubmissiondeleted', 'mod_upload');
    }

    public function get_description() {
        return "The user with id '$this->userid' deleted submission '$this->objectid' " .
            "belonging to user '$this->relateduserid' in course module '$this->contextinstanceid'.";
    }

    public function get_url() {
        return new \moodle_url('/mod/upload/submissions.php', ['id' => $this->contextinstanceid]);
    }

    public static function get_objectid_mapping() {
        return ['db' => 'upload_submissions', 'restore' => \core\event\base::NOT_MAPPED];
    }
}
