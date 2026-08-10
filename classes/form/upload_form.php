<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

/**
 * Upload interaction form for Simply Upload activity.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   See LICENSE.md for the full terms.
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
