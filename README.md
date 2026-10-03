# Simply Upload for Moodle

Collect learner files in a focused course activity, with optional final submission
and automatic activity completion.

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

Requires Moodle 4.5 or later and the PHP version required by your Moodle release.

## Installation

1. Place this plugin in:
   - `mod/upload`
2. Visit **Site administration → Notifications** to complete installation.

## Using the activity

Teachers select the permitted file types, number and size of files, and whether
learners may replace a submission. Choose whether completion follows an upload
or requires the learner to submit finally.

Learners upload their files from the activity page. When final submission is enabled,
they also confirm their submission. Teachers review learner files in the submissions
overview. File links open in a new tab.

## Author

- Andreas Giesen (<andreas@108design.com>)

## License

This is a 108design source-available commercial software license, not an open-source license. See [LICENSE.md](https://github.com/108design/moodle-mod_upload/blob/main/LICENSE.md) for the full terms.
