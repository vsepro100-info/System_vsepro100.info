<?php
/**
 * Plugin Name: Core Engine
 * Description: Технические канонические хуки и заготовки сущностей ядра.
 * Version: 0.1.2
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
 * Ingest subtype registry.
 */
function &core_engine_get_ingest_subtype_registry() {
    static $registry = array();
    return $registry;
}

function core_engine_register_ingest_subtype($subtype, callable $handler) {
    $registry = &core_engine_get_ingest_subtype_registry();
    $registry[$subtype] = $handler;
}

function core_engine_get_ingest_subtype_handler($subtype) {
    $registry = core_engine_get_ingest_subtype_registry();
    return $registry[$subtype] ?? null;
}

function core_engine_dispatch_ingest_subtype($subtype, array $payload) {
    $handler = core_engine_get_ingest_subtype_handler($subtype);
    if (!$handler) {
        return false;
    }

    return (bool) call_user_func($handler, $payload);
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
 * Orchestrates AutoWebinar ingest payload into canonical events.
 *
 * @param array $payload
 * @return bool True when the ingest event is emitted; false when required fields are missing.
 */
function core_engine_handle_autowebinar_payload(array $payload) {
    return core_engine_dispatch_ingest_subtype('autowebinar', $payload);
}

/**
 * Orchestrates AutoWebinar ingest payload into canonical events.
 *
 * @param array $payload
 * @return bool True when the ingest event is emitted; false when required fields are missing.
 */
function core_engine_orchestrate_autowebinar_payload(array $payload) {
    if (!core_engine_action_ingest_event($payload)) {
        return false;
    }

    $webinar_id = $payload['webinar_id'] ?? null;
    $entry_timestamp = $payload['entry_timestamp'] ?? null;

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

    return true;
}

core_engine_register_ingest_subtype('autowebinar', 'core_engine_orchestrate_autowebinar_payload');

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
 * Canonical Webinar entity.
 */
define('CORE_ENGINE_WEBINAR_CPT', 'webinar_event');
define('CORE_ENGINE_WEBINAR_META_PREFIX', '_core_webinar_');

function core_engine_register_webinar_entity() {
    register_post_type(
        CORE_ENGINE_WEBINAR_CPT,
        array(
            'label' => 'Webinar',
            'public' => false,
            'show_ui' => false,
            'show_in_menu' => false,
            'show_in_rest' => false,
            'supports' => array('title'),
            'rewrite' => false,
        )
    );
}

add_action('init', 'core_engine_register_webinar_entity');

function core_engine_webinar_meta_key($field) {
    return CORE_ENGINE_WEBINAR_META_PREFIX . $field;
}

function core_engine_webinar_normalize_status($status) {
    $status = (string) sanitize_key($status);
    $allowed = array('scheduled', 'live', 'ended');
    if (!in_array($status, $allowed, true)) {
        return 'scheduled';
    }

    return $status;
}

function core_engine_webinar_normalize_stream_type($stream_type) {
    $stream_type = (string) sanitize_key($stream_type);
    $allowed = array('obs', 'zoom', 'telegram');
    if (!in_array($stream_type, $allowed, true)) {
        return '';
    }

    return $stream_type;
}

/**
 * @param array<string, mixed> $payload
 * @return array<string, mixed>
 */
function core_engine_webinar_normalize_payload(array $payload) {
    $normalized = array();

    $normalized['title'] = isset($payload['title']) ? sanitize_text_field($payload['title']) : '';
    $normalized['start_datetime'] = isset($payload['start_datetime']) ? sanitize_text_field($payload['start_datetime']) : '';
    $normalized['status'] = core_engine_webinar_normalize_status($payload['status'] ?? 'scheduled');
    $normalized['poster_id'] = isset($payload['poster_id']) ? absint($payload['poster_id']) : 0;
    $normalized['poster_url'] = isset($payload['poster_url']) ? esc_url_raw($payload['poster_url']) : '';
    $normalized['stream_type'] = core_engine_webinar_normalize_stream_type($payload['stream_type'] ?? '');
    $normalized['stream_src'] = isset($payload['stream_src']) ? esc_url_raw($payload['stream_src']) : '';
    $normalized['chat_src'] = isset($payload['chat_src']) ? esc_url_raw($payload['chat_src']) : '';
    $normalized['cta_text'] = isset($payload['cta_text']) ? sanitize_text_field($payload['cta_text']) : '';
    $normalized['cta_link'] = isset($payload['cta_link']) ? esc_url_raw($payload['cta_link']) : '';

    if (isset($payload['webinar_id'])) {
        $normalized['webinar_id'] = absint($payload['webinar_id']);
    }

    if (isset($payload['id'])) {
        $normalized['webinar_id'] = absint($payload['id']);
    }

    return $normalized;
}

/**
 * @param array<string, mixed> $payload
 * @param int|null $webinar_id
 * @return void
 */
function core_engine_handle_webinar_upsert(array $payload, &$webinar_id = null) {
    $payload = core_engine_webinar_normalize_payload($payload);

    $candidate_id = isset($payload['webinar_id']) ? absint($payload['webinar_id']) : 0;
    if (!$candidate_id && !empty($webinar_id)) {
        $candidate_id = absint($webinar_id);
    }

    $post_data = array(
        'post_type' => CORE_ENGINE_WEBINAR_CPT,
        'post_status' => 'publish',
        'post_title' => $payload['title'] !== '' ? $payload['title'] : 'Webinar',
    );

    if ($candidate_id) {
        $post_data['ID'] = $candidate_id;
        $post_id = wp_update_post($post_data, true);
    } else {
        $post_id = wp_insert_post($post_data, true);
    }

    if (is_wp_error($post_id)) {
        return;
    }

    $webinar_id = (int) $post_id;

    update_post_meta($post_id, core_engine_webinar_meta_key('start_datetime'), $payload['start_datetime']);
    update_post_meta($post_id, core_engine_webinar_meta_key('status'), $payload['status']);
    update_post_meta($post_id, core_engine_webinar_meta_key('poster_id'), $payload['poster_id']);
    update_post_meta($post_id, core_engine_webinar_meta_key('poster_url'), $payload['poster_url']);
    update_post_meta($post_id, core_engine_webinar_meta_key('stream_type'), $payload['stream_type']);
    update_post_meta($post_id, core_engine_webinar_meta_key('stream_src'), $payload['stream_src']);
    update_post_meta($post_id, core_engine_webinar_meta_key('chat_src'), $payload['chat_src']);
    update_post_meta($post_id, core_engine_webinar_meta_key('cta_text'), $payload['cta_text']);
    update_post_meta($post_id, core_engine_webinar_meta_key('cta_link'), $payload['cta_link']);
}

add_action('core_webinar_upsert', 'core_engine_handle_webinar_upsert', 10, 2);

/**
 * @param int|null $webinar_id_or_null
 * @return int|null
 */
function core_engine_webinar_get_current_default($webinar_id_or_null) {
    if (!empty($webinar_id_or_null)) {
        return $webinar_id_or_null;
    }

    $live_query = new WP_Query(
        array(
            'post_type' => CORE_ENGINE_WEBINAR_CPT,
            'posts_per_page' => 1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => core_engine_webinar_meta_key('status'),
                    'value' => 'live',
                ),
            ),
        )
    );

    if ($live_query->have_posts()) {
        $webinar_id = (int) $live_query->posts[0]->ID;
        wp_reset_postdata();
        return $webinar_id;
    }
    wp_reset_postdata();

    $scheduled_query = new WP_Query(
        array(
            'post_type' => CORE_ENGINE_WEBINAR_CPT,
            'posts_per_page' => 1,
            'post_status' => 'publish',
            'meta_key' => core_engine_webinar_meta_key('start_datetime'),
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => core_engine_webinar_meta_key('status'),
                    'value' => 'scheduled',
                ),
            ),
        )
    );

    if ($scheduled_query->have_posts()) {
        $webinar_id = (int) $scheduled_query->posts[0]->ID;
        wp_reset_postdata();
        return $webinar_id;
    }
    wp_reset_postdata();

    return null;
}

