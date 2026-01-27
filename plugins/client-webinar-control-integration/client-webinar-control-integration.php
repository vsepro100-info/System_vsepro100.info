<?php
/**
 * Plugin Name: Client Webinar Control Integration
 * Description: AJAX integration for starting/stopping webinars via Core hooks.
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

define('CLIENT_WEBINAR_CONTROL_NONCE_ACTION', 'core_webinar_control');

define('CLIENT_WEBINAR_CONTROL_START_ACTION', 'core_webinar_start');
define('CLIENT_WEBINAR_CONTROL_STOP_ACTION', 'core_webinar_stop');

function client_webinar_control_integration_register_actions() {
    add_action('wp_ajax_' . CLIENT_WEBINAR_CONTROL_START_ACTION, 'client_webinar_control_handle_start');
    add_action('wp_ajax_' . CLIENT_WEBINAR_CONTROL_STOP_ACTION, 'client_webinar_control_handle_stop');
}

add_action('init', 'client_webinar_control_integration_register_actions');

function client_webinar_control_require_permission() {
    if (!is_user_logged_in() || !current_user_can('edit_pages')) {
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
