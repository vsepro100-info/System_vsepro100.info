<?php
/**
 * Plugin Name: Scenario Client Webinar Telegram
 * Description: Исполнитель сценария client_webinar с отправкой welcome-сообщения в Telegram
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

static $registered = false;
if (!$registered) {
    add_action('scenario_start', 'scenario_client_webinar_telegram_handle_start', 10, 2);
    $registered = true;
}

function scenario_client_webinar_telegram_handle_start($scenario, $context) {
    if ($scenario !== 'client_webinar') {
        return;
    }

    if (!is_array($context)) {
        return;
    }

    $event = (string)($context['event'] ?? '');

    if ($event !== 'client_webinar_completed') {
        return;
    }

    $lead_id = $context['lead_id'] ?? null;
    $webinar_id = $context['webinar_id'] ?? null;
    $timestamp = $context['timestamp'] ?? null;

    $formatted_time = ($timestamp !== null && $timestamp !== '')
        ? wp_date('d.m.Y H:i', (int) $timestamp)
        : wp_date('d.m.Y H:i');
    $lead_label = ($lead_id !== null && $lead_id !== '') ? $lead_id : '—';
    $webinar_label = ($webinar_id !== null && $webinar_id !== '') ? $webinar_id : '—';

    $lines = [
        '📡 Вебинар завершён',
        '',
        '🧑 Лид ID: ' . $lead_label,
        '🎥 Вебинар: ' . $webinar_label,
        '🕒 Время: ' . $formatted_time,
    ];

    $meta = [
        'scenario' => 'client_webinar',
        'event' => 'client_webinar_completed',
    ];

    if ($webinar_id !== null && $webinar_id !== '') {
        $meta['webinar_id'] = $webinar_id;
    }

    if ($lead_id !== null && $lead_id !== '') {
        $meta['lead_id'] = $lead_id;
    }

    do_action('telegram_send_message', [
        'text' => implode("\n", $lines),
        'meta' => $meta,
    ]);
}
