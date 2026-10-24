<?php
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
// See LICENSE.md for the full terms.

namespace mod_upload\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Capability-checked deletion shared by the submissions report and its regression tests.
 *
 * @package mod_upload
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license See LICENSE.md for the full terms.
 */
final class submission_manager {
    /** Delete the selected submissions, or all submissions when ids is null. */
    public static function delete(\stdClass $activity, \stdClass $course, \stdClass $cm, ?array $ids): int {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/upload/locallib.php');
        $context = \context_module::instance($cm->id);
        require_capability('mod/upload:deletesubmissions', $context);
        if ((int)$cm->instance !== (int)$activity->id || (int)$cm->course !== (int)$course->id
                || (int)$activity->course !== (int)$course->id) {
            throw new \invalid_parameter_exception('Mismatched upload activity');
        }
        $where = 'uploadid = :uploadid';
        $params = ['uploadid' => $activity->id];
        if ($ids !== null) {
            $ids = array_values(array_unique(array_map('intval', $ids)));
            if (!$ids) {
                return 0;
            }
            [$insql, $inparams] = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);
            $where .= ' AND id ' . $insql;
            $params += $inparams;
        }
        $records = $DB->get_records_select('upload_submissions', $where, $params);
        foreach ($records as $record) {
            $transaction = $DB->start_delegated_transaction();
            get_file_storage()->delete_area_files($context->id, 'mod_upload', 'submission_files', $record->id);
            $DB->delete_records('upload_submissions', ['id' => $record->id, 'uploadid' => $activity->id]);
            upload_update_user_completion($course, $cm, (int)$record->userid);
            $event = \mod_upload\event\submission_deleted::create([
                'context' => $context, 'objectid' => $record->id, 'relateduserid' => $record->userid,
            ]);
            $event->add_record_snapshot('upload_submissions', $record);
            $event->trigger();
            $transaction->allow_commit();
        }
        return count($records);
    }
}
