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

$validsort = ['user', 'timeuploaded'];
if (!in_array($sort, $validsort, true)) {
    $sort = 'user';
}
$dir = ($dir === 'desc') ? 'desc' : 'asc';
$nextdir = ($dir === 'asc') ? 'desc' : 'asc';

if (optional_param('deleteselected', 0, PARAM_BOOL) && confirm_sesskey()) {
    $selected = optional_param_array('selectedsubmissions', [], PARAM_INT);
    if (empty($selected)) {
        redirect($url, get_string('noselectedsubmissions', 'upload'), null, \core\output\notification::NOTIFY_WARNING);
    }

    [$insql, $inparams] = $DB->get_in_or_equal($selected, SQL_PARAMS_NAMED);
    $params = array_merge(['uploadid' => $upload->id], $inparams);
    $records = $DB->get_records_select('upload_submissions', "uploadid = :uploadid AND id {$insql}", $params, '', 'id');

    foreach ($records as $record) {
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'mod_upload', 'submission_files', $record->id);
        $DB->delete_records('upload_submissions', ['id' => $record->id]);
        upload_update_user_completion($course, $cm, (int)$record->userid);
    }

    $count = count($records);
    redirect($url, get_string('submissionsdeleted', 'upload', $count), null, \core\output\notification::NOTIFY_SUCCESS);
}

if (optional_param('deleteall', 0, PARAM_BOOL) && confirm_sesskey()) {
    $records = $DB->get_records('upload_submissions', ['uploadid' => $upload->id], '', 'id');
    foreach ($records as $record) {
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'mod_upload', 'submission_files', $record->id);
        $DB->delete_records('upload_submissions', ['id' => $record->id]);
        upload_update_user_completion($course, $cm, (int)$record->userid);
    }

    $count = count($records);
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
    echo $OUTPUT->footer();
    exit;
}

$selectallcheckbox = html_writer::checkbox('selectall', 1, false, '', [
    'id' => 'upload-selectall',
    'title' => get_string('selectall', 'upload'),
]);

$table = new html_table();
$table->head = [
    $selectallcheckbox,
    html_writer::link($usersorturl, get_string('user')),
    get_string('status', 'upload'),
    get_string('filename', 'upload'),
    html_writer::link($timesorturl, get_string('timeuploaded', 'upload')),
];

$fs = get_file_storage();
echo html_writer::start_tag('form', ['method' => 'post', 'action' => $url->out(false)]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

foreach ($submissions as $submission) {
    $files = $fs->get_area_files(
        $context->id,
        'mod_upload',
        'submission_files',
        $submission->id,
        'timemodified DESC',
        false
    );

    $username = fullname($submission) . ' (' . s($submission->email) . ')';
    $hasfiles = !empty($files);
    $filenames = [];
    $latesttimemodified = '-';

    if ($hasfiles) {
        $first = reset($files);
        $latesttimemodified = userdate($first->get_timemodified());
        foreach ($files as $file) {
            $fileurl = moodle_url::make_pluginfile_url(
                $context->id,
                'mod_upload',
                'submission_files',
                $submission->id,
                $file->get_filepath(),
                $file->get_filename()
            );
            $filenames[] = html_writer::link($fileurl, s($file->get_filename()), [
                'target' => '_blank',
                'rel' => 'noopener noreferrer',
            ]);
        }
    }

    if ($upload->completionmode === UPLOAD_COMPLETION_FINALSUBMIT) {
        if (!empty($submission->finalsubmitted)) {
            $status = get_string('status_finalsubmitted', 'upload');
        } else if ($hasfiles) {
            $status = get_string('status_draftwithfiles', 'upload');
        } else {
            $status = get_string('status_draftnofiles', 'upload');
        }
    } else {
        $status = $hasfiles
            ? get_string('status_uploaded', 'upload')
            : get_string('status_noupload', 'upload');
    }

    $table->data[] = [
        html_writer::checkbox('selectedsubmissions[]', $submission->id, false, '', [
            'class' => 'upload-selectitem',
        ]),
        $username,
        $status,
        $hasfiles ? implode('<br>', $filenames) : get_string('nofiles', 'upload'),
        $latesttimemodified,
    ];
}

echo html_writer::table($table);
echo html_writer::empty_tag('input', [
    'type' => 'submit',
    'name' => 'deleteselected',
    'value' => get_string('deleteselected', 'upload'),
    'class' => 'btn btn-secondary me-2',
]);
echo html_writer::empty_tag('input', [
    'type' => 'submit',
    'name' => 'deleteall',
    'value' => get_string('deleteallsubmissions', 'upload'),
    'class' => 'btn btn-danger',
]);
echo html_writer::end_tag('form');

$PAGE->requires->js_init_code("document.getElementById('upload-selectall')?.addEventListener('change', function() {
    var checked = this.checked;
    document.querySelectorAll('.upload-selectitem').forEach(function(cb) { cb.checked = checked; });
});");

echo $OUTPUT->footer();
