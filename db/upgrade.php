<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

/**
 * Upgrade script for mod_upload.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   See LICENSE.md for the full terms.
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade mod_upload database schema.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_upload_upgrade(int $oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026040900) {
        $table = new xmldb_table('upload');

        $field = new xmldb_field('appenduserid', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'allowresubmission');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('appendusername', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'appenduserid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2026040900, 'upload');
    }

    if ($oldversion < 2026080800) {
        $table = new xmldb_table('upload');
        $field = new xmldb_field(
            'name',
            XMLDB_TYPE_CHAR,
            '255',
            null,
            XMLDB_NOTNULL,
            null,
            'Upload',
            'course'
        );

        $dbman->change_field_default($table, $field);

        upgrade_mod_savepoint(true, 2026080800, 'upload');
    }

    return true;
}
