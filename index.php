<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

/**
 * Simply Upload index page.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   See LICENSE.md for the full terms.
 */

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);
require_course_login($course);

$PAGE->set_url('/mod/upload/index.php', ['id' => $id]);
$PAGE->set_title(get_string('modulenameplural', 'upload'));
$PAGE->set_heading($course->fullname);

$uploads = get_all_instances_in_course('upload', $course);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'upload'));

if (!$uploads) {
    echo $OUTPUT->notification(get_string('nonewmodules', 'upload'));
    echo $OUTPUT->footer();
    die();
}

$table = new html_table();
$table->head = [get_string('name')];

foreach ($uploads as $upload) {
    $cm = get_coursemodule_from_instance('upload', $upload->id, $course->id, false, MUST_EXIST);
    $context = context_module::instance($cm->id);
    if (!has_capability('mod/upload:view', $context)) {
        continue;
    }

    $link = html_writer::link(
        new moodle_url('/mod/upload/view.php', ['id' => $cm->id]),
        format_string($upload->name, true)
    );
    $table->data[] = [$link];
}

echo html_writer::table($table);
echo $OUTPUT->footer();
