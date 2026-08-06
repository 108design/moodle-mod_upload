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
 * Local helper library for Simply Upload module.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen <andreas.giesen.ext@nagarro.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

const UPLOAD_COMPLETION_HASFILE = 'hasfile';
const UPLOAD_COMPLETION_FINALSUBMIT = 'finalsubmit';

/**
 * Get or create a submission container for a user.
 *
 * @param stdClass $upload
 * @param int $userid
 * @return stdClass
 */
function upload_get_or_create_submission(stdClass $upload, int $userid): stdClass {
    global $DB;

    $submission = $DB->get_record('upload_submissions', [
        'uploadid' => $upload->id,
        'userid' => $userid,
    ]);

    if ($submission) {
        return $submission;
    }

    $now = time();
    $record = (object)[
        'uploadid' => $upload->id,
        'userid' => $userid,
        'status' => 'draft',
        'finalsubmitted' => 0,
        'finalsubmittedat' => 0,
        'timecreated' => $now,
        'timemodified' => $now,
    ];
    $record->id = $DB->insert_record('upload_submissions', $record);

    return $record;
}

/**
 * Checks if a submission has at least one file.
 *
 * @param int $contextid
 * @param int $submissionid
 * @return bool
 */
function upload_submission_has_files(int $contextid, int $submissionid): bool {
    $fs = get_file_storage();
    $files = $fs->get_area_files($contextid, 'mod_upload', 'submission_files', $submissionid, 'id', false);
    return !empty($files);
}

/**
 * Returns filemanager options for submission files.
 *
 * @param stdClass $upload
 * @param context_module $context
 * @param stdClass $submission
 * @return array
 */
function upload_submission_filemanager_options(stdClass $upload, context_module $context, stdClass $submission): array {
    $acceptedtypes = '*';
    if (!empty($upload->allowedtypes)) {
        $acceptedtypes = array_map('trim', explode(',', $upload->allowedtypes));
        $acceptedtypes = array_filter($acceptedtypes);
        if (empty($acceptedtypes)) {
            $acceptedtypes = '*';
        }
    }

    return [
        'subdirs' => 0,
        'maxbytes' => (int)$upload->maxbytes,
        'maxfiles' => (int)$upload->maxfiles,
        'accepted_types' => $acceptedtypes,
        'context' => $context,
    ];
}

/**
 * Returns true when submission is locked.
 *
 * @param stdClass $submission
 * @return bool
 */
function upload_submission_is_locked(stdClass $submission): bool {
    return !empty($submission->finalsubmitted);
}

/**
 * Recalculate/update module completion for a user.
 *
 * @param stdClass $course
 * @param cm_info|stdClass $cm
 * @param int $userid
 * @return void
 */
function upload_update_user_completion(stdClass $course, $cm, int $userid): void {
    global $DB;

    // Refresh cm from DB to avoid stale/partial module data during immediate post-save actions.
    if (empty($cm->id)) {
        return;
    }

    $cm = get_coursemodule_from_id('upload', (int)$cm->id, (int)$course->id, false, IGNORE_MISSING);
    if (!$cm) {
        return;
    }

    if (empty($cm->instance)) {
        return;
    }

    $instanceexists = $DB->record_exists('upload', ['id' => (int)$cm->instance]);
    if (!$instanceexists) {
        return;
    }

    $completion = new completion_info($course);
    if ($completion->is_enabled($cm) == COMPLETION_TRACKING_NONE) {
        return;
    }
    $completion->update_state($cm, COMPLETION_UNKNOWN, $userid);
}

/**
 * Resolve upload instance record from a course module-like object.
 *
 * Handles contexts where $cm->instance may be missing or inconsistent.
 *
 * @param stdClass|cm_info $cm
 * @return stdClass|null
 */
function upload_resolve_instance_from_cm($cm): ?stdClass {
    global $DB;

    if (!empty($cm->instance)) {
        $record = $DB->get_record('upload', ['id' => (int)$cm->instance], '*', IGNORE_MISSING);
        if ($record) {
            return $record;
        }
    }

    if (!empty($cm->id)) {
        $cmrecord = $DB->get_record('course_modules', ['id' => (int)$cm->id], 'id,instance', IGNORE_MISSING);
        if (!empty($cmrecord->instance)) {
            return $DB->get_record('upload', ['id' => (int)$cmrecord->instance], '*', IGNORE_MISSING);
        }
    }

    return null;
}

/**
 * Build filename suffix based on instance settings and user.
 *
 * @param stdClass $upload
 * @param stdClass $user
 * @return string
 */
function upload_build_user_filename_suffix(stdClass $upload, stdClass $user): string {
    $parts = [];

    if (!empty($upload->appenduserid)) {
        $parts[] = (string)(int)$user->id;
    }

    if (!empty($upload->appendusername) && !empty($user->username)) {
        $parts[] = preg_replace('/[^a-zA-Z0-9._-]+/', '_', $user->username);
    }

    if (empty($parts)) {
        return '';
    }

    return '_' . implode('_', $parts);
}

/**
 * Append configured user info to filenames in the submission file area.
 *
 * @param stdClass $upload
 * @param context_module $context
 * @param stdClass $submission
 * @param stdClass $user
 * @return void
 */
function upload_apply_user_filename_suffix(stdClass $upload, context_module $context,
    stdClass $submission, stdClass $user): void {

    $suffix = upload_build_user_filename_suffix($upload, $user);
    if ($suffix === '') {
        return;
    }

    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_upload', 'submission_files', $submission->id, 'id', false);

    foreach ($files as $file) {
        if ($file->is_directory()) {
            continue;
        }

        $filename = $file->get_filename();
        $dotpos = strrpos($filename, '.');
        if ($dotpos === false || $dotpos === 0) {
            $basename = $filename;
            $extension = '';
        } else {
            $basename = substr($filename, 0, $dotpos);
            $extension = substr($filename, $dotpos);
        }

        if (substr($basename, -strlen($suffix)) === $suffix) {
            continue;
        }

        $targetbasename = $basename . $suffix;
        $targetfilename = $targetbasename . $extension;

        $counter = 1;
        while ($fs->file_exists($context->id, 'mod_upload', 'submission_files', $submission->id,
            $file->get_filepath(), $targetfilename)) {
            $targetfilename = $targetbasename . '_' . $counter . $extension;
            $counter++;
        }

        $file->rename($file->get_filepath(), $targetfilename);
    }
}
