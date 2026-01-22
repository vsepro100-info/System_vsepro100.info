<?php
/**
 * Plugin Name: Client Webinar Tracker v2
 * Description: Безопасный сервисный трекер клиентских событий вебинара (только эмиссия событий).
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

/**
 * Формирует отпечаток клиента для защиты от повторной отправки.
 *
 * @return string
 */
function client_webinar_tracker_v2_get_fingerprint() {
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        return 'user_' . $user_id;
    }

    $ip_address = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';

    return 'anon_' . md5($ip_address . '|' . $user_agent);
}

/**
 * Обработчик AJAX: завершение просмотра вебинара.
 *
 * @return void
 */
function client_webinar_tracker_v2_handle_completed() {
    $fingerprint = client_webinar_tracker_v2_get_fingerprint();
    $key = 'client_webinar_completed_' . $fingerprint;

    if (get_transient($key)) {
        wp_send_json_success();
    }

    set_transient($key, 1, DAY_IN_SECONDS);

    do_action(
        'scenario_start',
        'client_webinar',
        array(
            'event' => 'client_webinar_completed',
            'source' => 'client-webinar-tracker-v2',
        )
    );

    wp_send_json_success();
}

add_action('wp_ajax_client_webinar_completed', 'client_webinar_tracker_v2_handle_completed');
add_action('wp_ajax_nopriv_client_webinar_completed', 'client_webinar_tracker_v2_handle_completed');
