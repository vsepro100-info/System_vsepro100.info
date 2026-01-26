<?php
/**
 * Plugin Name: Client Webinar Tracker v2
 * Description: Безопасный сервисный трекер клиентских событий вебинара (только эмиссия событий).
 * Version: 0.1.1
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_action('template_redirect', 'client_webinar_entered_maybe_emit');

/**
 * Формирует отпечаток клиента для защиты от повторной отправки.
 *
 * @return string
 */
function client_webinar_tracker_get_fingerprint() {
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        return (string) $user_id;
    }

    $ip_address = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';

    return md5($ip_address . $user_agent);
}

/**
 * Определяет контекст входа в вебинар.
 *
 * @return array{webinar_id:string,lead_id:int}
 */
function client_webinar_tracker_get_entry_context() {
    $webinar_id = '';
    $lead_id = 0;

    if (isset($_GET['webinar_id'])) {
        $webinar_id = (string) sanitize_text_field(wp_unslash($_GET['webinar_id']));
    } else {
        $query_webinar_id = get_query_var('webinar_id');
        if (!empty($query_webinar_id)) {
            $webinar_id = (string) $query_webinar_id;
        }
    }

    if (isset($_GET['lead_id'])) {
        $lead_id = absint($_GET['lead_id']);
    } else {
        $query_lead_id = get_query_var('lead_id');
        if (!empty($query_lead_id)) {
            $lead_id = absint($query_lead_id);
        }
    }

    return array(
        'webinar_id' => $webinar_id,
        'lead_id' => $lead_id,
    );
}

/**
 * Эмитирует факт входа в вебинар при загрузке страницы.
 *
 * @return void
 */
function client_webinar_entered_maybe_emit() {
    if (is_admin() || wp_doing_ajax()) {
        return;
    }

    $context = client_webinar_tracker_get_entry_context();
    $webinar_id = $context['webinar_id'];
    $lead_id = $context['lead_id'];

    if ($webinar_id === '') {
        return;
    }

    $fingerprint = client_webinar_tracker_get_fingerprint();
    $key = 'client_webinar_entered_' . $fingerprint . '_' . $webinar_id;

    if (get_transient($key)) {
        return;
    }

    set_transient($key, 1, DAY_IN_SECONDS);

    $ctx = array(
        'event' => 'client_webinar_entered',
        'webinar_id' => $webinar_id,
        'lead_id' => $lead_id,
        'timestamp' => time(),
        'source' => 'client-webinar-tracker-v2',
    );

    do_action('client_webinar_entered', $ctx);
}

/**
 * Обработчик AJAX: завершение просмотра вебинара.
 *
 * @return void
 */
function client_webinar_completed_handler() {
    if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_send_json_error();
    }

    $webinar_id = isset($_POST['webinar_id'])
        ? (string) sanitize_text_field(wp_unslash($_POST['webinar_id']))
        : '';
    $lead_id = isset($_POST['lead_id']) ? absint($_POST['lead_id']) : 0;
    $fingerprint = client_webinar_tracker_get_fingerprint();
    $key = 'client_webinar_completed_' . $fingerprint . '_' . $webinar_id;

    if (get_transient($key)) {
        wp_send_json_success(
            array(
                'ok' => true,
                'deduped' => true,
            )
        );
    }

    set_transient($key, 1, DAY_IN_SECONDS);

    $ctx = array(
        'event' => 'client_webinar_completed',
        'webinar_id' => $webinar_id,
        'lead_id' => $lead_id,
        'timestamp' => time(),
        'source' => 'client-webinar-tracker-v2',
    );

    do_action('client_webinar_completed', $ctx);

    wp_send_json_success(
        array(
            'ok' => true,
        )
    );
}

add_action('wp_ajax_client_webinar_completed', 'client_webinar_completed_handler');
add_action('wp_ajax_nopriv_client_webinar_completed', 'client_webinar_completed_handler');
