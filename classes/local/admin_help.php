<?php
/**
 * This file is part of a 108design source-available software product.
 *
 * Use and modification are permitted only under the Software License included
 * with this distribution. See LICENSE.md for the full terms.
 *
 * File documentation for classes/local/admin_help.php.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen
 * @license   See LICENSE.md for the full terms.
 */
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
namespace mod_upload\local;

defined('MOODLE_INTERNAL') || die();

/** Contextual settings help, following the standalone Frontpage panel contract. */
final class admin_help {
    private const TOPICS = ['upload', 'report', 'deleting'];
    private static bool $prepared = false;

    /** Register only the permitted settings page before its header is rendered. */
    public static function prepare(): void {
        global $CFG, $PAGE;
        if (self::$prepared || !($PAGE->context instanceof \context_module)
                || !has_capability('mod/upload:view', $PAGE->context)) {
            return;
        }
        $PAGE->add_body_class('mod-upload-admin-help-page');
        self::$prepared = true;
        $PAGE->requires->css(new \moodle_url('/mod/upload/styles/admin_help.css', [
            'rev' => get_config('mod_upload', 'version'),
            'theme' => theme_get_revision(),
        ]));
        $module = (int)$CFG->version < 2025041400 ? 'admin_help_legacy' : 'admin_help';
        $PAGE->requires->js_call_amd('mod_upload/admin_help_form', 'init');
        $PAGE->requires->js_call_amd('mod_upload/' . $module, 'init', [[
            'topic' => optional_param('uploadhelp', '', PARAM_ALPHA),
        ]]);
    }

    /** Native popover with concise guidance and a direct link to the extended topic. */
    public static function icon(string $key): string {
        global $CFG, $PAGE;
        if (!in_array($key, self::TOPICS, true)
                || ($key === 'deleting' && !has_capability('mod/upload:deletesubmissions', $PAGE->context))) {
            throw new \coding_exception('Unknown Administration help topic.');
        }
        $url = new \moodle_url($PAGE->url);
        $url->param('uploadhelp', $key);
        $help = new \help_icon('adminhelp_' . $key, 'mod_upload', $url->out(false));
        $renderer = $PAGE->get_renderer('core');
        $data = (array)$help->export_for_template($renderer);
        $data['completedoclink'] = \html_writer::link($url, get_string('adminhelpmore', 'mod_upload'));
        $data['legacy'] = (int)$CFG->version < 2025041400;
        $data['infoicon'] = (int)$CFG->version < 2026100500 ? 'i/info' : 'i/circleinfo';
        return $renderer->render_from_template('mod_upload/settings_help_icon', $data);
    }

    /** Always available in the native Topnav user area on the registered page. */
    public static function navbar(\renderer_base $renderer): string {
        global $CFG;
        if (!self::$prepared) {
            return '';
        }
        $label = get_string('adminhelp', 'mod_upload');
        return \html_writer::tag('button', $renderer->pix_icon(
            (int)$CFG->version < 2026100500 ? 'i/info' : 'i/circleinfo', ''), [
                'type' => 'button', 'class' => 'nav-link icon-no-margin mod-upload-help-navbar',
                'data-upload-help-open' => '', 'aria-label' => $label, 'title' => $label,
                'aria-controls' => 'mod-upload-context-help', 'aria-expanded' => 'false',
            ]);
    }

    /** Same header, topic chooser and article layout as the Frontpage reference. */
    public static function render(): string {
        global $PAGE;
        if (!self::$prepared) {
            return '';
        }
        $title = get_string('adminhelp', 'mod_upload');
        $close = \html_writer::tag('button', \html_writer::span('&#215;', '', ['aria-hidden' => 'true']), [
            'type' => 'button', 'class' => 'btn btn-secondary btn-sm mod-upload-help-close',
            'aria-label' => get_string('closebuttontitle', 'moodle'), 'data-upload-help-close' => '1',
        ]);
        $options = '';
        $articles = '';
        $topics = $PAGE->url->compare(new \moodle_url('/mod/upload/submissions.php'), URL_MATCH_BASE)
            ? ['report', 'upload', 'deleting'] : self::TOPICS;
        foreach ($topics as $topic) {
            if ($topic === 'report' && !has_capability('mod/upload:viewsubmissions', $PAGE->context)) {
                continue;
            }
            if ($topic === 'deleting' && !has_capability('mod/upload:deletesubmissions', $PAGE->context)) {
                continue;
            }
            $label = get_string('adminhelp_' . $topic, 'mod_upload');
            $options .= \html_writer::tag('option', $label, ['value' => $topic]);
            $articles .= \html_writer::tag('article', \html_writer::tag('h3', $label, ['class' => 'h5', 'tabindex' => '-1'])
                . get_string('adminhelp_' . $topic . '_article', 'mod_upload'), [
                    'data-upload-help-article' => $topic, 'hidden' => 'hidden',
                ]);
        }
        $choice = \html_writer::tag('label', get_string('adminhelptopics', 'mod_upload'), [
            'for' => 'mod-upload-help-topic', 'class' => 'form-label',
        ]) . \html_writer::tag('select', $options, [
            'id' => 'mod-upload-help-topic', 'class' => 'form-select custom-select',
        ]);
        // Shared help-panel identity: no link or action; close remains beside the topic selector.
        $brand = \html_writer::tag('span', \html_writer::empty_tag('img', [
            'src' => $PAGE->get_renderer('core')->image_url('helpbrand', 'mod_upload')->out(false),
            'alt' => '', 'width' => 25, 'height' => 24,
        ]), [
            'class' => 'mod-upload-help-brand', 'role' => 'img', 'aria-label' => '108design',
            'title' => '108design', 'tabindex' => '0', 'data-toggle' => 'tooltip', 'data-bs-toggle' => 'tooltip',
        ]);
        $header = \html_writer::tag('header', \html_writer::tag('h2', $title, [
            'id' => 'mod-upload-context-help-title', 'class' => 'sr-only visually-hidden', 'tabindex' => '-1',
        ]) . \html_writer::div($choice, 'mod-upload-help-choices') . $brand . $close,
            ['class' => 'mod-upload-help-header mod-upload-help-header-branded']);
        return \html_writer::tag('aside', $header
            . \html_writer::div($articles, 'mod-upload-help-content', ['tabindex' => '0']), [
                'id' => 'mod-upload-context-help', 'class' => 'mod-upload-context-help',
                'aria-labelledby' => 'mod-upload-context-help-title', 'hidden' => 'hidden',
                'data-upload-help-url' => $PAGE->url->out(false),
            ]);
    }

    /** Append the registered panel without changing the page's controls. */
    public static function footer(\core\hook\output\before_standard_footer_html_generation $hook): void {
        $hook->add_html(self::render());
    }
}
