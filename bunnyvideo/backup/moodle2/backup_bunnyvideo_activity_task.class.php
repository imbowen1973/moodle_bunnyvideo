<?php
// File: mod/bunnyvideo/backup/moodle2/backup_bunnyvideo_activity_task.class.php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/backup/moodle2/backup_activity_task.class.php');

class backup_bunnyvideo_activity_task extends backup_activity_task {

    protected function define_my_settings() {
        // No special settings for this activity.
    }

    protected function define_my_steps() {
        // Define the steps for backing up this activity.
        $this->add_step(new backup_bunnyvideo_activity_structure_step('bunnyvideo_structure', 'bunnyvideo.xml'));
    }

    static public function encode_content_links($content) {
        global $CFG;
        $base = preg_quote($CFG->wwwroot, "/");
        return preg_replace("/({$base}\/mod\/bunnyvideo\/view.php\?id\=)([0-9]+)/", '$@BUNNYVIDEOVIEWBYID*$2@$', $content);
    }
}

class backup_bunnyvideo_activity_structure_step extends backup_activity_structure_step {
    protected function define_structure() {
        $bunnyvideo = new backup_nested_element('bunnyvideo', array('id'), array('name', 'intro', 'introformat', 'videopath'));

        $bunnyvideo->set_source_table('bunnyvideo', array('id' => backup::VAR_ACTIVITYID));

        return $this->prepare_activity_structure($bunnyvideo);
    }
}
