<p align="center">
  <img src="https://raw.githubusercontent.com/108design/moodle-mod_upload/main/docs/branding/logo.svg" alt="Simply Upload logo" width="125" height="125">
</p>

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

- Moodle 4.5 through 5.3

## Compatibility

Supports Moodle 4.5 through 5.3 and the PHP version required by your Moodle release.

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
overview. Submission files download as attachments.

## Managing submissions

The **Submissions** tab shows learner files and their submission status. Viewing
submissions does not grant deletion rights. Deletion requires the separate
**Delete Simply Upload submissions** permission, which is assigned to editing
teachers and managers by default.

Choose **Delete selected** or **Delete all submissions**, then review the confirmation.
Deletion removes the submission records and files from this activity and recalculates
upload-based completion. Cancelling the confirmation leaves submissions unchanged.
Files and submissions in other activities are preserved.

Course backups and restores include learner submissions and files only when user
data is included. The activity and its settings can be restored without learner data.

The interface and contextual help are available in English and German.

## Privacy

The activity stores learner submission files, the submitting user and activity IDs,
submission status, final-submission information and timestamps. Moodle's Privacy API
provides export and deletion of a learner's submission data and files. Optional filename
suffixes can include the learner's user ID or username. Files are accessible to their
owner and staff with permission to view submissions. No external upload service is used.

## Author

- Andreas Giesen (<andreas@108design.com>)

## License

**This release is available free of charge under the 108design Software License.**

See [LICENSE.md](https://github.com/108design/moodle-mod_upload/blob/main/LICENSE.md) for the full terms.
