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
 * Simply Upload module view page.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen <andreas.giesen.ext@nagarro.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/locallib.php');
require_once(__DIR__ . '/classes/form/upload_form.php');

$id = optional_param('id', 0, PARAM_INT);
$n = optional_param('n', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('upload', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $upload = $DB->get_record('upload', ['id' => $cm->instance], '*', MUST_EXIST);
} else if ($n) {
    $upload = $DB->get_record('upload', ['id' => $n], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $upload->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('upload', $upload->id, $course->id, false, MUST_EXIST);
} else {
    throw new moodle_exception('invalidcoursemodule');
}

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/upload:view', $context);

$url = new moodle_url('/mod/upload/view.php', ['id' => $cm->id]);
$PAGE->set_url($url);
$PAGE->set_title(format_string($upload->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$submission = upload_get_or_create_submission($upload, (int)$USER->id);
$locked = upload_submission_is_locked($submission);
$canedit = has_capability('mod/upload:submit', $context) && !$locked;

if (!$upload->allowresubmission && upload_submission_has_files((int)$context->id, (int)$submission->id)) {
    $canedit = false;
}

$fileoptions = upload_submission_filemanager_options($upload, $context, $submission);
$draftitemid = file_get_submitted_draft_itemid('submission_files');
file_prepare_draft_area($draftitemid, $context->id, 'mod_upload', 'submission_files', $submission->id, $fileoptions);

$showfinal = ($upload->completionmode === UPLOAD_COMPLETION_FINALSUBMIT && !$locked &&
    has_capability('mod/upload:submit', $context));
$mform = new \mod_upload\form\upload_form($url->out(false), [
    'canedit' => $canedit,
    'showfinal' => $showfinal,
    'draftitemid' => $draftitemid,
    'fileoptions' => $fileoptions,
]);

if ($mform->is_cancelled()) {
    redirect($url);
}

$fromform = $mform->get_data();
if ($fromform && confirm_sesskey()) {
    $isfinalsubmit = optional_param('finalsubmitbtn', '', PARAM_RAW_TRIMMED) !== '';

    if ($isfinalsubmit && $showfinal) {
        if (!upload_submission_has_files((int)$context->id, (int)$submission->id)) {
            redirect($url, get_string('cannotfinalsubmitnofiles', 'upload'), null, \core\output\notification::NOTIFY_WARNING);
        }

        $now = time();
        $submission->status = 'submitted';
        $submission->finalsubmitted = 1;
        $submission->finalsubmittedat = $now;
        $submission->timemodified = $now;
        $DB->update_record('upload_submissions', $submission);

        upload_update_user_completion($course, $cm, (int)$USER->id);
        redirect($url, get_string('finalsubmitsuccess', 'upload'), null, \core\output\notification::NOTIFY_SUCCESS);
    }

    // If this is not a final-submit action, treat it as a save attempt.
    // This is more robust than relying on submit button value presence alone.
    if (!$isfinalsubmit && $canedit) {
        $draftitemid = (int)($fromform->submission_files ?? 0);

        if ($draftitemid <= 0) {
            redirect($url, get_string('nofiles', 'upload'), null, \core\output\notification::NOTIFY_WARNING);
        }

        $data = (object)[
            'submission_files' => $draftitemid,
        ];

        file_save_draft_area_files(
            $draftitemid,
            $context->id,
            'mod_upload',
            'submission_files',
            $submission->id,
            $fileoptions
        );

        upload_apply_user_filename_suffix($upload, $context, $submission, $USER);

        $updaterec = (object)[
            'id' => $submission->id,
            'timemodified' => time(),
        ];
        $DB->update_record('upload_submissions', $updaterec);
        upload_update_user_completion($course, $cm, (int)$USER->id);
        redirect($url, get_string('filesaved', 'upload'), null, \core\output\notification::NOTIFY_SUCCESS);
    }
}

echo $OUTPUT->header();

$tabs = [];
$tabrow = [
    new tabobject(
        'view',
        new moodle_url('/mod/upload/view.php', ['id' => $cm->id]),
        get_string('uploadtab', 'upload')
    ),
];
if (has_capability('mod/upload:viewsubmissions', $context)) {
    $tabrow[] = new tabobject(
        'submissions',
        new moodle_url('/mod/upload/submissions.php', ['id' => $cm->id]),
        get_string('submissions', 'upload')
    );
}
$tabs[] = $tabrow;
print_tabs($tabs, 'view');

if (trim(strip_tags($upload->intro ?? '')) !== '') {
    echo $OUTPUT->box(format_module_intro('upload', $upload, $cm->id), 'generalbox mod_introbox', 'uploadintro');
}

$courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);
$hasfiles = upload_submission_has_files((int)$context->id, (int)$submission->id);

if ($upload->completionmode === UPLOAD_COMPLETION_FINALSUBMIT) {
    if ($locked) {
        echo $OUTPUT->notification(get_string('submissionlocked', 'upload'), \core\output\notification::NOTIFY_SUCCESS);
    } else if ($hasfiles) {
        echo $OUTPUT->notification(get_string('finalsubmitneeded', 'upload'), \core\output\notification::NOTIFY_INFO);
    } else {
        echo $OUTPUT->notification(get_string('finalsubmitneeded_nofiles', 'upload'), \core\output\notification::NOTIFY_INFO);
    }
} else {
    if ($hasfiles && !$canedit) {
        echo $OUTPUT->notification(get_string('uploadcompleted', 'upload'), \core\output\notification::NOTIFY_SUCCESS);
    } else if ($hasfiles && $canedit) {
        echo $OUTPUT->notification(get_string('uploadcompleted_canedit', 'upload'), \core\output\notification::NOTIFY_INFO);
    }
}

if ($hasfiles || $locked || !$canedit) {
    echo $OUTPUT->single_button($courseurl, get_string('returntocourse', 'upload'));
    echo html_writer::empty_tag('br');
    echo html_writer::empty_tag('br');
}

$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'mod_upload', 'submission_files', $submission->id, 'timemodified DESC', false);

echo $OUTPUT->heading(get_string('currentfiles', 'upload'), 3);
if (empty($files)) {
    echo $OUTPUT->notification(get_string('nofiles', 'upload'), \core\output\notification::NOTIFY_INFO);
} else {
    $items = [];
    foreach ($files as $file) {
        $fileurl = moodle_url::make_pluginfile_url(
            $context->id,
            'mod_upload',
            'submission_files',
            $submission->id,
            $file->get_filepath(),
            $file->get_filename()
        );
        $items[] = html_writer::link($fileurl, s($file->get_filename()), [
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
        ]);
    }
    echo html_writer::alist($items);
}

if (!$locked) {
    $mform->display();
}

echo $OUTPUT->footer();
