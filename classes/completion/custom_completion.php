<?php
// This file is part of Moodle - http://moodle.org/

// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Custom completion handling for Simply Upload activity.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen <andreas.giesen.ext@nagarro.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_upload\completion;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../locallib.php');

use core_completion\activity_custom_completion;

/**
 * Custom completion class for upload.
 */
class custom_completion extends activity_custom_completion {
    /**
     * Defines custom completion rules.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        return ['completionuploaddone'];
    }

    /**
     * Returns completion state for provided rule.
     *
     * @param string $rule
     * @return int
     */
    public function get_state(string $rule): int {
        if ($rule !== 'completionuploaddone') {
            return \COMPLETION_INCOMPLETE;
        }

        $upload = \upload_resolve_instance_from_cm($this->cm);
        if (!$upload) {
            return \COMPLETION_INCOMPLETE;
        }

        $upload = (object)[
            'id' => $upload->id,
            'completionuploaddone' => $upload->completionuploaddone,
            'completionmode' => $upload->completionmode,
        ];

        global $DB;
        if (empty($upload->completionuploaddone)) {
            return \COMPLETION_COMPLETE;
        }

        $submission = $DB->get_record('upload_submissions', [
            'uploadid' => $upload->id,
            'userid' => $this->userid,
        ]);
        if (!$submission) {
            return \COMPLETION_INCOMPLETE;
        }

        if ($upload->completionmode === 'finalsubmit') {
            return !empty($submission->finalsubmitted) ? \COMPLETION_COMPLETE : \COMPLETION_INCOMPLETE;
        }

        $context = \context_module::instance($this->cm->id);
        $hasfiles = \upload_submission_has_files((int)$context->id, (int)$submission->id);
        return $hasfiles ? \COMPLETION_COMPLETE : \COMPLETION_INCOMPLETE;
    }

    /**
     * Rule description for UI.
     *
     * @param string $rule
     * @return string
     */
    public function get_custom_rule_descriptions(): array {
        return [
            'completionuploaddone' => get_string('completiondetail:uploaddone', 'upload'),
        ];
    }

    /**
     * Defines sort order for custom completion rules in the UI.
     *
     * @return array
     */
    public function get_sort_order(): array {
        return ['completionuploaddone'];
    }
}
