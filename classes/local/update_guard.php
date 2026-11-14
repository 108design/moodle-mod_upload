<?php
// This file is part of Moodle - https://moodle.org/
/**
 * Early independent update-package guard.
 *
 * @package mod_upload
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license 108design source-available software license; see LICENSE.md
 */
namespace mod_upload\local;

defined('MOODLE_INTERNAL') || die();

/** Keep the update guard available independently of optional product hosts. */
final class update_guard {
    /**
     * Check supported native update requests before any plugin files are replaced.
     *
     * @param \core\hook\after_config $hook Native early configuration hook.
     * @return void
     */
    public static function after_config(\core\hook\after_config $hook): void {
        if (!class_exists(\design108\update_guard\v1\guard::class, false)) {
            require_once(dirname(__DIR__, 2) . '/update-guard.php');
        }
        \design108\update_guard\v1\guard::enter('mod_upload', dirname(__DIR__, 2));
    }
}
