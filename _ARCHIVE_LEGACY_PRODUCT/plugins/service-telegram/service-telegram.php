<?php
/**
 * Plugin Name: Service Telegram Notifier
 * Description: Технический обработчик уведомлений Telegram для core_lead_created.
 * Version: 0.1.4
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_action('core_lead_created', 'service_telegram_handle_lead_created', 10, 2);

function service_telegram_handle_lead_created(int $lead_id, array $payload) {
    if (empty($payload) || !is_array($payload)) {
        return;
    }

    $source = $payload['source'] ?? '';

    if ($source !== 'web_form') {
        return;
    }

    $lead_id = (int) $lead_id;
    $name = $payload['name'] ?? '';
    $email = $payload['email'] ?? '';
    $created_at_meta = '';

    if (!empty($lead_id)) {
        $created_at_meta = get_post_meta($lead_id, 'lead_created_at', true);
    }

    $created_at = $created_at_meta !== '' ? (int) $created_at_meta : time();
    $created_at_formatted = wp_date('d.m.Y H:i', $created_at, wp_timezone());

    $message_lines = [
        'Новый лид с формы:',
        "ID лида: {$lead_id}",
        "Источник: {$source}",
        "Имя: {$name}",
        "Email: {$email}",
        "Время создания: {$created_at_formatted}",
    ];

    $message = implode("\n", $message_lines);

    do_action('telegram_send_message', [
        'text' => $message,
    ]);
}
