<?php

defined('ABSPATH') || exit;

require_once __DIR__ . '/module.php';

add_action('client_webinar_completed', 'client_webinar_event_emitter_handle_completed', 10, 1);
