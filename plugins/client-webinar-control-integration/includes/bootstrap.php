<?php

defined('ABSPATH') || exit;

define('CLIENT_WEBINAR_CONTROL_NONCE_ACTION', 'core_webinar_control');

define('CLIENT_WEBINAR_CONTROL_START_ACTION', 'core_webinar_start');
define('CLIENT_WEBINAR_CONTROL_STOP_ACTION', 'core_webinar_stop');
define('CLIENT_WEBINAR_CONTROL_SET_CTA_VISIBILITY_ACTION', 'core_webinar_set_cta_visibility');
define('CLIENT_WEBINAR_CONTROL_GET_CTA_VISIBILITY_ACTION', 'core_webinar_get_cta_visibility');

function client_webinar_control_integration_register_actions() {
    // Admin-ajax handlers for webinar state are deprecated in favor of REST.
}

add_action('init', 'client_webinar_control_integration_register_actions');

function client_webinar_control_require_permission() {
    if (!is_user_logged_in() || !current_user_can('edit_pages')) {
        wp_send_json_error(array('message' => 'forbidden'), 403);
    }

    check_ajax_referer(CLIENT_WEBINAR_CONTROL_NONCE_ACTION, 'nonce');
}

function client_webinar_control_require_speaker_permission() {
    if (!is_user_logged_in() || !current_user_can('speaker')) {
        wp_send_json_error(array('message' => 'forbidden'), 403);
    }

    check_ajax_referer(CLIENT_WEBINAR_CONTROL_NONCE_ACTION, 'nonce');
}

function client_webinar_control_get_current_webinar_id() {
    return apply_filters('core_webinar_get_current', null);
}

function client_webinar_control_handle_start() {
    client_webinar_control_require_permission();

    $webinar_id = client_webinar_control_get_current_webinar_id();
    if (empty($webinar_id)) {
        wp_send_json_error(array('message' => 'no_webinar'), 404);
    }

    do_action(
        'core_webinar_set_status',
        (int) $webinar_id,
        'live',
        array(
            'source' => 'ajax',
            'action' => 'start',
            'user_id' => get_current_user_id(),
        )
    );

    wp_send_json_success(array('status' => 'live'));
}

function client_webinar_control_handle_stop() {
    client_webinar_control_require_permission();

    $webinar_id = client_webinar_control_get_current_webinar_id();
    if (empty($webinar_id)) {
        wp_send_json_error(array('message' => 'no_webinar'), 404);
    }

    do_action(
        'core_webinar_set_status',
        (int) $webinar_id,
        'ended',
        array(
            'source' => 'ajax',
            'action' => 'stop',
            'user_id' => get_current_user_id(),
        )
    );

    wp_send_json_success(array('status' => 'ended'));
}

function client_webinar_control_handle_set_cta_visibility() {
    client_webinar_control_require_speaker_permission();

    $webinar_id = client_webinar_control_get_current_webinar_id();
    if (empty($webinar_id)) {
        wp_send_json_error(array('message' => 'no_webinar'), 404);
    }

    $visibility = isset($_POST['visibility']) ? sanitize_key(wp_unslash($_POST['visibility'])) : '';
    if (!in_array($visibility, array('hidden', 'shown'), true)) {
        wp_send_json_error(array('message' => 'invalid_visibility'), 400);
    }

    do_action(
        'core_webinar_set_cta_visibility',
        (int) $webinar_id,
        $visibility,
        array(
            'source' => 'ajax',
            'action' => 'set_cta_visibility',
            'user_id' => get_current_user_id(),
        )
    );

    wp_send_json_success(array('cta_visibility' => $visibility));
}

function client_webinar_control_handle_get_cta_visibility() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'forbidden'), 403);
    }

    check_ajax_referer(CLIENT_WEBINAR_CONTROL_NONCE_ACTION, 'nonce');

    $webinar_id = client_webinar_control_get_current_webinar_id();
    if (empty($webinar_id)) {
        wp_send_json_error(array('message' => 'no_webinar'), 404);
    }

    $webinar_data = apply_filters('core_webinar_get', (int) $webinar_id, array());
    $cta_visibility = $webinar_data['cta_visibility'] ?? 'hidden';

    wp_send_json_success(array('cta_visibility' => $cta_visibility));
}
