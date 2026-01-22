<?php
/**
 * Plugin Name: Telegram Sender Service
 * Description: Канонический сервис отправки сообщений в Telegram через action.
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_action('telegram_send_message', 'telegram_sender_handle', 10, 1);

function telegram_sender_handle($payload) {
    error_log('[telegram-sender] action fired');
    if (!is_array($payload)) {
        return;
    }

    $text = trim((string) ($payload['text'] ?? ''));

    if ($text === '') {
        return;
    }

    if (!defined('TELEGRAM_BOT_TOKEN') || TELEGRAM_BOT_TOKEN === '') {
        error_log('telegram_send_message: TELEGRAM_BOT_TOKEN is missing.');
        return;
    }

    $chat_id = $payload['chat_id'] ?? null;

    if (empty($chat_id) && defined('TELEGRAM_CHAT_ID')) {
        $chat_id = TELEGRAM_CHAT_ID;
    }

    if (empty($chat_id)) {
        return;
    }

    $url = 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/sendMessage';

    $response = wp_remote_post($url, [
        'body' => [
            'chat_id' => $chat_id,
            'text' => $text,
        ],
    ]);

    error_log('[telegram-sender] request_url: ' . $url);
    error_log('[telegram-sender] is_wp_error: ' . (is_wp_error($response) ? '1' : '0'));
    error_log('[telegram-sender] http_response_code: ' . wp_remote_retrieve_response_code($response));

    $response_body = wp_remote_retrieve_body($response);
    error_log('[telegram-sender] http_body: ' . trim((string) $response_body));

    if (is_wp_error($response)) {
        error_log('[telegram-sender] wp_error: ' . $response->get_error_message());
    }
}
