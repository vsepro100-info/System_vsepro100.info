<?php

defined('ABSPATH') || exit;

add_action('webinar_registered', 'client_webinar_scenario_service_handle_webinar_registered', 10, 1);
add_action('webinar_entered', 'client_webinar_scenario_service_handle_webinar_entered', 10, 1);
add_action('webinar_left', 'client_webinar_scenario_service_handle_webinar_left', 10, 1);
add_action('webinar_completed', 'client_webinar_scenario_service_handle_webinar_completed', 10, 1);
add_action('post_webinar_form_submitted', 'client_webinar_scenario_service_handle_post_webinar_form_submitted', 10, 1);

function client_webinar_scenario_service_handle_webinar_registered($payload) {
    client_webinar_scenario_service_process_event('webinar_registered', $payload);
}

function client_webinar_scenario_service_handle_webinar_entered($payload) {
    client_webinar_scenario_service_process_event('webinar_entered', $payload);
}

function client_webinar_scenario_service_handle_webinar_left($payload) {
    client_webinar_scenario_service_process_event('webinar_left', $payload);
}

function client_webinar_scenario_service_handle_webinar_completed($payload) {
    client_webinar_scenario_service_process_event('webinar_completed', $payload);
}

function client_webinar_scenario_service_handle_post_webinar_form_submitted($payload) {
    client_webinar_scenario_service_process_event('post_webinar_form_submitted', $payload);
}

function client_webinar_scenario_service_process_event($event, $payload) {
    $payload = is_array($payload) ? $payload : array();

    $context = array_merge(
        array(
            'event' => $event,
        ),
        $payload
    );

    if (!client_webinar_scenario_service_mark_processed($event, $context)) {
        return;
    }

    $actions = client_webinar_scenario_service_build_actions($event, $context);

    foreach ($actions as $action) {
        do_action('client_webinar_scenario_action', $action, $context);

        if (isset($action['type']) && $action['type'] !== '') {
            do_action('client_webinar_scenario_action_' . $action['type'], $action, $context);
        }
    }

    do_action('client_webinar_scenario_actions_emitted', $event, $context, $actions);
}

function client_webinar_scenario_service_build_actions($event, array $context) {
    $state_map = array(
        'webinar_registered' => 'registered',
        'webinar_entered' => 'entered',
        'webinar_left' => 'left',
        'webinar_completed' => 'completed',
        'post_webinar_form_submitted' => 'post_form_submitted',
    );

    if (!isset($state_map[$event])) {
        return array();
    }

    $state = $state_map[$event];

    return array(
        array(
            'type' => 'route_state',
            'scenario' => 'client_webinar',
            'state' => $state,
            'event' => $event,
            'context' => $context,
        ),
        array(
            'type' => 'screen_transition',
            'scenario' => 'client_webinar',
            'screen' => 'client_webinar_' . $state,
            'event' => $event,
            'context' => $context,
        ),
    );
}

function client_webinar_scenario_service_mark_processed($event, array $context) {
    $fingerprint = client_webinar_scenario_service_fingerprint($event, $context);
    if ($fingerprint === '') {
        return false;
    }

    $key = 'client_webinar_scenario_' . $fingerprint;

    if (get_transient($key)) {
        return false;
    }

    set_transient($key, 1, DAY_IN_SECONDS);

    return true;
}

function client_webinar_scenario_service_fingerprint($event, array $context) {
    $normalized = $context;
    client_webinar_scenario_service_sort_recursive($normalized);

    $encoded = wp_json_encode($normalized);
    if ($encoded === false) {
        $encoded = '';
    }

    return md5($event . '|' . $encoded);
}

function client_webinar_scenario_service_sort_recursive(array &$data) {
    foreach ($data as &$value) {
        if (is_array($value)) {
            client_webinar_scenario_service_sort_recursive($value);
        }
    }

    unset($value);

    ksort($data);
}
