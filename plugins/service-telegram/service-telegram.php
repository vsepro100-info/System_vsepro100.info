<?php
/**
 * Plugin Name: Service Telegram Notifier
 * Description: Отправляет уведомление в Telegram при событии core_lead_ingest.
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_action('core_lead_ingest', 'service_telegram_on_lead_ingest', 10, 1);

function service_telegram_on_lead_ingest(array $lead_meta) {
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
