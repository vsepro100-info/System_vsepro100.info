<?php

defined('ABSPATH') || exit;

require_once __DIR__ . '/module.php';

add_shortcode('webinar_public', 'ui_webinar_public_render_shortcode');
add_shortcode('whieda_webinar_public', 'ui_webinar_public_render_shortcode');
add_shortcode('webinar_admin', 'ui_webinar_admin_render_shortcode');
add_shortcode('whieda_live_admin', 'ui_webinar_admin_render_shortcode');
