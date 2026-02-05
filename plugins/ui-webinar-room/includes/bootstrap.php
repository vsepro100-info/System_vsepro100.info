<?php

defined('ABSPATH') || exit;

require_once __DIR__ . '/module.php';

add_shortcode('webinar_room', 'ui_webinar_room_render_shortcode');
add_shortcode('whieda_live_room', 'ui_webinar_room_render_shortcode');

add_action('rest_api_init', 'ui_webinar_room_register_state_routes');
