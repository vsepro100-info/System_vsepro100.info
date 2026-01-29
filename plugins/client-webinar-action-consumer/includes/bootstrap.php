<?php

defined('ABSPATH') || exit;

require_once __DIR__ . '/module.php';

add_action('client_webinar_scenario_action_route_state', 'client_webinar_action_consumer_handle_route_state', 10, 2);
add_action('client_webinar_scenario_action_screen_transition', 'client_webinar_action_consumer_handle_screen_transition', 10, 2);
add_action('client_webinar_scenario_action_screen_selection', 'client_webinar_action_consumer_handle_screen_transition', 10, 2);
