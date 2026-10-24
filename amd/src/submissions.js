/**
 * This file is part of a 108design source-available software product.
 *
 * Use and modification are permitted only under the Software License included
 * with this distribution. See LICENSE.md for the full terms.
 *
 * File documentation for amd/src/submissions.js.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   See LICENSE.md for the full terms.
 */
// Copyright 2026 Andreas Giesen <andreas@108design.com>. See LICENSE.md.
/**
 * Select only the submissions in this report.
 * @module mod_upload/submissions
 * @license See LICENSE.md for the full terms.
 */
export const init = () => {
    const root = document.querySelector('[data-upload-submissions]');
    const selectall = root?.querySelector('[data-upload-selectall]');
    selectall?.addEventListener('change', () => {
        root.querySelectorAll('[data-upload-selectitem]').forEach(input => {
            input.checked = selectall.checked;
        });
    });
};
