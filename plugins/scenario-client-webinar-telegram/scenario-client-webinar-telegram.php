<?php
/**
 * Plugin Name: Scenario Client Webinar Telegram
 * Description: Исполнитель сценария client_webinar с отправкой welcome-сообщения в Telegram
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

static $registered = false;
if (!$registered) {
    add_action('scenario_start', 'scenario_client_webinar_telegram_handle_start', 10, 2);
    $registered = true;
}

function scenario_client_webinar_telegram_handle_start($scenario, $context) {
    if ($scenario !== 'client_webinar') {
        return;
    }

    if (!is_array($context)) {
        return;
    }

    $event = (string)($context['event'] ?? '');

    if ($event === '') {
        return;
    }

    switch ($event) {
        case 'client_webinar_entered':
            $message = "Вы подключились к вебинару.\nОставайтесь до конца — в конце будет полезный бонус.";
            break;
        case 'client_webinar_completed':
            $message = "Вебинар завершён.\nЧтобы получить бонус и продолжить — напишите «СТАРТ» в ответ.";
            break;
        default:
            return;
    }

    do_action('telegram_send_message', ['text' => $message]);
}
