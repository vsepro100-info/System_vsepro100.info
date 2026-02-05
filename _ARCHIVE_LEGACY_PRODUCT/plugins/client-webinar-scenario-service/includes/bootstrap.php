<?php

defined('ABSPATH') || exit;

require_once __DIR__ . '/module.php';

add_action('webinar_registered', 'client_webinar_scenario_service_handle_webinar_registered', 10, 1);
add_action('webinar_entered', 'client_webinar_scenario_service_handle_webinar_entered', 10, 1);
add_action('webinar_left', 'client_webinar_scenario_service_handle_webinar_left', 10, 1);
add_action('webinar_completed', 'client_webinar_scenario_service_handle_webinar_completed', 10, 1);
add_action('post_webinar_form_submitted', 'client_webinar_scenario_service_handle_post_webinar_form_submitted', 10, 1);
