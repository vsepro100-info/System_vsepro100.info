<?php
/**
 * Plugin Name: Scenario Client Webinar Telegram
 * Description: Исполнитель сценария client_webinar с отправкой welcome-сообщения в Telegram
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_action('scenario_start', 'scenario_client_webinar_telegram_handle_start', 10, 2);

function scenario_client_webinar_telegram_handle_start($scenario, $context) {
    if ($scenario !== 'client_webinar') {
        return;
    }

    if (!defined('TELEGRAM_BOT_TOKEN') || !defined('TELEGRAM_CHAT_ID')) {
        return;
    }

    if (!is_array($context)) {
        return;
    }

    $payload = $context['payload'] ?? [];

    if (!is_array($payload)) {
        $payload = [];
    }

    $event = $payload['event'] ?? '';

    if ($event !== 'client_webinar_entered') {
        return;
    }

    $message = "Вы подключились к вебинару.\nОставайтесь до конца — в конце будет полезный бонус.";
    $url = 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/sendMessage';

    wp_remote_post($url, [
        'body' => [
            'chat_id' => TELEGRAM_CHAT_ID,
            'text' => $message,
        ],
    ]);
}
