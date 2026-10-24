<?php
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
// See LICENSE.md for the full terms.

namespace mod_upload\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Native module access logging.
 *
 * @package mod_upload
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license See LICENSE.md for the full terms.
 */
final class course_module_viewed extends \core\event\course_module_viewed {
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'upload';
    }

    public static function get_objectid_mapping() {
        return ['db' => 'upload', 'restore' => 'upload'];
    }
}
