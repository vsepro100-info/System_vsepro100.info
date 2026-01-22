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

    wp_remote_post($url, [
        'body' => [
            'chat_id' => $chat_id,
            'text' => $text,
        ],
    ]);
}
