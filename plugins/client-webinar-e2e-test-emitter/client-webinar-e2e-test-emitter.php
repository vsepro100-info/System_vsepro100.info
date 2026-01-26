<?php
/**
 * Plugin Name: Client Webinar E2E Test Emitter
 * Description: Временный тестовый эмиттер raw-сигнала входа клиента в вебинар (только проверка E2E).
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

/**
 * AJAX handler: emits raw client_webinar_entered signal for E2E verification.
 *
 * @return void
 */
function client_webinar_e2e_test_emitter_handle_entered() {
    if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_send_json_error();
        wp_die();
    }

    $context = array(
        'event' => 'client_webinar_entered',
        'lead_id' => isset($_POST['lead_id']) ? absint($_POST['lead_id']) : 0,
        'webinar_id' => isset($_POST['webinar_id'])
            ? (string) sanitize_text_field(wp_unslash($_POST['webinar_id']))
            : '',
        'timestamp' => isset($_POST['timestamp']) ? (int) $_POST['timestamp'] : time(),
        'source' => 'client-webinar-tracker-v2',
    );

    do_action('client_webinar_entered', $context);

    wp_send_json_success(
        array(
            'ok' => true,
        )
    );
    wp_die();
}

add_action('wp_ajax___e2e_webinar_entered', 'client_webinar_e2e_test_emitter_handle_entered');
add_action('wp_ajax_nopriv___e2e_webinar_entered', 'client_webinar_e2e_test_emitter_handle_entered');
