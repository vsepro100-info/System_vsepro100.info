<?php
/**
 * Plugin Name: Scenario Welcome Telegram
 * Description: Исполнитель сценария welcome с отправкой сообщения в Telegram
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_action('scenario_start', 'scenario_welcome_telegram_handle_start', 10, 2);

function scenario_welcome_telegram_handle_start($scenario, $context) {
    if ($scenario !== 'welcome') {
        return;
    }

    if (!defined('TELEGRAM_BOT_TOKEN') || !defined('TELEGRAM_CHAT_ID')) {
        return;
    }

    if (!is_array($context)) {
        return;
    }

    $lead_id = isset($context['lead_id']) ? (int) $context['lead_id'] : 0;
    $payload = $context['payload'] ?? [];

    if (!is_array($payload)) {
        $payload = [];
    }

    $name = $payload['name'] ?? '';
    $email = $payload['email'] ?? '';

    $message_lines = [
        'Новый лид (welcome):',
        "ID лида: {$lead_id}",
        "Имя: {$name}",
        "Email: {$email}",
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
