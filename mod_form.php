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
 * Settings form for Simple upload activity module.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen <andreas.giesen.ext@nagarro.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../course/moodleform_mod.php');
require_once(__DIR__ . '/locallib.php');

/**
 * Activity settings form for upload.
 */
class mod_upload_mod_form extends moodleform_mod {
    /**
     * Defines forms elements.
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('text', 'name', get_string('name'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements(get_string('modulename', 'upload'));

        $mform->addElement('header', 'uploadsettings', get_string('modulename', 'upload'));

        $mform->addElement('select', 'maxfiles', get_string('maxfiles', 'upload'), [
            1 => 1,
            2 => 2,
            3 => 3,
            5 => 5,
            10 => 10,
            20 => 20,
        ]);
        $mform->setDefault('maxfiles', 3);

        $mform->addElement('select', 'maxbytes', get_string('maxbytes', 'upload'), get_max_upload_sizes($CFG->maxbytes, 0, 0));
        $mform->setDefault('maxbytes', 0);

        $mform->addElement('text', 'allowedtypes', get_string('allowedtypes', 'upload'), ['size' => '64']);
        $mform->setType('allowedtypes', PARAM_TEXT);
        $mform->addHelpButton('allowedtypes', 'allowedtypes', 'upload');

        $mform->addElement('advcheckbox', 'allowresubmission', get_string('allowresubmission', 'upload'));
        $mform->addHelpButton('allowresubmission', 'allowresubmission', 'upload');
        $mform->setDefault('allowresubmission', 1);

        $mform->addElement('advcheckbox', 'appenduserid', get_string('appenduserid', 'upload'));
        $mform->addHelpButton('appenduserid', 'appenduserid', 'upload');
        $mform->setDefault('appenduserid', 0);

        $mform->addElement('advcheckbox', 'appendusername', get_string('appendusername', 'upload'));
        $mform->addHelpButton('appendusername', 'appendusername', 'upload');
        $mform->setDefault('appendusername', 0);

        $mform->addElement('select', 'completionmode', get_string('completionmode', 'upload'), [
            UPLOAD_COMPLETION_HASFILE => get_string('completionmode_hasfile', 'upload'),
            UPLOAD_COMPLETION_FINALSUBMIT => get_string('completionmode_finalsubmit', 'upload'),
        ]);
        $mform->setDefault('completionmode', UPLOAD_COMPLETION_HASFILE);

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Add custom completion rules.
     *
     * @return array
     */
    public function add_completion_rules() {
        $mform = $this->_form;

        $mform->addElement('advcheckbox', 'completionuploaddone', '', get_string('completionuploaddone', 'upload'));
        $mform->addHelpButton('completionuploaddone', 'completionuploaddone', 'upload');

        return ['completionuploaddone'];
    }

    /**
     * Data validation.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ((int)$data['maxfiles'] < 1) {
            $errors['maxfiles'] = get_string('err_numeric', 'form');
        }

        return $errors;
    }

    /**
     * Whether completion rule is enabled.
     *
     * @param array $data
     * @return bool
     */
    public function completion_rule_enabled($data) {
        return !empty($data['completionuploaddone']);
    }
}
