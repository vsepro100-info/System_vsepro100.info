<?php

defined('ABSPATH') || exit;

require_once __DIR__ . '/module.php';

add_action('client_webinar_entered', 'client_webinar_attendance_handle_entered', 10, 1);
add_action('client_webinar_completed', 'client_webinar_attendance_handle_completed', 10, 1);
add_action('client_webinar_attendance_evaluate', 'client_webinar_attendance_handle_evaluate', 10, 1);
