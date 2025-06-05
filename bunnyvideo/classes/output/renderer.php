<?php
// File: mod/bunnyvideo/classes/output/renderer.php

namespace mod_bunnyvideo\output;

defined('MOODLE_INTERNAL') || die();

class renderer extends \plugin_renderer_base {

    public function bunnyvideo_player($bunnyvideo, $cm) {
        global $USER, $DB;

        $playerid = 'bunnyvideo-player-' . $cm->id;
        $secureurl = \bunnyvideo_generate_signed_url($bunnyvideo->videopath);

        // Use standard completion fields for tracking
        $completionEnabled = !empty($bunnyvideo->completionvideo);
        $threshold = isset($bunnyvideo->completionpercent) ? (int)$bunnyvideo->completionpercent : 80;

        $progress = [
            'watchtime' => 0,
            'duration' => 0,
            'maxwatched' => 0,
            'percentcomplete' => 0,
            'completed' => false
        ];

        if ($completionEnabled && $DB->get_manager()->table_exists('bunnyvideo_tracking')) {
            $progress = \bunnyvideo_get_progress($bunnyvideo->id, $USER->id);
        }

        $output = '';

        $output .= \html_writer::tag('link', '', [
            'rel' => 'stylesheet',
            'href' => 'https://vjs.zencdn.net/8.6.1/video-js.css'
        ]);
        $output .= \html_writer::tag('script', '', [
            'src' => 'https://vjs.zencdn.net/8.6.1/video.min.js'
        ]);

        $output .= $this->get_player_styles($playerid);

        if ($completionEnabled && $progress['duration'] > 0) {
            $output .= $this->render_progress_bar($progress);
        } elseif ($completionEnabled) {
            $output .= $this->render_progress_bar([
                'percentcomplete' => 0,
                'completed' => false
            ]);
        }

        $videoattributes = [
            'id' => $playerid,
            'class' => 'video-js vjs-default-skin',
            'controls' => 'controls',
            'preload' => 'auto',
            'width' => 800,
            'height' => 450,
            'data-setup' => '{}'
        ];

        if (!empty($bunnyvideo->posterurl)) {
            $videoattributes['poster'] = $bunnyvideo->posterurl;
        }

        $output .= \html_writer::start_tag('video', $videoattributes);
        $output .= \html_writer::tag('source', '', [
            'src' => $secureurl,
            'type' => 'application/x-mpegURL'
        ]);
        $output .= \html_writer::tag('p', get_string('novideosupport', 'bunnyvideo'));
        $output .= \html_writer::end_tag('video');

        // Inject JS with new params
        $output .= $this->get_player_javascript($playerid, $cm, $progress, $completionEnabled, $threshold);

        return \html_writer::div($output, 'bunnyvideo-container');
    }

