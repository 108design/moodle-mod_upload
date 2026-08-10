<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

/**
 * Library of interface functions and constants for module upload.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   See LICENSE.md for the full terms.
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/locallib.php');

/**
 * Returns the information on whether the module supports a feature.
 *
 * @param string $feature FEATURE_xx constant.
 * @return mixed
 */
function upload_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Creates a new upload instance.
 *
 * @param stdClass $data
 * @param mod_upload_mod_form $mform
 * @return int
 */
function upload_add_instance($data, $mform = null): int {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    return $DB->insert_record('upload', $data);
}

/**
 * Updates an existing upload instance.
 *
 * @param stdClass $data
 * @param mod_upload_mod_form $mform
 * @return bool
 */
function upload_update_instance($data, $mform = null): bool {
    global $DB;

    $data->id = $data->instance;
    $data->timemodified = time();
    return (bool)$DB->update_record('upload', $data);
}

/**
 * Deletes a upload instance.
 *
 * @param int $id
 * @return bool
 */
function upload_delete_instance($id): bool {
    global $DB;

    $upload = $DB->get_record('upload', ['id' => $id]);
    if (!$upload) {
        // Deletion is intentionally idempotent. This can happen when a stale
        // course-module cleanup task runs after the instance was already removed.
        return true;
    }

    $context = null;
    $cm = get_coursemodule_from_instance('upload', $id, 0, false, IGNORE_MISSING);
    if ($cm) {
        $context = context_module::instance($cm->id);
    }
    $fs = get_file_storage();

    $submissions = $DB->get_records('upload_submissions', ['uploadid' => $id]);
    if (!empty($context)) {
        foreach ($submissions as $submission) {
            $fs->delete_area_files($context->id, 'mod_upload', 'submission_files', $submission->id);
        }
    }

    $DB->delete_records('upload_submissions', ['uploadid' => $id]);
    $DB->delete_records('upload', ['id' => $id]);

    return true;
}

/**
 * Gets completion custom rule descriptions.
 *
 * @param cm_info $cm
 * @return array
 */
function upload_get_completion_active_rule_descriptions($cm): array {
    $upload = upload_resolve_instance_from_cm($cm);
    if (!$upload || empty($upload->completionuploaddone)) {
        return [];
    }

    return [get_string('completiondetail:uploaddone', 'upload')];
}

/**
 * Obtains completion state for custom completion rules.
 *
 * @param stdClass $course
 * @param cm_info|stdClass $cm
 * @param int $userid
 * @param bool $type
 * @return bool
 */
function upload_get_completion_state($course, $cm, $userid, $type): bool {
    global $DB;

    $upload = upload_resolve_instance_from_cm($cm);
    if (!$upload) {
        return false;
    }

    if (empty($upload->completionuploaddone)) {
        return true;
    }

    $submission = $DB->get_record('upload_submissions', [
        'uploadid' => $upload->id,
        'userid' => $userid,
    ]);

    if (!$submission) {
        return false;
    }

    if ($upload->completionmode === UPLOAD_COMPLETION_FINALSUBMIT) {
        return !empty($submission->finalsubmitted);
    }

    $context = context_module::instance($cm->id);
    return upload_submission_has_files((int)$context->id, (int)$submission->id);
}

/**
 * File serving callback.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function upload_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []): bool {
    global $DB, $USER;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);
    require_capability('mod/upload:view', $context);
    if ($filearea !== 'submission_files') {
        return false;
    }

    if (empty($args)) {
        return false;
    }

    $submissionid = (int)array_shift($args);
    if (empty($args)) {
        return false;
    }
    $filepath = '/' . implode('/', $args);
    if (substr($filepath, -1) !== '/') {
        $parts = explode('/', $filepath);
        $filename = array_pop($parts);
        $filepath = implode('/', $parts) . '/';
    } else {
        return false;
    }

    $submission = $DB->get_record('upload_submissions', ['id' => $submissionid], '*', IGNORE_MISSING);
    if (!$submission) {
        return false;
    }

    $canviewstaff = has_capability('mod/upload:viewsubmissions', $context);
    $isowner = (int)$submission->userid === (int)$USER->id;
    if (!$canviewstaff && !$isowner) {
        return false;
    }

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_upload', 'submission_files', $submissionid, $filepath, $filename);
    if (!$file || $file->is_directory()) {
        return false;
    }

    send_stored_file($file, null, 0, $forcedownload, $options);
    return true;
}

/**
 * Extend settings navigation for module instance.
 *
 * @param settings_navigation $settings
 * @param navigation_node $uploadnode
 * @return void
 */
function upload_extend_settings_navigation(settings_navigation $settings, navigation_node $uploadnode): void {
    global $PAGE;

    if (empty($PAGE->cm) || $PAGE->cm->modname !== 'upload') {
        return;
    }
    if (!$uploadnode || !method_exists($uploadnode, 'add_node')) {
        return;
    }

    $context = context_module::instance($PAGE->cm->id);
    if (!has_capability('mod/upload:viewsubmissions', $context)) {
        return;
    }

    $url = new moodle_url('/mod/upload/submissions.php', ['id' => $PAGE->cm->id]);

    // Mirror core/plugin pattern (e.g. questionnaire): place after modedit when possible.
    $keys = $uploadnode->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if (($i === false) && array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    $node = navigation_node::create(
        get_string('viewsubmissions', 'upload'),
        $url,
        navigation_node::TYPE_SETTING,
        null,
        'uploadsubmissionssettings'
    );

    $uploadnode->add_node($node, $beforekey);
}
