<?php
/**
 * Plugin Name: Service Telegram Notifier
 * Description: Технический обработчик уведомлений Telegram для core_ingest_event.
 * Version: 0.1.2
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_action('core_ingest_event', 'service_telegram_handle_ingest', 10, 1);
add_action('core_lead_created', 'service_telegram_handle_lead_created', 10, 2);

function service_telegram_handle_ingest(array $lead_meta) {
    if (!defined('TELEGRAM_BOT_TOKEN') || !defined('TELEGRAM_CHAT_ID')) {
        return;
    }

    $webinar_id = $lead_meta['webinar_id'] ?? '';
    $ref = $lead_meta['ref'] ?? '';
    $ip = $lead_meta['ip'] ?? '';

    $message = "New autowebinar lead:\n"
        . "webinar_id: {$webinar_id}\n"
        . "ref: {$ref}\n"
        . "ip: {$ip}";

    $url = 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/sendMessage';

    wp_remote_post($url, [
        'body' => [
            'chat_id' => TELEGRAM_CHAT_ID,
            'text' => $message,
        ],
    ]);
}

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
    $name = $payload['name'] ?? null;
    $email = $payload['email'] ?? null;
    $created_at_meta = '';

    if (!empty($lead_id)) {
        $created_at_meta = get_post_meta($lead_id, 'lead_created_at', true);
    }

    $created_at = $created_at_meta !== '' ? $created_at_meta : time();

    $message_lines = [
        'New web_form lead:',
        "lead_id: {$lead_id}",
        "source: {$source}",
        "created_at: {$created_at}",
    ];

    if (!empty($name)) {
        $message_lines[] = "name: {$name}";
    }

    if (!empty($email)) {
        $message_lines[] = "email: {$email}";
    }

    $message = implode("\n", $message_lines);
    $url = 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/sendMessage';

    wp_remote_post($url, [
        'body' => [
            'chat_id' => TELEGRAM_CHAT_ID,
            'text' => $message,
        ],
    ]);
}
