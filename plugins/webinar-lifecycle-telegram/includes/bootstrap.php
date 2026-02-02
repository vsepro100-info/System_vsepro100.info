<?php

defined('ABSPATH') || exit;

require_once __DIR__ . '/module.php';

add_action('core_webinar_upsert', 'webinar_lifecycle_event_bridge_handle_upsert', 20, 2);
add_action('core_webinar_set_status', 'webinar_lifecycle_event_bridge_handle_status', 20, 3);

add_action('webinar_created', 'webinar_lifecycle_telegram_notify_created', 10, 1);
add_action('webinar_started', 'webinar_lifecycle_telegram_notify_started', 10, 1);
add_action('webinar_finished', 'webinar_lifecycle_telegram_notify_finished', 10, 1);
