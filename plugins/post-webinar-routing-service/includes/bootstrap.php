<?php

defined('ABSPATH') || exit;

require_once __DIR__ . '/module.php';

add_action('client_webinar_attendance_classified', 'post_webinar_routing_service_handle_attendance', 10, 1);
