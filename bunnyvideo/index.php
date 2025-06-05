<?php
// File: mod/bunnyvideo/index.php

require_once(__DIR__.'/../../config.php');

$id = required_param('id', PARAM_INT); // Course ID

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
require_login($course);

$PAGE->set_url('/mod/bunnyvideo/index.php', array('id' => $id));
$PAGE->set_title(get_string('modulenameplural', 'bunnyvideo'));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'bunnyvideo'));

if (! $bunnyvideos = get_all_instances_in_course('bunnyvideo', $course)) {
    echo $OUTPUT->notification(get_string('nobunnyvideos', 'bunnyvideo'));
} else {
    $table = new html_table();
    $table->head  = array(get_string('name'), get_string('intro', 'bunnyvideo'));
    $table->align = array('left', 'left');

    foreach ($bunnyvideos as $bunnyvideo) {
        $link = html_writer::link(new moodle_url('/mod/bunnyvideo/view.php', array('id' => $bunnyvideo->coursemodule)), format_string($bunnyvideo->name));
        $table->data[] = array($link, format_module_intro('bunnyvideo', $bunnyvideo, $bunnyvideo->coursemodule));
    }

    echo html_writer::table($table);
}

echo $OUTPUT->footer();
