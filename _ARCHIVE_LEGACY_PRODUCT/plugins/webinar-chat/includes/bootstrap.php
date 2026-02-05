<?php

defined('ABSPATH') || exit;

require_once __DIR__ . '/module.php';

register_activation_hook(WEBINAR_CHAT_PLUGIN_FILE, 'webinar_chat_activate');

add_action('init', 'webinar_chat_bootstrap');

add_shortcode('webinar_room_chat', 'webinar_chat_render_shortcode');
// Deprecated shortcode alias for backward compatibility.
add_shortcode('whieda_room_chat', 'webinar_chat_render_shortcode');

add_action('wp_ajax_' . WEBINAR_CHAT_ACTION_FETCH, 'webinar_chat_handle_fetch');
add_action('wp_ajax_' . WEBINAR_CHAT_ACTION_SEND, 'webinar_chat_handle_send');
add_action('wp_ajax_' . WEBINAR_CHAT_ACTION_MODER, 'webinar_chat_handle_moderation');

add_action('wp_ajax_' . WEBINAR_CHAT_ACTION_FETCH_LEGACY, 'webinar_chat_handle_fetch');
add_action('wp_ajax_' . WEBINAR_CHAT_ACTION_SEND_LEGACY, 'webinar_chat_handle_send');
add_action('wp_ajax_' . WEBINAR_CHAT_ACTION_MODER_LEGACY, 'webinar_chat_handle_moderation');
