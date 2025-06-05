<?php
// File: mod/bunnyvideo/lib.php

defined('MOODLE_INTERNAL') || die();

function bunnyvideo_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

function bunnyvideo_add_instance($data, $mform) {
    global $DB;
    $data->timecreated = time();
    return $DB->insert_record('bunnyvideo', $data);
}

function bunnyvideo_update_instance($data, $mform) {
    global $DB;
    $data->timemodified = time();
    $data->id = $data->instance;
    return $DB->update_record('bunnyvideo', $data);
}

function bunnyvideo_delete_instance($id) {
    global $DB;
    $DB->delete_records('bunnyvideo_tracking', ['bunnyvideoid' => $id]);
    return $DB->delete_records('bunnyvideo', ['id' => $id]);
}

function bunnyvideo_generate_signed_url($videopath) {
    $secretkey     = get_config('bunnyvideo', 'secretkey');
    $cdnurl        = get_config('bunnyvideo', 'bunnycdnurl');
    $validduration = get_config('bunnyvideo', 'validduration', 300); // Default 5 mins

    if (empty($secretkey) || empty($cdnurl)) {
        debugging('Bunny.net configuration is incomplete.', DEBUG_DEVELOPER);
        return $videopath;
    }

    $expiry = time() + $validduration;
    $fullpath = preg_match('~^https?://~', $videopath)
        ? $videopath
        : rtrim($cdnurl, '/') . '/' . ltrim($videopath, '/');

    $token = hash_hmac('sha256', $fullpath . $expiry, $secretkey);
    return $fullpath . '?token=' . $token . '&expires=' . $expiry;
}

function bunnyvideo_update_progress($bunnyvideoid, $userid, $watchtime, $duration, $maxwatched) {
    global $DB;

    if ($duration <= 0) {
        return false;
    }

    $instance = $DB->get_record('bunnyvideo', ['id' => $bunnyvideoid], '*', MUST_EXIST);

    // Only track if instance has tracking enabled
    if (empty($instance->completionvideo)) {
        return false;
    }

    $threshold = !empty($instance->completionpercent) ? (int)$instance->completionpercent : 80;
    $percentcomplete = floor(($maxwatched / $duration) * 100);
    $completed = $percentcomplete >= $threshold;

    $record = $DB->get_record('bunnyvideo_tracking', [
        'bunnyvideoid' => $bunnyvideoid,
        'userid' => $userid
    ]);

    $data = (object)[
        'bunnyvideoid'     => $bunnyvideoid,
        'userid'           => $userid,
        'watchtime'        => $watchtime,
        'duration'         => $duration,
        'maxwatched'       => $maxwatched,
        'percentcomplete'  => $percentcomplete,
        'completed'        => $completed ? 1 : 0,
        'timemodified'     => time()
    ];

    if ($record) {
        $data->id = $record->id;
        $DB->update_record('bunnyvideo_tracking', $data);
    } else {
        $data->timecreated = time();
        $DB->insert_record('bunnyvideo_tracking', $data);
    }

    // Mark complete in Moodle completion system
    $cm = get_coursemodule_from_instance('bunnyvideo', $bunnyvideoid, 0, false, MUST_EXIST);
    $completion = new completion_info(get_course($cm->course));

    if ($completed) {
        $completion->update_state($cm, COMPLETION_COMPLETE, $userid);
    }

    return $completed && (!$record || !$record->completed);
}

function bunnyvideo_get_progress($bunnyvideoid, $userid) {
    global $DB;

    $record = $DB->get_record('bunnyvideo_tracking', [
        'bunnyvideoid' => $bunnyvideoid,
        'userid' => $userid
    ]);

    if (!$record) {
        return [
            'watchtime'        => 0,
            'duration'         => 0,
            'maxwatched'       => 0,
            'percentcomplete'  => 0,
            'completed'        => false
        ];
    }

    return [
        'watchtime'        => (int)$record->watchtime,
        'duration'         => (int)$record->duration,
        'maxwatched'       => (int)$record->maxwatched,
        'percentcomplete'  => (int)$record->percentcomplete,
        'completed'        => (bool)$record->completed
    ];
}
