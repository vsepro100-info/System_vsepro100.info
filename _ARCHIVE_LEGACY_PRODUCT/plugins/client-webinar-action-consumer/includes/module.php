<?php

defined('ABSPATH') || exit;

function client_webinar_action_consumer_handle_route_state($action, $context) {
    $action = is_array($action) ? $action : array();
    $context = is_array($context) ? $context : array();

    if (!client_webinar_action_consumer_is_allowed_action($action, 'route_state')) {
        return;
    }

    $identity = client_webinar_action_consumer_get_identity($action, $context);
    if ($identity === '') {
        return;
    }

    if (!client_webinar_action_consumer_mark_processed($action, $context, $identity)) {
        return;
    }

    $state = $action['state'] ?? '';
    if ($state === '') {
        return;
    }

    $key = client_webinar_action_consumer_state_option_key('route_state', $identity);
    $previous = get_option($key, '');

    if ($previous === $state) {
        return;
    }

    update_option($key, $state, false);

    do_action('client_webinar_route_state_updated', $identity, $state, $action, $context);
}

function client_webinar_action_consumer_handle_screen_transition($action, $context) {
    $action = is_array($action) ? $action : array();
    $context = is_array($context) ? $context : array();

    if (!client_webinar_action_consumer_is_allowed_action($action, 'screen_transition')) {
        return;
    }

    $identity = client_webinar_action_consumer_get_identity($action, $context);
    if ($identity === '') {
        return;
    }

    if (!client_webinar_action_consumer_mark_processed($action, $context, $identity)) {
        return;
    }

    $screen = $action['screen'] ?? '';
    if ($screen === '') {
        return;
    }

    $key = client_webinar_action_consumer_state_option_key('screen', $identity);
    $previous = get_option($key, '');

    if ($previous === $screen) {
        return;
    }

    update_option($key, $screen, false);

    do_action('client_webinar_route_screen_updated', $identity, $screen, $action, $context);
}

function client_webinar_action_consumer_is_allowed_action(array $action, $expected_type) {
    $type = $action['type'] ?? '';
    if ($type === 'screen_selection') {
        $type = 'screen_transition';
    }

    if ($type !== $expected_type) {
        return false;
    }

    $scenario = $action['scenario'] ?? '';
    return $scenario === 'client_webinar';
}

function client_webinar_action_consumer_get_identity(array $action, array $context) {
    $identity = '';

    $candidate_map = array(
        'lead_id' => 'lead',
        'user_id' => 'user',
        'client_id' => 'client',
    );

    foreach ($candidate_map as $field => $prefix) {
        $value = $action[$field] ?? $context[$field] ?? null;
        if ($value !== null && $value !== '') {
            $identity = $prefix . ':' . $value;
            break;
        }
    }

    $identity = apply_filters('client_webinar_action_consumer_identity', $identity, $action, $context);

    if (!is_string($identity)) {
        return '';
    }

    return trim($identity);
}

function client_webinar_action_consumer_state_option_key($suffix, $identity) {
    $hash = md5($identity);
    return 'client_webinar_' . $suffix . '_' . $hash;
}

function client_webinar_action_consumer_mark_processed(array $action, array $context, $identity) {
    $fingerprint = client_webinar_action_consumer_fingerprint($action, $context, $identity);
    if ($fingerprint === '') {
        return false;
    }

    $key = 'client_webinar_action_' . $fingerprint;
    return add_option($key, 1, '', false);
}

function client_webinar_action_consumer_fingerprint(array $action, array $context, $identity) {
    $normalized = array(
        'identity' => $identity,
        'action' => $action,
        'context' => $context,
    );

    client_webinar_action_consumer_sort_recursive($normalized);

    $encoded = wp_json_encode($normalized);
    if ($encoded === false) {
        $encoded = '';
    }

    return md5($encoded);
}

function client_webinar_action_consumer_sort_recursive(array &$data) {
    foreach ($data as &$value) {
        if (is_array($value)) {
            client_webinar_action_consumer_sort_recursive($value);
        }
    }

    unset($value);

    ksort($data);
}

function client_webinar_action_consumer_get_route_state($identity) {
    if (!is_string($identity) || $identity === '') {
        return '';
    }

    $key = client_webinar_action_consumer_state_option_key('route_state', $identity);
    $state = get_option($key, '');

    return apply_filters('client_webinar_route_state', $state, $identity);
}

function client_webinar_action_consumer_get_screen($identity) {
    if (!is_string($identity) || $identity === '') {
        return '';
    }

    $key = client_webinar_action_consumer_state_option_key('screen', $identity);
    $screen = get_option($key, '');

    return apply_filters('client_webinar_route_screen', $screen, $identity);
}
