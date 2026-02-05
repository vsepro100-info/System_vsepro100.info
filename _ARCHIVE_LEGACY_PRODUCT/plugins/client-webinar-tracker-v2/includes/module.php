<?php

defined('ABSPATH') || exit;

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
 * @param string $status
 * @return string
 */
function client_webinar_tracker_normalize_status($status) {
    $status = (string) sanitize_key($status);
    if ($status === 'finished') {
        $status = 'ended';
    }

    return $status;
}

/**
 * @param string $webinar_id
 * @return string
 */
function client_webinar_tracker_get_webinar_status($webinar_id) {
    $webinar_data = apply_filters('core_webinar_get', (int) $webinar_id, array());
    $status = isset($webinar_data['status']) ? (string) $webinar_data['status'] : 'scheduled';

    return client_webinar_tracker_normalize_status($status);
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

    if ($lead_id <= 0) {
        error_log('client_webinar_tracker_v2: denied client_webinar_entered for webinar ' . $webinar_id . ' guest');
        return;
    }

    $current_status = client_webinar_tracker_get_webinar_status($webinar_id);
    if ($current_status !== 'live') {
        error_log(
            'client_webinar_tracker_v2: denied client_webinar_entered for webinar ' .
            $webinar_id .
            ' status ' .
            $current_status .
            ' lead ' .
            $lead_id
        );
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

    error_log('client_webinar_tracker_v2: emit client_webinar_entered for webinar ' . $webinar_id . ' lead ' . $lead_id);

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
    if ($lead_id <= 0) {
        error_log('client_webinar_tracker_v2: denied client_webinar_completed for webinar ' . $webinar_id . ' guest');
        wp_send_json_error(array('message' => 'forbidden'), 403);
    }

    $current_status = client_webinar_tracker_get_webinar_status($webinar_id);
    if ($current_status !== 'ended') {
        error_log(
            'client_webinar_tracker_v2: denied client_webinar_completed for webinar ' .
            $webinar_id .
            ' status ' .
            $current_status .
            ' lead ' .
            $lead_id
        );
        wp_send_json_error(array('message' => 'invalid_state'), 403);
    }

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

    error_log('client_webinar_tracker_v2: emit client_webinar_completed for webinar ' . $webinar_id . ' lead ' . $lead_id);

    do_action('client_webinar_completed', $ctx);

    wp_send_json_success(
        array(
            'ok' => true,
        )
    );
}
