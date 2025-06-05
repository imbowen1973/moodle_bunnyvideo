<?php
// File: mod/bunnyvideo/mod_form.php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_bunnyvideo_mod_form extends moodleform_mod {

    function definition() {
        $mform = $this->_form;

        // General settings
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        // Video settings
        $mform->addElement('header', 'videosettings', get_string('videosettings', 'bunnyvideo'));

        $mform->addElement('text', 'videopath', get_string('videopath', 'bunnyvideo'), ['size' => '64']);
        $mform->setType('videopath', PARAM_TEXT);
        $mform->addRule('videopath', null, 'required', null, 'client');
        $mform->addHelpButton('videopath', 'videopath', 'bunnyvideo');

        $mform->addElement('text', 'posterurl', get_string('posterurl', 'bunnyvideo'), ['size' => '64']);
        $mform->setType('posterurl', PARAM_URL);
        $mform->addHelpButton('posterurl', 'posterurl', 'bunnyvideo');

        // Standard course module settings
        $this->standard_coursemodule_elements();

        // Action buttons
        $this->add_action_buttons();
    }

    function add_completion_rules() {
        $mform =& $this->_form;

        $mform->addElement('checkbox', 'completionvideo', '', get_string('completionvideo', 'bunnyvideo'));
        $mform->addHelpButton('completionvideo', 'completionvideo', 'bunnyvideo');

        $percentoptions = [];
        for ($i = 5; $i <= 100; $i += 5) {
            $percentoptions[$i] = $i . '%';
        }

        $defaultthreshold = get_config('mod_bunnyvideo', 'completionthreshold') ?: 80;

        $mform->addElement('select', 'completionpercent', get_string('completionpercent', 'bunnyvideo'), $percentoptions);
        $mform->setDefault('completionpercent', $defaultthreshold);
        $mform->addHelpButton('completionpercent', 'completionpercent', 'bunnyvideo');
        $mform->disabledIf('completionpercent', 'completionvideo', 'notchecked');

        return ['completionvideo', 'completionpercent'];
    }

    function completion_rule_enabled($data) {
        return !empty($data['completionvideo']);
    }

    function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }

        // Only enforce if auto-tracking is active
        if (!empty($data->completionunlocked)) {
            $autocompletion = !empty($data->completion) && $data->completion == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completionvideo) || !$autocompletion) {
                $data->completionvideo = 0;
            }

            if (empty($data->completionpercent) || $data->completionpercent < 5 || $data->completionpercent > 100) {
                $data->completionpercent = get_config('mod_bunnyvideo', 'completionthreshold') ?: 80;
            }
        }

        return $data;
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!empty($data['completionvideo'])) {
            if (empty($data['completionpercent']) || $data['completionpercent'] < 5 || $data['completionpercent'] > 100) {
                $errors['completionpercent'] = get_string('completionpercentrequired', 'bunnyvideo');
            }
        }

        return $errors;
    }
}
