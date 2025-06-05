<?php
// File: mod/bunnyvideo/track.php
// Video progress tracking endpoint for Bunny Video module

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/completionlib.php');
require_once(__DIR__ . '/lib.php');

header('Content-Type: application/json');

// Required parameters
$id         = required_param('id', PARAM_INT);           // Course module ID
$watchtime  = required_param('watchtime', PARAM_INT);    // Current watch time
$duration   = required_param('duration', PARAM_INT);     // Full duration of video
$maxwatched = optional_param('maxwatched', 0, PARAM_INT);

// Validate parameters
if ($duration <= 0 || $watchtime < 0 || $watchtime > $duration + 5) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid tracking data.']);
    exit;
}

// Load context and module
$cm         = get_coursemodule_from_id('bunnyvideo', $id, 0, false, MUST_EXIST);
$course     = get_course($cm->course);
$bunnyvideo = $DB->get_record('bunnyvideo', ['id' => $cm->instance], '*', MUST_EXIST);

// Auth and permissions
require_login($course, false, $cm);
require_capability('mod/bunnyvideo:view', context_module::instance($cm->id));
require_sesskey();

// Check if completion tracking is enabled for this instance
$completionEnabled = !empty($bunnyvideo->completionvideo);
$threshold         = !empty($bunnyvideo->completionpercent) ? (int)$bunnyvideo->completionpercent : 80;

if (!$completionEnabled) {
    echo json_encode(['tracking' => 'disabled']);
    exit;
}

// Update progress using instance-specific threshold
$newlycompleted = bunnyvideo_update_progress(
    $bunnyvideo->id,
    $USER->id,
    $watchtime,
    $duration,
    $maxwatched
);

// Fetch updated progress
$progress = bunnyvideo_get_progress($bunnyvideo->id, $USER->id);

// Return response
echo json_encode([
    'success'          => true,
    'watchtime'        => $progress['watchtime'],
    'duration'         => $progress['duration'],
    'maxwatched'       => $progress['maxwatched'],
    'percentcomplete'  => $progress['percentcomplete'],
    'completed'        => $progress['completed'],
    'newly_completed'  => $newlycompleted
]);
