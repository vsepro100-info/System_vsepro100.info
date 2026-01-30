<?php

defined('ABSPATH') || exit;

/**
 * @param array<string, mixed> $payload
 * @param int|null $webinar_id
 * @return void
 */
function webinar_lifecycle_event_bridge_handle_upsert($payload, $webinar_id = null) {
    $payload = is_array($payload) ? $payload : array();

    if (!empty($payload['webinar_id'])) {
        return;
    }

    $webinar_id = isset($webinar_id) ? absint($webinar_id) : 0;
    if (!$webinar_id) {
        return;
    }

    $webinar = apply_filters('core_webinar_get', $webinar_id, array());

    do_action('webinar_created', array(
        'webinar_id' => $webinar_id,
        'webinar' => $webinar,
        'payload' => $payload,
    ));
}

/**
 * @param int $webinar_id
 * @param string $status
 * @param array<string, mixed>|null $context
 * @return void
 */
function webinar_lifecycle_event_bridge_handle_status($webinar_id, $status, $context = null) {
    $webinar_id = absint($webinar_id);
    if (!$webinar_id) {
        return;
    }

    $status = (string) sanitize_key($status);
    if ($status !== 'start' && $status !== 'stop') {
        return;
    }

    $webinar = apply_filters('core_webinar_get', $webinar_id, array());

    $payload = array(
        'webinar_id' => $webinar_id,
        'status' => $status,
        'webinar' => $webinar,
        'context' => is_array($context) ? $context : array(),
    );

    if ($status === 'start') {
        do_action('webinar_started', $payload);
        return;
    }

    do_action('webinar_finished', $payload);
}

/**
 * @param array<string, mixed> $payload
 * @return void
 */
function webinar_lifecycle_telegram_notify_created($payload) {
    webinar_lifecycle_telegram_notify('created', $payload);
}

/**
 * @param array<string, mixed> $payload
 * @return void
 */
function webinar_lifecycle_telegram_notify_started($payload) {
    webinar_lifecycle_telegram_notify('started', $payload);
}

/**
 * @param array<string, mixed> $payload
 * @return void
 */
function webinar_lifecycle_telegram_notify_finished($payload) {
    webinar_lifecycle_telegram_notify('finished', $payload);
}

/**
 * @param string $event
 * @param array<string, mixed> $payload
 * @return void
 */
function webinar_lifecycle_telegram_notify($event, $payload) {
    $payload = is_array($payload) ? $payload : array();

    $webinar_id = isset($payload['webinar_id']) ? absint($payload['webinar_id']) : 0;
    if (!$webinar_id) {
        return;
    }

    $webinar = isset($payload['webinar']) && is_array($payload['webinar']) ? $payload['webinar'] : array();
    if (empty($webinar)) {
        $webinar = apply_filters('core_webinar_get', $webinar_id, array());
    }

    $title = isset($webinar['title']) ? (string) $webinar['title'] : '';
    $start_datetime = isset($webinar['start_datetime']) ? (string) $webinar['start_datetime'] : '';
    $status = isset($webinar['status']) ? (string) $webinar['status'] : '';

    $headline_map = array(
        'created' => 'Создан вебинар',
        'started' => 'Вебинар начался',
        'finished' => 'Вебинар завершён',
    );

    $headline = $headline_map[$event] ?? 'Событие вебинара';

    $message_lines = array(
        $headline . ':',
        'ID: ' . $webinar_id,
    );

    if ($title !== '') {
        $message_lines[] = 'Название: ' . $title;
    }

    if ($start_datetime !== '') {
        $message_lines[] = 'Старт: ' . $start_datetime;
    }

    if ($status !== '') {
        $message_lines[] = 'Статус: ' . $status;
    }

    $message = implode("\n", $message_lines);

    do_action('telegram_send_message', array(
        'text' => $message,
        'meta' => array(
            'event' => $event,
            'webinar_id' => $webinar_id,
        ),
    ));
}
