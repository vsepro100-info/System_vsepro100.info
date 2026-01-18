<?php
/**
 * Plugin Name: AutoWebinar Delivery
 * Description: Skeleton plugin for AutoWebinar delivery hooks (no logic).
 * Version: 0.1.0
 */

defined('ABSPATH') || exit;

add_action('core_user_registered', 'autowebinar_delivery_on_user_registered');
add_action('core_user_login', 'autowebinar_delivery_on_user_login');
add_action('core_ref_context_resolved', 'autowebinar_delivery_on_ref_context_resolved');
add_action('core_user_login', function ($user_id, $user = null) {
    error_log('[autowebinar][probe] core_user_login fired user_id=' . intval($user_id));
});
// @diagnostic REMOVE AFTER TEST

add_action('core_user_registered', function ($user_id) {
    error_log('[autowebinar][probe] core_user_registered fired user_id=' . intval($user_id));
});
// @diagnostic REMOVE AFTER TEST

function autowebinar_delivery_on_user_registered() {
    error_log('[autowebinar] core_user_registered fired');
}

function autowebinar_delivery_on_user_login() {
    error_log('[autowebinar] core_user_login fired');
}

function autowebinar_delivery_on_ref_context_resolved() {
    error_log('[autowebinar] core_ref_context_resolved fired');
}

function autowebinar_session_created() {
    do_action('autowebinar_session_created');
}

function autowebinar_join() {
    do_action('autowebinar_join');
}

function autowebinar_progress() {
    do_action('autowebinar_progress');
}

function autowebinar_cta_click() {
    do_action('autowebinar_cta_click');
}

function autowebinar_completed() {
    do_action('autowebinar_completed');
}
