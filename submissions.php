<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

/**
 * Simply Upload submissions report page.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   See LICENSE.md for the full terms.
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/locallib.php');

$id = required_param('id', PARAM_INT);
$sort = optional_param('sort', 'user', PARAM_ALPHA);
$dir = optional_param('dir', 'asc', PARAM_ALPHA);

$cm = get_coursemodule_from_id('upload', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$upload = $DB->get_record('upload', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/upload:viewsubmissions', $context);

$url = new moodle_url('/mod/upload/submissions.php', ['id' => $cm->id]);
$PAGE->set_url($url);
$PAGE->set_title(format_string($upload->name) . ': ' . get_string('submissions', 'upload'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
\mod_upload\local\admin_help::prepare();

$validsort = ['user', 'timeuploaded'];
if (!in_array($sort, $validsort, true)) {
    $sort = 'user';
}
$dir = ($dir === 'desc') ? 'desc' : 'asc';
$nextdir = ($dir === 'asc') ? 'desc' : 'asc';

$candelete = has_capability('mod/upload:deletesubmissions', $context);
$deleteall = optional_param('deleteall', false, PARAM_BOOL);
$deleteselected = optional_param('deleteselected', false, PARAM_BOOL);
if ($deleteall || $deleteselected) {
    require_capability('mod/upload:deletesubmissions', $context);
    require_sesskey();
    $selected = optional_param_array('selectedsubmissions', [], PARAM_INT);
    if (!$deleteall && !$selected) {
        redirect($url, get_string('noselectedsubmissions', 'upload'), null, \core\output\notification::NOTIFY_WARNING);
    }
    if (!optional_param('confirmdelete', false, PARAM_BOOL) || !data_submitted()) {
        $params = ['id' => $cm->id, 'confirmdelete' => 1, $deleteall ? 'deleteall' : 'deleteselected' => 1];
        foreach ($selected as $key => $submissionid) {
            $params['selectedsubmissions[' . $key . ']'] = $submissionid;
        }
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(get_string($deleteall ? 'confirmdeleteall' : 'confirmdeleteselected', 'upload'),
            new single_button(new moodle_url($url, $params), get_string('delete'), 'post'), $url);
        echo \mod_upload\local\admin_help::render();
        echo $OUTPUT->footer();
        exit;
    }
    $count = \mod_upload\local\submission_manager::delete($upload, $course, $cm, $deleteall ? null : $selected);
    redirect($url, get_string('submissionsdeleted', 'upload', $count), null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();

$tabs = [];
$tabrow = [
    new tabobject(
        'view',
        new moodle_url('/mod/upload/view.php', ['id' => $cm->id]),
        get_string('uploadtab', 'upload')
    ),
    new tabobject(
        'submissions',
        new moodle_url('/mod/upload/submissions.php', ['id' => $cm->id]),
        get_string('submissions', 'upload')
    ),
];
$tabs[] = $tabrow;
print_tabs($tabs, 'submissions');

$usersorturl = new moodle_url($url, ['sort' => 'user', 'dir' => ($sort === 'user') ? $nextdir : 'asc']);
$timesorturl = new moodle_url($url, ['sort' => 'timeuploaded', 'dir' => ($sort === 'timeuploaded') ? $nextdir : 'asc']);

$ordersql = ($sort === 'timeuploaded')
    ? "s.timemodified {$dir}, u.lastname ASC, u.firstname ASC"
    : "u.lastname {$dir}, u.firstname {$dir}, s.timemodified DESC";

$sql = "SELECT s.id, s.userid, s.status, s.finalsubmitted, s.timemodified,
               u.firstname, u.lastname, u.email,
               u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename
          FROM {upload_submissions} s
          JOIN {user} u ON u.id = s.userid
         WHERE s.uploadid = :uploadid
      ORDER BY {$ordersql}";
$submissions = $DB->get_records_sql($sql, ['uploadid' => $upload->id]);

if (!$submissions) {
    echo $OUTPUT->notification(get_string('nosubmissions', 'upload'), \core\output\notification::NOTIFY_INFO);
    echo \mod_upload\local\admin_help::render();
    echo $OUTPUT->footer();
    exit;
}

$rows = [];
$fs = get_file_storage();
foreach ($submissions as $submission) {
    $files = $fs->get_area_files($context->id, 'mod_upload', 'submission_files', $submission->id,
        'timemodified DESC', false);
    $hasfiles = !empty($files);
    if ($upload->completionmode === UPLOAD_COMPLETION_FINALSUBMIT) {
        $statuskey = !empty($submission->finalsubmitted) ? 'status_finalsubmitted'
            : ($hasfiles ? 'status_draftwithfiles' : 'status_draftnofiles');
    } else {
        $statuskey = $hasfiles ? 'status_uploaded' : 'status_noupload';
    }
    $links = [];
    foreach ($files as $file) {
        $links[] = [
            'name' => $file->get_filename(),
            'url' => moodle_url::make_pluginfile_url($context->id, 'mod_upload', 'submission_files',
                $submission->id, $file->get_filepath(), $file->get_filename(), true)->out(false),
        ];
    }
    $rows[] = [
        'id' => $submission->id, 'fullname' => fullname($submission), 'email' => $submission->email,
        'selectlabel' => get_string('selectsubmission', 'upload', fullname($submission)),
        'status' => get_string($statuskey, 'upload'), 'files' => $links,
        'time' => $hasfiles ? userdate(reset($files)->get_timemodified()) : '-',
    ];
}
echo $OUTPUT->render_from_template('mod_upload/submissions', [
    'url' => $url->out(false), 'sesskey' => sesskey(), 'candelete' => $candelete,
    'deletehelp' => $candelete ? \mod_upload\local\admin_help::icon('deleting') : '',
    'usersorturl' => $usersorturl->out(false), 'timesorturl' => $timesorturl->out(false), 'rows' => $rows,
]);
$PAGE->requires->js_call_amd('mod_upload/submissions', 'init');
echo \mod_upload\local\admin_help::render();
echo $OUTPUT->footer();
