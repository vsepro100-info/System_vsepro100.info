<?php

defined('ABSPATH') || exit;

function client_webinar_attendance_handle_entered($payload) {
    $context = client_webinar_attendance_normalize_payload($payload);
    if ($context['webinar_id'] === '') {
        return;
    }

    error_log('client_webinar_attendance_service: entered webinar ' . $context['webinar_id'] . ' lead ' . $context['lead_id']);

    $entered_key = client_webinar_attendance_entered_key($context['lead_id'], $context['webinar_id']);
    set_transient($entered_key, $context['timestamp'], DAY_IN_SECONDS);
}

function client_webinar_attendance_handle_completed($payload) {
    $context = client_webinar_attendance_normalize_payload($payload);
    if ($context['webinar_id'] === '') {
        return;
    }

    error_log('client_webinar_attendance_service: completed webinar ' . $context['webinar_id'] . ' lead ' . $context['lead_id']);

    $completed_key = client_webinar_attendance_completed_key($context['lead_id'], $context['webinar_id']);
    set_transient($completed_key, $context['timestamp'], DAY_IN_SECONDS);

    client_webinar_attendance_emit($context, true);

    $entered_key = client_webinar_attendance_entered_key($context['lead_id'], $context['webinar_id']);
    delete_transient($entered_key);
}

function client_webinar_attendance_handle_evaluate($payload) {
    $context = client_webinar_attendance_normalize_payload($payload);
    if ($context['webinar_id'] === '') {
        return;
    }

    $completed_key = client_webinar_attendance_completed_key($context['lead_id'], $context['webinar_id']);
    if (get_transient($completed_key)) {
        client_webinar_attendance_emit($context, true);
        return;
    }

    $entered_key = client_webinar_attendance_entered_key($context['lead_id'], $context['webinar_id']);
    if (get_transient($entered_key)) {
        client_webinar_attendance_emit($context, false);
    }
}

function client_webinar_attendance_emit(array $context, $attended) {
    $attended = (bool) $attended;
    $classification_key = client_webinar_attendance_classified_key(
        $context['lead_id'],
        $context['webinar_id'],
        $attended
    );

    if (get_transient($classification_key)) {
        return;
    }

    set_transient($classification_key, 1, DAY_IN_SECONDS);

    error_log(
        'client_webinar_attendance_service: classified attendance ' .
        ($attended ? 'attended' : 'not_attended') .
        ' for webinar ' . $context['webinar_id'] . ' lead ' . $context['lead_id']
    );

    if ($attended) {
        do_action(
            'user_attended',
            array(
                'lead_id' => $context['lead_id'],
                'webinar_id' => $context['webinar_id'],
                'timestamp' => $context['timestamp'],
            )
        );
    }

    do_action(
        'client_webinar_attendance_classified',
        array(
            'lead_id' => $context['lead_id'],
            'webinar_id' => $context['webinar_id'],
            'attended' => $attended,
            'timestamp' => $context['timestamp'],
        )
    );
}

function client_webinar_attendance_normalize_payload($payload) {
    $payload = is_array($payload) ? $payload : array();

    $lead_id = isset($payload['lead_id']) ? absint($payload['lead_id']) : 0;
    $webinar_id = isset($payload['webinar_id']) ? (string) $payload['webinar_id'] : '';
    $webinar_id = $webinar_id !== '' ? (string) sanitize_text_field($webinar_id) : '';
    $timestamp = isset($payload['timestamp']) ? (int) $payload['timestamp'] : time();

    return array(
        'lead_id' => $lead_id,
        'webinar_id' => $webinar_id,
        'timestamp' => $timestamp,
    );
}

function client_webinar_attendance_entered_key($lead_id, $webinar_id) {
    return 'client_webinar_attendance_entered_' . absint($lead_id) . '_' . md5((string) $webinar_id);
}

function client_webinar_attendance_completed_key($lead_id, $webinar_id) {
    return 'client_webinar_attendance_completed_' . absint($lead_id) . '_' . md5((string) $webinar_id);
}

function client_webinar_attendance_classified_key($lead_id, $webinar_id, $attended) {
    return 'client_webinar_attendance_classified_' . absint($lead_id) . '_' . md5((string) $webinar_id) . '_' . ($attended ? '1' : '0');
}
