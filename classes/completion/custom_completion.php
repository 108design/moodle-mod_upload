<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

/**
 * Custom completion handling for Simply Upload activity.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   See LICENSE.md for the full terms.
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
