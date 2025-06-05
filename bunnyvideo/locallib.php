<?php
// File: mod/bunnyvideo/locallib.php

defined('MOODLE_INTERNAL') || die();

function bunnyvideo_generate_signed_url($videopath) {
    $secretkey = get_config('bunnyvideo', 'secretkey');
    $cdnurl = get_config('bunnyvideo', 'bunnycdnurl');
    $validduration = get_config('bunnyvideo', 'validduration', 300); // Default 5 minutes

    if (empty($secretkey) || empty($cdnurl)) {
        debugging('Bunny.net configuration is incomplete.', DEBUG_DEVELOPER);
        return $cdnurl . '/' . ltrim($videopath, '/');
    }

    $expiry = time() + $validduration;
    $fullpath = $cdnurl . '/' . ltrim($videopath, '/');

    $token = hash_hmac('sha256', $fullpath . $expiry, $secretkey);
    return $fullpath . '?token=' . $token . '&expires=' . $expiry;
}
