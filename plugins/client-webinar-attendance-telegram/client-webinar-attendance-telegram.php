<?php
/**
 * Plugin Name: Client Webinar Attendance Telegram
 * Description: Уведомление партнера в Telegram при классификации посещаемости вебинара.
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_action('client_webinar_attendance_classified', 'client_webinar_attendance_telegram_notify', 10, 1);

function client_webinar_attendance_telegram_notify($payload) {
    $payload = is_array($payload) ? $payload : array();

    if (!array_key_exists('attended', $payload)) {
        return;
    }

    $attended = (bool) $payload['attended'];
    $lead_id = isset($payload['lead_id']) ? absint($payload['lead_id']) : 0;
    $webinar_id = isset($payload['webinar_id']) ? (string) $payload['webinar_id'] : '';
    $webinar_id = $webinar_id !== '' ? (string) sanitize_text_field($webinar_id) : '';

    $chat_id = $payload['partner_chat_id'] ?? $payload['chat_id'] ?? null;
    $chat_id = apply_filters('partner_telegram_chat_id', $chat_id, $lead_id, $webinar_id, $payload);

    if (empty($chat_id)) {
        return;
    }

    $message = $attended
        ? 'Ваш кандидат посетил вебинар'
        : 'Ваш кандидат зарегистрировался, но не посетил вебинар';

    do_action('telegram_send_message', array(
        'text' => $message,
        'chat_id' => $chat_id,
        'meta' => array(
            'lead_id' => $lead_id,
            'webinar_id' => $webinar_id,
            'attended' => $attended,
        ),
    ));
}
