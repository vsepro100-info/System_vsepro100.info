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
 * Core ingest payload contract version.
 */
define('CORE_INGEST_CONTRACT_VERSION', 'v1');

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

/**
 * Normalizes ingest payload without mutating the original array.
 * Contract version: CORE_INGEST_CONTRACT_VERSION.
 *
 * @param array $payload
 * @return array
 */
function core_engine_normalize_ingest_payload(array $payload) {
    return array_replace([], $payload);
}

/**
 * Checks required ingest payload fields.
 * Contract version: CORE_INGEST_CONTRACT_VERSION.
 *
 * @param array $payload
 * @return bool
 */
function core_engine_ingest_payload_has_required_fields(array $payload) {
    $webinar_id = $payload['webinar_id'] ?? null;
    $entry_timestamp = $payload['entry_timestamp'] ?? null;

    return !empty($webinar_id) && !empty($entry_timestamp);
}

/**
 * Dispatches the canonical ingest event payload.
 * Contract version: CORE_INGEST_CONTRACT_VERSION.
 *
 * Payload schema (current source: core_handle_autowebinar_payload):
 * Required fields:
 * - webinar_id (string|int): AutoWebinar webinar identifier; non-empty.
 * - entry_timestamp (string|int): Lead entry timestamp; non-empty.
 *
 * Optional fields:
 * - user_agent (string|null): User agent string, when available.
 * - ip (string|null): IP address, when available.
 * - ref (string): Referral/source token, when provided.
 *
 * Invariants:
 * - Payload is shallow-copied without value changes.
 * - Event is emitted only when required fields are present and non-empty.
 * - Field names are stable for current routing targets.
 *
 * @param array{
 *     webinar_id: string|int,
 *     entry_timestamp: string|int,
 *     user_agent?: string|null,
 *     ip?: string|null,
 *     ref?: string
 * } $payload
 * @return bool True when the event is emitted; false when required fields are missing.
 */
function core_engine_action_ingest_event(array $payload) {
    if (!core_engine_ingest_payload_has_required_fields($payload)) {
        return false;
    }

    $payload = core_engine_normalize_ingest_payload($payload);
    do_action('core_ingest_event', $payload);
    return true;
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