add_filter('core_webinar_get_current', 'core_engine_webinar_get_current_default');

/**
 * @param int $webinar_id
 * @param array<string, mixed> $webinar_data
 * @return array<string, mixed>
 */
function core_engine_webinar_get_default($webinar_id, $webinar_data = array()) {
    if (!empty($webinar_data)) {
        return $webinar_data;
    }

    if (empty($webinar_id)) {
        return array();
    }

    $post = get_post($webinar_id);
    if (!$post || $post->post_type !== CORE_ENGINE_WEBINAR_CPT) {
        return array();
    }

    return array(
        'id' => $webinar_id,
        'title' => $post->post_title,
        'start_datetime' => (string) get_post_meta($webinar_id, core_engine_webinar_meta_key('start_datetime'), true),
        'status' => (string) get_post_meta($webinar_id, core_engine_webinar_meta_key('status'), true),
        'poster_id' => (int) get_post_meta($webinar_id, core_engine_webinar_meta_key('poster_id'), true),
        'poster_url' => (string) get_post_meta($webinar_id, core_engine_webinar_meta_key('poster_url'), true),
        'stream_type' => (string) get_post_meta($webinar_id, core_engine_webinar_meta_key('stream_type'), true),
        'stream_src' => (string) get_post_meta($webinar_id, core_engine_webinar_meta_key('stream_src'), true),
        'chat_src' => (string) get_post_meta($webinar_id, core_engine_webinar_meta_key('chat_src'), true),
        'cta_text' => (string) get_post_meta($webinar_id, core_engine_webinar_meta_key('cta_text'), true),
        'cta_link' => (string) get_post_meta($webinar_id, core_engine_webinar_meta_key('cta_link'), true),
    );
}

add_filter('core_webinar_get', 'core_engine_webinar_get_default', 10, 2);

/**
 * @param int $webinar_id
 * @param string $status
 * @param array<string, mixed>|null $context
 * @return void
 */
function core_engine_handle_webinar_set_status($webinar_id, $status, $context = null) {
    $webinar_id = absint($webinar_id);
    if (!$webinar_id) {
        return;
    }

    $status = core_engine_webinar_normalize_status($status);
    update_post_meta($webinar_id, core_engine_webinar_meta_key('status'), $status);

    if (!empty($context)) {
        update_post_meta($webinar_id, core_engine_webinar_meta_key('status_context'), wp_json_encode($context));
    }
}

add_action('core_webinar_set_status', 'core_engine_handle_webinar_set_status', 10, 3);

/**
 * Entity placeholders.
 */
class Core_Engine_Lead {
    // Placeholder for canonical Lead entity.
}

class Core_Engine_User {
    // Placeholder for canonical User entity.
}
