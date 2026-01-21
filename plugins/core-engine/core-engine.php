<?php
/**
 * Plugin Name: Core Engine
 * Description: Canonical hooks and entity placeholders for the core foundation.
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

/**
 * Canonical action hooks.
 */
function core_engine_action_user_registered($user_id, $user = null) {
    do_action('core_engine_user_registered', $user_id, $user);
}

function core_engine_action_user_login($user_id, $user = null) {
    do_action('core_engine_user_login', $user_id, $user);
}

function core_engine_action_lead_ingest(array $lead_meta) {
    do_action('core_engine_lead_ingest', $lead_meta);
}

function core_engine_action_ingest_event(array $payload) {
    do_action('core_ingest_event', $payload);
}

/**
 * Canonical filter hooks.
 */
function core_engine_filter_lead_meta(array $lead_meta) {
    return apply_filters('core_engine_lead_meta', $lead_meta);
}

function core_engine_filter_user_context(array $context) {
    return apply_filters('core_engine_user_context', $context);
}

/**
 * Entity placeholders.
 */
class Core_Engine_Lead {
    // Placeholder for canonical Lead entity.
}

class Core_Engine_User {
    // Placeholder for canonical User entity.
}
