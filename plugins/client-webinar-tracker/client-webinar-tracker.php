<?php
/**
 * Plugin Name: Client Webinar Tracker
 * Description: Сервисный трекер клиентских событий вебинара (только эмиссия событий).
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

/**
 * Формирует минимальный контекст события вебинара.
 *
 * @param array $context
 * @return array
 */
function client_webinar_tracker_prepare_context(array $context) {
    $prepared = array();

    if (array_key_exists('lead_id', $context)) {
        $prepared['lead_id'] = (int) $context['lead_id'];
    }

    if (array_key_exists('webinar_id', $context)) {
        $prepared['webinar_id'] = (string) $context['webinar_id'];
    }

    $prepared['timestamp'] = array_key_exists('timestamp', $context)
        ? (int) $context['timestamp']
        : time();

    return $prepared;
}

/**
 * Эмиссия события входа клиента на вебинар.
 *
 * @param array $context
 * @return void
 */
function client_webinar_enter(array $context = array()) {
    $prepared = client_webinar_tracker_prepare_context($context);

    do_action('client_webinar_entered', $prepared);
}

/**
 * Эмиссия события завершения просмотра вебинара.
 *
 * @param array $context
 * @return void
 */
function client_webinar_complete(array $context = array()) {
    $prepared = client_webinar_tracker_prepare_context($context);

    do_action('client_webinar_completed', $prepared);
}

/**
 * Эмиссия события отправки формы во время вебинара.
 *
 * @param array $context
 * @return void
 */
function client_webinar_form_submit(array $context = array()) {
    $prepared = client_webinar_tracker_prepare_context($context);

    do_action('client_webinar_form_submitted', $prepared);
}
