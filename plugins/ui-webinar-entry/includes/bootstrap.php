<?php

defined('ABSPATH') || exit;

require_once __DIR__ . '/module.php';

add_shortcode('webinar_entry', 'ui_webinar_entry_render_shortcode');
