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

add_action('autowebinar_delivery_payload', 'core_handle_autowebinar_payload', 10, 1);

function core_handle_autowebinar_payload(array $payload) {
    $webinar_id = $payload['webinar_id'] ?? null;
    $entry_timestamp = $payload['entry_timestamp'] ?? null;

    if (empty($webinar_id) || empty($entry_timestamp)) {
        return;
    }

    $lead_meta = array(
        'source' => 'autowebinar',
        'webinar_id' => $webinar_id,
        'entry_timestamp' => $entry_timestamp,
        'user_agent' => $payload['user_agent'] ?? null,
        'ip' => $payload['ip'] ?? null,
    );

    if (!empty($payload['ref'])) {
        $lead_meta['ref'] = $payload['ref'];
    }

    do_action('core_lead_ingest', $lead_meta);
}
