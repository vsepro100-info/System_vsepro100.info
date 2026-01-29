<?php

defined('ABSPATH') || exit;

require_once __DIR__ . '/module.php';

add_action('client_webinar_attendance_classified', 'client_webinar_attendance_telegram_notify', 10, 1);
