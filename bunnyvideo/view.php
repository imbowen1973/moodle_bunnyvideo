<?php
// File: mod/bunnyvideo/view.php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$id = required_param('id', PARAM_INT); // Course module ID

$cm = get_coursemodule_from_id('bunnyvideo', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$bunnyvideo = $DB->get_record('bunnyvideo', ['id' => $cm->instance], '*', MUST_EXIST);

require_course_login($course, true, $cm);

// Set up page.
$PAGE->set_url('/mod/bunnyvideo/view.php', ['id' => $cm->id]);
$PAGE->set_title($bunnyvideo->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_context(context_module::instance($cm->id));

// Option 1: Disable the activity header completely (uncomment if you want manual control)
// $PAGE->activityheader->disable();

// Option 2: Configure the activity header (recommended)
// This keeps the header but removes duplication
$PAGE->activityheader->set_attrs([
    'hidecompletion' => false,
    'description' => ''  // Prevents automatic description display
]);

// Completion tracking trigger (manual or auto).
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Get renderer.
$renderer = $PAGE->get_renderer('mod_bunnyvideo');

// Output page.
echo $OUTPUT->header();

// The activity header will show the title automatically, so we don't need this:
// echo $OUTPUT->heading(format_string($bunnyvideo->name));

// Show the description manually since we disabled it in the activity header
if (!empty($bunnyvideo->intro)) {
    echo $OUTPUT->box(format_module_intro('bunnyvideo', $bunnyvideo, $cm->id), 'generalbox mod_introbox', 'bunnyvideointro');
}

// Render the video player.
echo $renderer->bunnyvideo_player($bunnyvideo, $cm);

echo $OUTPUT->footer();