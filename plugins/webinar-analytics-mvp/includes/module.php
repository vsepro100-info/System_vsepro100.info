<?php

defined('ABSPATH') || exit;

function webinar_analytics_mvp_handle_webinar_started($payload) {
    webinar_analytics_mvp_capture_event('webinar_started', $payload);
}

function webinar_analytics_mvp_handle_user_attended($payload) {
    webinar_analytics_mvp_capture_event('user_attended', $payload);
}

function webinar_analytics_mvp_handle_webinar_finished($payload) {
    webinar_analytics_mvp_capture_event('webinar_finished', $payload);
}

function webinar_analytics_mvp_handle_cta_shown($payload) {
    webinar_analytics_mvp_capture_event('cta_shown', $payload);
}

function webinar_analytics_mvp_handle_cta_clicked($payload) {
    webinar_analytics_mvp_capture_event('cta_clicked', $payload);
}

/**
 * @param string $event
 * @param array<string, mixed>|mixed $payload
 * @return void
 */
function webinar_analytics_mvp_capture_event($event, $payload) {
    try {
        $event = (string) sanitize_key($event);
        if (!in_array($event, webinar_analytics_mvp_allowed_events(), true)) {
            return;
        }

        $payload = is_array($payload) ? $payload : array();
        $webinar_id = webinar_analytics_mvp_extract_webinar_id($payload);
        if (!$webinar_id) {
            return;
        }

        $timestamp = webinar_analytics_mvp_extract_timestamp($payload);
        webinar_analytics_mvp_increment_event($webinar_id, $event, $timestamp);
    } catch (Throwable $error) {
        error_log('webinar_analytics_mvp: failed to capture ' . $event . ' (' . $error->getMessage() . ')');
    }
}

/**
 * @param array<string, mixed> $payload
 * @return int
 */
function webinar_analytics_mvp_extract_webinar_id(array $payload) {
    if (!array_key_exists('webinar_id', $payload)) {
        return 0;
    }

    return absint($payload['webinar_id']);
}

/**
 * @param array<string, mixed> $payload
 * @return int
 */
function webinar_analytics_mvp_extract_timestamp(array $payload) {
    if (isset($payload['timestamp'])) {
        return (int) $payload['timestamp'];
    }

    return time();
}

/**
 * @param int $webinar_id
 * @return array<string, mixed>
 */
function webinar_analytics_mvp_get_storage($webinar_id) {
    $webinar_id = absint($webinar_id);
    if (!$webinar_id) {
        return array();
    }

    $data = get_option(webinar_analytics_mvp_option_key($webinar_id), array());
    if (!is_array($data)) {
        $data = array();
    }

    if (!isset($data['counts']) || !is_array($data['counts'])) {
        $data['counts'] = array();
    }

    if (!isset($data['timestamps']) || !is_array($data['timestamps'])) {
        $data['timestamps'] = array();
    }

    return $data;
}

/**
 * @param int $webinar_id
 * @param string $event
 * @param int $timestamp
 * @return void
 */
function webinar_analytics_mvp_increment_event($webinar_id, $event, $timestamp) {
    $webinar_id = absint($webinar_id);
    if (!$webinar_id) {
        return;
    }

    $event = (string) sanitize_key($event);
    $timestamp = (int) $timestamp;

    $data = webinar_analytics_mvp_get_storage($webinar_id);
    if (empty($data)) {
        $data = array(
            'counts' => array(),
            'timestamps' => array(),
        );
    }

    $data['counts'][$event] = isset($data['counts'][$event]) ? (int) $data['counts'][$event] + 1 : 1;
    $data['timestamps'][$event] = $timestamp;

    $key = webinar_analytics_mvp_option_key($webinar_id);
    $existing = get_option($key, null);
    if ($existing === null) {
        if (!add_option($key, $data, '', false)) {
            error_log('webinar_analytics_mvp: failed to add option for webinar ' . $webinar_id);
        }
        return;
    }

    if (!update_option($key, $data)) {
        error_log('webinar_analytics_mvp: failed to update option for webinar ' . $webinar_id);
    }
}

/**
 * Read-only aggregates for MVP dashboards.
 *
 * @param int $webinar_id
 * @return array<string, int>
 */
function webinar_analytics_mvp_get_aggregates($webinar_id) {
    $webinar_id = absint($webinar_id);
    if (!$webinar_id) {
        return array(
            'views' => 0,
            'attended' => 0,
            'cta_shown' => 0,
            'cta_clicked' => 0,
        );
    }

    $data = webinar_analytics_mvp_get_storage($webinar_id);
    $counts = isset($data['counts']) && is_array($data['counts']) ? $data['counts'] : array();

    return array(
        'views' => isset($counts['webinar_started']) ? (int) $counts['webinar_started'] : 0,
        'attended' => isset($counts['user_attended']) ? (int) $counts['user_attended'] : 0,
        'cta_shown' => isset($counts['cta_shown']) ? (int) $counts['cta_shown'] : 0,
        'cta_clicked' => isset($counts['cta_clicked']) ? (int) $counts['cta_clicked'] : 0,
    );
}

/**
 * @param int $webinar_id
 * @return string
 */
function webinar_analytics_mvp_option_key($webinar_id) {
    return 'webinar_mvp_analytics_' . absint($webinar_id);
}

/**
 * @return string[]
 */
function webinar_analytics_mvp_allowed_events() {
    return array(
        'webinar_started',
        'user_attended',
        'webinar_finished',
        'cta_shown',
        'cta_clicked',
    );
}
