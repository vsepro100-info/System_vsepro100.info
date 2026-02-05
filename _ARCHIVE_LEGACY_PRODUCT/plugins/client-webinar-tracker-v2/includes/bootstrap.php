<?php

defined('ABSPATH') || exit;

require_once __DIR__ . '/module.php';

add_action('template_redirect', 'client_webinar_entered_maybe_emit');

add_action('wp_ajax_client_webinar_completed', 'client_webinar_completed_handler');
add_action('wp_ajax_nopriv_client_webinar_completed', 'client_webinar_completed_handler');
