<?php

defined('ABSPATH') || exit;

require_once __DIR__ . '/module.php';

add_action('post_webinar_route', 'post_webinar_recording_followup_telegram_handle', 10, 2);
