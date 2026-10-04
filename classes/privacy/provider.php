<?php
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
// Use and modification are permitted only under the included Software License.
// See LICENSE.md for the full terms.

namespace mod_upload\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Manage learner submission data through Moodle's Privacy API.
 *
 * @package mod_upload
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license See LICENSE.md for the full terms.
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\request\core_userlist_provider {

    /**
     * Describe submission records and files.
     *
     * @param collection $collection Metadata collection.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('upload_submissions', [
            'uploadid' => 'privacy:metadata:submission:uploadid',
            'userid' => 'privacy:metadata:submission:userid',
            'status' => 'privacy:metadata:submission:status',
            'finalsubmitted' => 'privacy:metadata:submission:finalsubmitted',
            'finalsubmittedat' => 'privacy:metadata:submission:finalsubmittedat',
            'timecreated' => 'privacy:metadata:submission:timecreated',
            'timemodified' => 'privacy:metadata:submission:timemodified',
        ], 'privacy:metadata:submission');
        $collection->add_subsystem_link('core_files', [], 'privacy:metadata:files');
        return $collection;
    }

    /**
     * Find activities containing this learner's submissions.
     *
     * @param int $userid Learner ID.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contexts = new contextlist();
        $contexts->add_from_sql(
            "SELECT ctx.id
               FROM {upload_submissions} s
               JOIN {course_modules} cm ON cm.instance = s.uploadid
               JOIN {modules} m ON m.id = cm.module AND m.name = :module
               JOIN {context} ctx ON ctx.instanceid = cm.id AND ctx.contextlevel = :level
              WHERE s.userid = :userid",
            ['module' => 'upload', 'level' => CONTEXT_MODULE, 'userid' => $userid]
        );
        return $contexts;
    }

    /**
     * Find learners whose submissions belong to the requested activity.
     *
     * @param userlist $userlist Requested activity context.
     */
    public static function get_users_in_context(userlist $userlist): void {
        $uploadid = self::upload_id($userlist->get_context());
        if ($uploadid !== null) {
            $userlist->add_from_sql('userid',
                'SELECT userid FROM {upload_submissions} WHERE uploadid = :uploadid',
                ['uploadid' => $uploadid]);
        }
    }

    /**
     * Export only the approved learner's submissions and their files.
     *
     * @param approved_contextlist $contextlist Approved learner and contexts.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        foreach ($contextlist->get_contexts() as $context) {
            $uploadid = self::upload_id($context);
            if ($uploadid === null) {
                continue;
            }
            $submission = $DB->get_record('upload_submissions', [
                'uploadid' => $uploadid, 'userid' => $contextlist->get_user()->id,
            ]);
            if (!$submission) {
                continue;
            }
            $path = [get_string('privacy:path', 'upload')];
            $data = (object)[
                'status' => $submission->status,
                'finalsubmitted' => transform::yesno((bool)$submission->finalsubmitted),
                'finalsubmittedat' => $submission->finalsubmittedat
                    ? transform::datetime($submission->finalsubmittedat) : null,
                'timecreated' => transform::datetime($submission->timecreated),
                'timemodified' => transform::datetime($submission->timemodified),
            ];
            writer::with_context($context)->export_data($path, $data);
            writer::with_context($context)->export_area_files(
                $path, 'mod_upload', 'submission_files', $submission->id);
        }
    }

    /**
     * Remove all submissions in one approved upload activity.
     *
     * @param \context $context Approved context.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        $uploadid = self::upload_id($context);
        if ($uploadid === null) {
            return;
        }
        get_file_storage()->delete_area_files($context->id, 'mod_upload', 'submission_files');
        $DB->delete_records('upload_submissions', ['uploadid' => $uploadid]);
    }

    /**
     * Remove one learner's submissions only in approved contexts.
     *
     * @param approved_contextlist $contextlist Approved learner and contexts.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        foreach ($contextlist->get_contexts() as $context) {
            self::delete_submissions($context, [$contextlist->get_user()->id]);
        }
    }

    /**
     * Remove submissions for an approved selection of learners.
     *
     * @param approved_userlist $userlist Approved learners in one context.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        self::delete_submissions($userlist->get_context(), $userlist->get_userids());
    }

    /**
     * Resolve an upload activity, rejecting course/system/other-module contexts.
     *
     * @param \context $context Context to inspect.
     * @return int|null
     */
    private static function upload_id(\context $context): ?int {
        if ($context->contextlevel !== CONTEXT_MODULE) {
            return null;
        }
        $cm = get_coursemodule_from_id('upload', $context->instanceid, 0, false, IGNORE_MISSING);
        return $cm ? (int)$cm->instance : null;
    }

    /**
     * Delete only selected learners' submission records and linked file areas.
     *
     * @param \context $context Approved context.
     * @param int[] $userids Approved learner IDs.
     */
    private static function delete_submissions(\context $context, array $userids): void {
        global $DB;

        $uploadid = self::upload_id($context);
        if ($uploadid === null || !$userids) {
            return;
        }
        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'uploaduser');
        $params['uploadid'] = $uploadid;
        $submissions = $DB->get_records_select('upload_submissions',
            "uploadid = :uploadid AND userid $insql", $params, '', 'id');
        $fs = get_file_storage();
        foreach ($submissions as $submission) {
            $fs->delete_area_files($context->id, 'mod_upload', 'submission_files', $submission->id);
            $DB->delete_records('upload_submissions', ['id' => $submission->id]);
        }
    }
}
