<?php

defined('ABSPATH') || exit;

require_once __DIR__ . '/module.php';

add_action('init', 'autowebinar_delivery_init');
add_action('core_ingest_event', 'autowebinar_delivery_handle_ingest', 10, 1);
