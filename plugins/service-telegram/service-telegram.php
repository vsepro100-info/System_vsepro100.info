<?php
/**
 * Plugin Name: Service Telegram Notifier
 * Description: Технический обработчик уведомлений Telegram для core_lead_created.
 * Version: 0.1.3
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_action('core_lead_created', 'service_telegram_handle_lead_created', 10, 2);

function service_telegram_handle_lead_created(int $lead_id, array $payload) {
    if (!defined('TELEGRAM_BOT_TOKEN') || !defined('TELEGRAM_CHAT_ID')) {
        return;
    }

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

    $created_at = $created_at_meta !== '' ? $created_at_meta : time();

    $message_lines = [
        'Новый лид с формы:',
        "ID лида: {$lead_id}",
        "Источник: {$source}",
        "Имя: {$name}",
        "Email: {$email}",
        "Время создания: {$created_at}",
    ];

    $message = implode("\n", $message_lines);
    $url = 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/sendMessage';

    wp_remote_post($url, [
        'body' => [
            'chat_id' => TELEGRAM_CHAT_ID,
            'text' => $message,
        ],
    ]);
}
