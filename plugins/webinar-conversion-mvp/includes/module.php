<?php

defined('ABSPATH') || exit;

function webinar_conversion_mvp_handle_cta_clicked($context) {
    $context = is_array($context) ? $context : array();

    $webinar_id = isset($context['webinar_id']) ? absint($context['webinar_id']) : 0;
    $user_id = isset($context['user_id']) ? absint($context['user_id']) : 0;

    $chat_id = $context['chat_id'] ?? null;
    $chat_id = apply_filters('client_telegram_chat_id', $chat_id, $user_id, (string) $webinar_id, 'cta_clicked', $context);
    $chat_id = apply_filters('candidate_telegram_chat_id', $chat_id, $user_id, (string) $webinar_id, 'cta_clicked', $context);

    if (!empty($chat_id)) {
        error_log('webinar_conversion_mvp: sending telegram interest notification for webinar ' . (int) $webinar_id . ' user ' . (int) $user_id);
        do_action('telegram_send_message', array(
            'text' => 'Пользователь проявил интерес к WHIEDA после вебинара.',
            'chat_id' => $chat_id,
            'meta' => array(
                'user_id' => $user_id,
                'webinar_id' => $webinar_id,
                'event' => 'cta_clicked',
            ),
        ));
        error_log('webinar_conversion_mvp: telegram interest notification sent for webinar ' . (int) $webinar_id . ' user ' . (int) $user_id);
    } else {
        error_log('webinar_conversion_mvp: telegram interest notification skipped (no chat id) for webinar ' . (int) $webinar_id . ' user ' . (int) $user_id);
    }

    do_action('whieda_conversion_signal', array(
        'event' => 'cta_clicked',
        'webinar_id' => $webinar_id,
        'user_id' => $user_id,
        'source' => 'webinar_cta',
    ));
    error_log('webinar_conversion_mvp: WHIEDA conversion signal emitted for webinar ' . (int) $webinar_id . ' user ' . (int) $user_id);
}
