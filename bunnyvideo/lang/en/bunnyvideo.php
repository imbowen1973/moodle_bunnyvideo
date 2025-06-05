<?php
// File: mod/bunnyvideo/lang/en/bunnyvideo.php

$string['pluginname'] = 'Bunny Video';
$string['modulename'] = 'Bunny Video';
$string['modulenameplural'] = 'Bunny Videos';

$string['bunnyvideointro'] = 'Intro text for Bunny video';
$string['bunnyvideo:addinstance'] = 'Add a new Bunny video';
$string['bunnyvideo:view'] = 'View Bunny video';

$string['privacy:metadata'] = 'The Bunny video module does not store any personal data.';

// Video fields
$string['videopath'] = 'Video Path';
$string['videopath_help'] = 'Enter the relative path or full URL to the video file hosted on Bunny.net.';

$string['posterurl'] = 'Poster Image URL';
$string['posterurl_help'] = 'Optional. Enter the full URL of an image to use as the video thumbnail/poster frame.';

// Completion tracking
$string['completionvideo'] = 'Require video watch for completion';
$string['completionvideo_help'] = 'When checked, activity completion will only be marked after the specified percentage of the video is watched.';

$string['completionpercent'] = 'Completion threshold';
$string['completionpercent_help'] = 'Select the percentage of the video that must be watched to mark this activity complete.';

$string['completionpercentrequired'] = 'Please select a valid completion percentage between 5% and 100%.';

// Settings page (admin)
$string['settings_desc'] = 'Configure default values and authentication for Bunny Video.';
$string['bunnycdnurl'] = 'Bunny.net CDN base URL';
$string['bunnycdnurl_desc'] = 'Enter the base URL of your Bunny.net pull zone.';

$string['authkey'] = 'Secure token key';
$string['authkey_desc'] = 'Secret key used to generate signed tokens for secure video access.';

$string['validduration'] = 'Token validity duration (seconds)';
$string['validduration_desc'] = 'How long a signed token should remain valid (in seconds).';

$string['defaultcompletionvideo'] = 'Enable video-based completion by default';
$string['defaultcompletionvideo_desc'] = 'If enabled, new Bunny Video activities will require completion tracking by default.';

$string['completionthreshold'] = 'Default completion percentage';
$string['completionthreshold_desc'] = 'Default percentage of video watched required for completion, for new activities.';

// Player strings
$string['progress'] = 'Progress';
$string['completed'] = 'Completed';
$string['novideosupport'] = 'Your browser does not support HTML5 video.';

// Misc
$string['nobunnyvideos'] = 'No Bunny videos found';