    private function get_player_styles($playerid) {
        return \html_writer::tag('style', "
            .bunnyvideo-container {
                max-width: 800px;
                margin: 20px auto;
            }
            #{$playerid} {
                width: 100%;
                height: auto;
            }
            .bunnyvideo-progress { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; margin: 10px 0; text-align: center; }
            .bunnyvideo-progress-bar { width: 100%; height: 25px; background: #e9ecef; border-radius: 15px; overflow: hidden; margin: 10px 0; position: relative; }
            .bunnyvideo-progress-fill { height: 100%; background: linear-gradient(90deg, #28a745, #20c997); border-radius: 15px; transition: width 0.3s ease; position: relative; }
            .bunnyvideo-progress-text { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-weight: bold; color: #495057; z-index: 10; text-shadow: 1px 1px 2px rgba(255,255,255,0.8); }
            .vjs-progress-control .vjs-progress-holder::after {
                content: ''; position: absolute; top: 0; right: 0; height: 100%;
                background: rgba(220, 53, 69, 0.3); border-left: 2px solid #dc3545;
                pointer-events: none; z-index: 10;
            }
        ");
    }

    private function render_progress_bar($progress) {
        $percent = isset($progress['percentcomplete']) ? $progress['percentcomplete'] : 0;
        $completed = !empty($progress['completed']) ? get_string('completed', 'bunnyvideo') : '';
        $text = get_string('progress', 'bunnyvideo') . ': ' . $percent . '%' . ($completed ? " - $completed" : '');

        $bar = \html_writer::div('', 'bunnyvideo-progress-fill', [
            'id' => 'progress-fill',
            'style' => "width: {$percent}%"
        ]);
        $label = \html_writer::div($text, 'bunnyvideo-progress-text', ['id' => 'progress-text']);
        $container = \html_writer::div($bar . $label, 'bunnyvideo-progress-bar');

        return \html_writer::div($container, 'bunnyvideo-progress');
    }

    private function get_player_javascript($playerid, $cm, $progress, $completionEnabled, $threshold) {
        global $CFG;

        $trackurl = new \moodle_url('/mod/bunnyvideo/track.php');
        $sesskey = sesskey();

        $jsonprogress = json_encode($progress);
        $cmid = (int)$cm->id;
        $enabled = $completionEnabled ? 'true' : 'false';
        $threshold = (int)$threshold;
        $progressstr = get_string('progress', 'bunnyvideo');
        $completedstr = get_string('completed', 'bunnyvideo');

        return \html_writer::script("
            (function() {
                if (typeof videojs === 'undefined') return setTimeout(arguments.callee, 100);

                var player = videojs('$playerid', {
                    fluid: true,
                    responsive: true,
                    playbackRates: [0.75, 1, 1.25, 1.5],
                    html5: { hls: { overrideNative: true } }
                });

                var maxWatched = {$progress['maxwatched']};
                var saveInterval = 5;
                var threshold = $threshold;
                var completionEnabled = $enabled;
                var lastSaveTime = 0;
                var progressLabel = '$progressstr';
                var completedLabel = '$completedstr';

                player.ready(function() {
                    if (completionEnabled && {$progress['watchtime']} > 0) {
                        player.currentTime({$progress['watchtime']});
                    }

                    player.on('loadedmetadata', function() {
                        var duration = Math.floor(player.duration() || 0);
                        if (completionEnabled && duration > 0) {
                            updateProgressBarOverlay(duration);
                            updateProgressBar(duration);
                        }
                    });
                });

                if (completionEnabled) {
                    player.on('seeking', function() {
                        if (player.currentTime() > maxWatched + 2) {
                            player.currentTime(maxWatched);
                            showBlockedMessage();
                        }
                    });

                    player.on('timeupdate', function() {
                        var currentTime = Math.floor(player.currentTime());
                        var duration = Math.floor(player.duration());
                        if (currentTime > maxWatched) maxWatched = currentTime;

                        if (currentTime - lastSaveTime >= saveInterval) {
                            saveProgress(currentTime, duration, maxWatched);
                            lastSaveTime = currentTime;
                        }

                        updateProgressBarOverlay(duration);
                        updateProgressBar(duration);
                    });

                    player.on('ended', function() {
                        var duration = Math.floor(player.duration() || 0);
                        saveProgress(duration, duration, duration);
                        updateProgressBar(duration);
                    });
                }

                function updateProgressBarOverlay(duration) {
                    var overlay = player.el().querySelector('.vjs-progress-overlay');
                    var progressHolder = player.el().querySelector('.vjs-progress-holder');
                    if (!progressHolder || duration === 0) return;

                    if (!overlay) {
                        overlay = document.createElement('div');
                        overlay.className = 'vjs-progress-overlay';
                        overlay.style.cssText = 'position:absolute;top:0;height:100%;background:rgba(220,53,69,0.3);border-left:2px solid #dc3545;pointer-events:none;z-index:10;';
                        progressHolder.appendChild(overlay);
                    }

                    var lockedPercent = 100 - ((maxWatched / duration) * 100);
                    overlay.style.width = lockedPercent + '%';
                    overlay.style.right = '0';
                }

                function updateProgressBar(duration) {
                    var fill = document.getElementById('progress-fill');
                    var label = document.getElementById('progress-text');
                    if (!fill || !label || duration === 0) return;

                    var percent = Math.floor((maxWatched / duration) * 100);
                    if (percent > 100) percent = 100;
                    fill.style.width = percent + '%';
                    label.textContent = progressLabel + ': ' + percent + '%';
                    if (percent >= threshold) {
                        label.textContent += ' - ' + completedLabel;
                    }
                }

                function saveProgress(watchtime, duration, maxwatched) {
                    if (!completionEnabled) return;
                    fetch('$trackurl', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'id=$cmid&watchtime=' + watchtime + '&duration=' + duration + '&maxwatched=' + maxwatched + '&sesskey=$sesskey'
                    });
                }

                function showBlockedMessage() {
                    var existing = document.querySelector('.seek-blocked-message');
                    if (existing) existing.remove();

                    var message = document.createElement('div');
                    message.className = 'seek-blocked-message alert alert-warning';
                    message.style.cssText = 'position:absolute;top:10px;left:50%;transform:translateX(-50%);z-index:1000;padding:10px;background:#fff3cd;border:1px solid #ffeaa7;border-radius:5px;';
                    message.textContent = 'You cannot skip ahead in this video.';
                    player.el().style.position = 'relative';
                    player.el().appendChild(message);

                    setTimeout(() => { if (message.parentNode) message.parentNode.removeChild(message); }, 3000);
                }
            })();
        ");
    }
}
