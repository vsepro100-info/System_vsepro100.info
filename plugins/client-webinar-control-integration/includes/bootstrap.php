<?php

defined('ABSPATH') || exit;

require_once __DIR__ . '/module.php';

add_action('init', 'client_webinar_control_integration_register_actions');
