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
 * Upload interaction form for Simple upload activity.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen <andreas.giesen.ext@nagarro.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_upload\form;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../../../lib/formslib.php');

/**
 * Upload form for upload student interactions.
 */
class upload_form extends \moodleform {
    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $canedit = !empty($customdata['canedit']);
        $showfinal = !empty($customdata['showfinal']);
        $draftitemid = (int)($customdata['draftitemid'] ?? 0);
        $fileoptions = $customdata['fileoptions'] ?? [];

        if ($canedit) {
            $mform->addElement('filemanager', 'submission_files', get_string('uploadfiles', 'upload'), null, $fileoptions);
            $mform->setDefault('submission_files', $draftitemid);
            $mform->addElement('submit', 'savefiles', get_string('submitfiles', 'upload'));
        }

        if ($showfinal) {
            $mform->addElement('submit', 'finalsubmitbtn', get_string('finalsubmit', 'upload'));
        }
    }
}
