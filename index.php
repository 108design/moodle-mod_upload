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
 * Simply Upload index page.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen <andreas.giesen.ext@nagarro.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
