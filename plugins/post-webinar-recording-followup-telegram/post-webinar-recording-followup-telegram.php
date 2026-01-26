<?php
/**
 * Plugin Name: Post Webinar Recording Follow-up Telegram
 * Description: Отправка нейтрального сообщения с предложением записи кандидатам, не посетившим вебинар.
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_action('post_webinar_route', 'post_webinar_recording_followup_telegram_handle', 10, 2);

function post_webinar_recording_followup_telegram_handle($route, $context) {
    $route = is_string($route) ? $route : '';
    $context = is_array($context) ? $context : array();

    if ($route !== 'not_attended') {
        return;
    }

    $lead_id = isset($context['lead_id']) ? absint($context['lead_id']) : 0;
    $webinar_id = isset($context['webinar_id']) ? (string) $context['webinar_id'] : '';
    $webinar_id = $webinar_id !== '' ? (string) sanitize_text_field($webinar_id) : '';

    $chat_id = $context['candidate_chat_id'] ?? $context['chat_id'] ?? $context['telegram_chat_id'] ?? null;
    $chat_id = apply_filters('client_telegram_chat_id', $chat_id, $lead_id, $webinar_id, $route, $context);
    $chat_id = apply_filters('candidate_telegram_chat_id', $chat_id, $lead_id, $webinar_id, $route, $context);

    if (empty($chat_id)) {
        return;
    }

    $message = "Вы не смогли присутствовать на вебинаре.\nМогу прислать запись — напишите, если актуально.";

    do_action('telegram_send_message', array(
        'text' => $message,
        'chat_id' => $chat_id,
        'meta' => array(
            'lead_id' => $lead_id,
            'webinar_id' => $webinar_id,
            'route' => $route,
        ),
    ));
}
