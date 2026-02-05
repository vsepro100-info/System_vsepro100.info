<?php

defined('ABSPATH') || exit;

function webinar_conversion_mvp_handle_cta_clicked($context) {
    $context = is_array($context) ? $context : array();

    webinar_conversion_mvp_handle_conversion_event('cta_clicked', $context);
}

function webinar_conversion_mvp_handle_interest_confirmed($context) {
    $context = is_array($context) ? $context : array();

    webinar_conversion_mvp_handle_conversion_event('interest_confirmed', $context);
}

/**
 * @param string $event
 * @param array<string, mixed> $context
 * @return void
 */
function webinar_conversion_mvp_handle_conversion_event($event, array $context) {
    $webinar_id = isset($context['webinar_id']) ? absint($context['webinar_id']) : 0;
    $user_id = isset($context['user_id']) ? absint($context['user_id']) : 0;

    $event = (string) sanitize_key($event);
    $ref_id = webinar_conversion_mvp_resolve_ref_id($context, $user_id, $webinar_id, $event);
    if ($ref_id === '') {
        error_log(
            'webinar_conversion_mvp: ref_id missing for event ' .
            $event .
            ' webinar ' .
            (int) $webinar_id .
            ' user ' .
            (int) $user_id
        );
    }

    if ($ref_id !== '') {
        do_action('webinar_ref_attribution', array(
            'event' => $event,
            'webinar_id' => $webinar_id,
            'user_id' => $user_id,
            'ref_id' => $ref_id,
            'timestamp' => isset($context['timestamp']) ? (int) $context['timestamp'] : time(),
        ));
    }

    if ($event !== 'cta_clicked') {
        webinar_conversion_mvp_emit_whieda_signal($event, $webinar_id, $user_id, $ref_id);
        return;
    }

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

    webinar_conversion_mvp_emit_whieda_signal('cta_clicked', $webinar_id, $user_id, $ref_id);
}

/**
 * @param array<string, mixed> $context
 * @param int $user_id
 * @param int $webinar_id
 * @param string $event
 * @return string
 */
function webinar_conversion_mvp_resolve_ref_id(array $context, $user_id, $webinar_id, $event) {
    if (isset($context['ref_id']) && $context['ref_id'] !== '') {
        return (string) sanitize_text_field((string) $context['ref_id']);
    }

    $resolved_ref_id = '';
    if (function_exists('core_engine_filter_user_context')) {
        $user_context = core_engine_filter_user_context(array(
            'user_id' => $user_id,
            'webinar_id' => $webinar_id,
            'event' => $event,
        ));
        if (is_array($user_context) && !empty($user_context['ref_id'])) {
            $resolved_ref_id = (string) $user_context['ref_id'];
        }
    }

    if ($resolved_ref_id === '' && $user_id) {
        $resolved_ref_id = (string) get_user_meta($user_id, 'ref_id', true);
    }

    if ($resolved_ref_id === '' && $user_id) {
        $resolved_ref_id = (string) get_user_meta($user_id, 'ref', true);
    }

    if ($resolved_ref_id === '' && isset($_COOKIE['ref_id'])) {
        $resolved_ref_id = (string) wp_unslash($_COOKIE['ref_id']);
    }

    if ($resolved_ref_id === '' && isset($_COOKIE['ref'])) {
        $resolved_ref_id = (string) wp_unslash($_COOKIE['ref']);
    }

    if ($resolved_ref_id === '') {
        return '';
    }

    return (string) sanitize_text_field($resolved_ref_id);
}

/**
 * @param string $event
 * @param int $webinar_id
 * @param int $user_id
 * @param string $ref_id
 * @return void
 */
function webinar_conversion_mvp_emit_whieda_signal($event, $webinar_id, $user_id, $ref_id) {
    do_action('whieda_conversion_signal', array(
        'event' => $event,
        'webinar_id' => $webinar_id,
        'user_id' => $user_id,
        'ref_id' => $ref_id,
        'source' => 'webinar_cta',
    ));
    error_log(
        'webinar_conversion_mvp: WHIEDA conversion signal emitted for event ' .
        $event .
        ' webinar ' .
        (int) $webinar_id .
        ' user ' .
        (int) $user_id
    );
}
