<?php
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

/**
 * English language strings for mod_upload.
 *
 * @package   mod_upload
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   See LICENSE.md for the full terms.
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Simply Upload';
$string['modulename'] = 'Simply Upload';
$string['modulenameplural'] = 'Simply Uploads';
$string['modulename_help'] = 'Simply Upload allows users to upload one or more files and complete based on upload/final submit rules.';
$string['pluginadministration'] = 'Simply Upload administration';

$string['upload:addinstance'] = 'Add a new Simply Upload activity';
$string['upload:view'] = 'View Simply Upload activity';
$string['upload:submit'] = 'Upload files in Simply Upload';
$string['upload:viewsubmissions'] = 'View Simply Upload submissions';

$string['maxfiles'] = 'Maximum number of files';
$string['maxbytes'] = 'Maximum file size';
$string['allowedtypes'] = 'Allowed file types';
$string['allowedtypes_help'] = 'Comma-separated list, e.g. .pdf,.docx,.png. Leave empty to allow all.';
$string['allowresubmission'] = 'Allow resubmission';
$string['allowresubmission_help'] = 'If enabled, users can replace files until final submit. Final submit always locks the attempt.';
$string['appenduserid'] = 'Append user ID to filenames';
$string['appenduserid_help'] = 'If enabled, uploaded filenames will be saved with the user ID appended (for example: report_1234.pdf).';
$string['appendusername'] = 'Append username to filenames';
$string['appendusername_help'] = 'If enabled, uploaded filenames will be saved with the Moodle username appended (for example: report_jane.doe.pdf).';

$string['completionmode'] = 'Completion mode';
$string['completionmode_hasfile'] = 'Complete when at least one file exists';
$string['completionmode_finalsubmit'] = 'Complete only after final submit';

$string['completionuploaddone'] = 'Student must complete Simply Upload conditions';
$string['completionuploaddone_desc'] = 'Activity is complete when the Simply Upload completion mode condition is met.';
$string['completiondetail:uploaddone'] = 'User must satisfy Simply Upload completion condition';

$string['uploadfiles'] = 'Upload files';
$string['uploadtab'] = 'Upload';
$string['submitfiles'] = 'Save uploaded files';
$string['finalsubmit'] = 'Final submit';
$string['finalsubmitconfirm'] = 'Are you sure you want to final submit? You will not be able to edit files afterwards.';
$string['finalsubmitdisabled'] = 'Final submit is available after at least one uploaded file exists.';
$string['submissionlocked'] = 'Your submission is final and locked.';
$string['nofiles'] = 'No files uploaded yet.';
$string['currentfiles'] = 'Current files';
$string['filesaved'] = 'Uploaded files saved.';
$string['finalsubmitsuccess'] = 'Your submission is now final.';
$string['cannotfinalsubmitnofiles'] = 'You must upload at least one file before final submit.';
$string['uploadcompleted'] = 'Your upload is completed.';
$string['uploadcompleted_canedit'] = 'Your upload is saved. You can still update your files here.';
$string['finalsubmitneeded'] = 'You uploaded files. Click Final submit to complete this activity.';
$string['finalsubmitneeded_nofiles'] = 'Upload at least one file, then click Final submit to complete this activity.';
$string['returntocourse'] = 'Return to course';

$string['submissions'] = 'Submissions';
$string['viewsubmissions'] = 'View submissions';
$string['user'] = 'User';
$string['status'] = 'Status';
$string['filename'] = 'File';
$string['timeuploaded'] = 'Uploaded';
$string['submitted'] = 'Final submitted';
$string['notsubmitted'] = 'Not final submitted';
$string['status_finalsubmitted'] = 'Final submitted';
$string['status_draftwithfiles'] = 'Files uploaded (final submission pending)';
$string['status_draftnofiles'] = 'Draft (no files uploaded)';
$string['status_uploaded'] = 'Files uploaded';
$string['status_noupload'] = 'No upload yet';
$string['selectall'] = 'Select all';
$string['deleteselected'] = 'Delete selected';
$string['deleteallsubmissions'] = 'Delete all submissions';
$string['noselectedsubmissions'] = 'No submissions selected.';
$string['submissionsdeleted'] = '{$a} submission(s) deleted.';
$string['nosubmissions'] = 'No submissions found.';
$string['nonewmodules'] = 'No Simply Upload activities found in this course.';
$string['pluginnotconfigured'] = 'Simply Upload is not configured correctly.';

$string['modulename_link'] = 'mod/upload/view';

$string['eventsubmissionupdated'] = 'Simply Upload submission updated';
$string['eventsubmissionfinalised'] = 'Simply Upload submission finalised';

$string['page-mod-upload-x'] = 'Any upload module page';
$string['uploadfieldset'] = 'Custom upload settings';
