<?php
/**
 * Plugin Name: Core Legacy Event Bridge
 * Description: Bridges legacy WordPress events to core canonical events.
 * Version: 0.1.0
 */

defined('ABSPATH') || exit;

add_action('wp_login', function ($user_login, $user) {
    do_action('core_user_login', $user->ID, $user);
}, 10, 2);

add_action('user_register', function ($user_id) {
    do_action('core_user_registered', $user_id);
});
