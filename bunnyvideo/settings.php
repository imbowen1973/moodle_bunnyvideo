<?php
// File: mod/bunnyvideo/settings.php

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('mod_bunnyvideo', get_string('pluginname', 'bunnyvideo'));

    $settings->add(new admin_setting_configtext(
        'bunnyvideo/bunnycdnurl',
        get_string('bunnycdnurl', 'bunnyvideo'),
        'Enter the base URL of your Bunny.net CDN (e.g., https://yourzone.b-cdn.net)',
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'bunnyvideo/secretkey',
        get_string('secretkey', 'bunnyvideo'),
        'Enter the Bunny.net secret key for token signing.',
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'bunnyvideo/validduration',
        'Token Valid Duration (seconds)',
        'Duration in seconds that the token remains valid. Default is 300 seconds.',
        '300'
    ));

    $ADMIN->add('modsettings', $settings);
}
