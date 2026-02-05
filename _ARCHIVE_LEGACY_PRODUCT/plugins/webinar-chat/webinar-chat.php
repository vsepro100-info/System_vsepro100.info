<?php
/**
 * Plugin Name: Вебинар — Чат
 * Description: Модуль чата вебинарной комнаты: сообщения, модерация, бан, очистка.
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

if (!defined('WEBINAR_CHAT_PLUGIN_FILE')) {
    define('WEBINAR_CHAT_PLUGIN_FILE', __FILE__);
}

require_once __DIR__ . '/includes/bootstrap.php';
