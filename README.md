# mod_upload

Simply Upload activity module for Moodle.

## Overview

`mod_upload` provides a lightweight file upload activity where learners can:

- upload one or more files,
- optionally perform a **final submission**,
- complete activity conditions based on upload mode.

## Features

- Configurable max file count and max file size
- Optional allowed file type restrictions
- Optional resubmission control
- Completion mode:
  - complete on file upload, or
  - complete on final submission
- Teacher submissions overview page
- Moodle Backup/Restore support; learner submissions and files are only included when user data is selected

## Requirements

- Moodle 4.5+

## Compatibility

The plugin supports Moodle 4.5 through current releases while the relevant Moodle APIs remain compatible. If a future Moodle release requires an incompatible implementation, supported versions will be maintained in separate branches or releases.

## Installation

1. Place this plugin in:
   - `mod/upload`
2. Visit **Site administration → Notifications** to complete installation.

## Notes

- File links in student and teacher views open in a new tab.
- Submission status labels are mode-aware for clearer wording.

## Author

- Andreas Giesen (<andreas@108design.com>)

## License

This is a 108design source-available commercial software license, not an open-source license. See [LICENSE.md](https://github.com/108design/moodle-mod_upload/blob/main/LICENSE.md) for the full terms.
