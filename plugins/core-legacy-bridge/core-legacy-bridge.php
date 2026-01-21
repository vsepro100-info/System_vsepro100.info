<?php
/**
 * Plugin Name: Core Legacy Event Bridge
 * Description: Связывает устаревшие события WordPress с каноничными событиями ядра.
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_action('wp_login', function ($user_login, $user) {
    do_action('core_user_login', $user->ID, $user);
}, 10, 2);

add_action('user_register', function ($user_id) {
    do_action('core_user_registered', $user_id);
});

add_action('autowebinar_delivery_payload', 'core_engine_orchestrate_autowebinar_payload', 10, 1);
