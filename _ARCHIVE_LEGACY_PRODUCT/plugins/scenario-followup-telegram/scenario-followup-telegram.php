<?php
/**
 * Plugin Name: Scenario Follow-up Telegram
 * Description: Исполнитель follow-up сценария с отложенной отправкой сообщения в Telegram
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_action('scenario_start', 'scenario_followup_schedule', 10, 2);
add_action('scenario_followup_execute', 'scenario_followup_execute', 10, 2);

function scenario_followup_schedule($scenario, $context) {
    if ($scenario !== 'welcome') {
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

    wp_schedule_single_event(time() + 600, 'scenario_followup_execute', [$lead_id, $payload]);
}

function scenario_followup_execute($lead_id, $payload) {
    if (!defined('TELEGRAM_BOT_TOKEN') || !defined('TELEGRAM_CHAT_ID')) {
        return;
    }

    if (!is_array($payload)) {
        $payload = [];
    }

    $lead_id = (int) $lead_id;
    $name = $payload['name'] ?? '';
    $email = $payload['email'] ?? '';

    $message_lines = [
        'Follow-up:',
        'Напоминаем о сообщении выше.',
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
