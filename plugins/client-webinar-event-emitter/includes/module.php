<?php

defined('ABSPATH') || exit;

function client_webinar_event_emitter_handle_completed($context) {
    $context = is_array($context) ? $context : array();

    if (!client_webinar_event_emitter_is_allowed_source($context, 'client-webinar-tracker-v2')) {
        return;
    }

    $payload = client_webinar_event_emitter_build_completed_payload($context);
    if (empty($payload)) {
        return;
    }

    if (!client_webinar_event_emitter_mark_processed('webinar_completed', $payload, $context)) {
        return;
    }

    error_log('client_webinar_event_emitter: emit webinar_completed for webinar ' . $payload['webinar_id'] . ' lead ' . $payload['lead_id']);

    do_action('webinar_completed', $payload);
}

function client_webinar_event_emitter_is_allowed_source(array $context, $expected_source) {
    $source = $context['source'] ?? '';

    return is_string($source) && $source === $expected_source;
}

function client_webinar_event_emitter_build_completed_payload(array $context) {
    $lead_id = isset($context['lead_id']) ? absint($context['lead_id']) : 0;
    $webinar_id = isset($context['webinar_id']) ? (string) $context['webinar_id'] : '';
    $timestamp = isset($context['timestamp']) ? (int) $context['timestamp'] : 0;

    if ($lead_id === 0 || $webinar_id === '' || $timestamp === 0) {
        return array();
    }

    $payload = array(
        'lead_id' => $lead_id,
        'webinar_id' => $webinar_id,
        'webinar_type' => isset($context['webinar_type']) ? (string) $context['webinar_type'] : '',
        'session_id' => isset($context['session_id']) ? (string) $context['session_id'] : '',
        'completed_at' => $timestamp,
        'source' => 'client-webinar-tracker-v2',
    );

    return apply_filters('client_webinar_event_emitter_payload', $payload, $context);
}

function client_webinar_event_emitter_mark_processed($event, array $payload, array $context) {
    $fingerprint = client_webinar_event_emitter_fingerprint($event, $payload, $context);
    if ($fingerprint === '') {
        return false;
    }

    $key = 'client_webinar_emitter_' . $fingerprint;

    if (get_transient($key)) {
        return false;
    }

    set_transient($key, 1, DAY_IN_SECONDS);

    return true;
}

function client_webinar_event_emitter_fingerprint($event, array $payload, array $context) {
    $normalized = array(
        'event' => $event,
        'payload' => $payload,
        'context' => $context,
    );

    client_webinar_event_emitter_sort_recursive($normalized);

    $encoded = wp_json_encode($normalized);
    if ($encoded === false) {
        $encoded = '';
    }

    return md5($encoded);
}

function client_webinar_event_emitter_sort_recursive(array &$data) {
    foreach ($data as &$value) {
        if (is_array($value)) {
            client_webinar_event_emitter_sort_recursive($value);
        }
    }

    unset($value);

    ksort($data);
}
