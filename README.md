# Simply Upload for Moodle

Collect learner files in a focused course activity, with optional final submission
and automatic activity completion.

## Screenshots

<details>
<summary>View screenshots (3)</summary>

Click a preview to open the full-size screenshot.

<table>
<tr>
<td align="center" width="50%" valign="middle">
<a href="https://raw.githubusercontent.com/108design/moodle-mod_upload/main/docs/screenshots/simply-upload-view.jpg"><img src="https://raw.githubusercontent.com/108design/moodle-mod_upload/main/docs/screenshots/simply-upload-view.jpg" width="275" height="160" alt="Upload files from the activity page"></a><br>
<sub>Upload files from the activity page</sub>
</td>
<td align="center" width="50%" valign="middle">
<a href="https://raw.githubusercontent.com/108design/moodle-mod_upload/main/docs/screenshots/simply-upload-settings.jpg"><img src="https://raw.githubusercontent.com/108design/moodle-mod_upload/main/docs/screenshots/simply-upload-settings.jpg" width="300" height="151" alt="Configure file limits and submission options"></a><br>
<sub>Configure file limits and submission options</sub>
</td>
</tr>
<tr>
<td align="center" width="50%" valign="middle">
<a href="https://raw.githubusercontent.com/108design/moodle-mod_upload/main/docs/screenshots/simply-upload-submissions.jpg"><img src="https://raw.githubusercontent.com/108design/moodle-mod_upload/main/docs/screenshots/simply-upload-submissions.jpg" width="300" height="92" alt="Review learner files in the submissions overview"></a><br>
<sub>Review learner files in the submissions overview</sub>
</td>
</tr>
</table>

</details>

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

- Moodle 4.5 through 5.2

## Compatibility

Supports Moodle 4.5 through 5.2 and the PHP version required by your Moodle release.

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

## Privacy

The activity stores learner submission files, the submitting user and activity IDs,
submission status, final-submission information and timestamps. Moodle's Privacy API
provides export and deletion of a learner's submission data and files. Optional filename
suffixes can include the learner's user ID or username. Files are accessible to their
owner and staff with permission to view submissions. No external upload service is used.

## Author

- Andreas Giesen (<andreas@108design.com>)

## License

This is a 108design source-available commercial software license, not an open-source license. See [LICENSE.md](https://github.com/108design/moodle-mod_upload/blob/main/LICENSE.md) for the full terms.
