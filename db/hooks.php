<?php
/**
 * This file is part of a 108design source-available software product.
 *
 * Use and modification are permitted only under the Software License included
 * with this distribution. See LICENSE.md for the full terms.
 *
 * File documentation for db/hooks.php.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   See LICENSE.md for the full terms.
 */
defined('MOODLE_INTERNAL') || die();

$callbacks = [];

// Decorative product identity is restricted to the native administration surfaces.
$callbacks[] = [
    'hook' => \core\hook\output\before_standard_head_html_generation::class,
    'callback' => [\mod_upload\local\admin_branding::class, 'head'],
];
$callbacks[] = [
    'hook' => \core\hook\output\before_http_headers::class,
    'callback' => [\mod_upload\local\admin_branding::class, 'prepare'],
];
