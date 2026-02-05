<?php
/**
 * Plugin Name: Core Lead Engine
 * Description: Технические хуки и заготовки сущностей канонического жизненного цикла лида.
 * Version: 0.1.2
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

/**
 * Canonical action hooks for the lead lifecycle.
 */
function core_lead_engine_action_lead_created($lead_id, array $lead_payload) {
    do_action('core_lead_created', $lead_id, $lead_payload);
}

function core_lead_engine_action_lead_updated($lead_id, $lead_meta = null) {
    do_action('core_lead_updated', $lead_id, $lead_meta);
}

function core_lead_engine_action_lead_deleted($lead_id, $lead_meta = null) {
    do_action('core_lead_deleted', $lead_id, $lead_meta);
}

function core_lead_engine_action_lead_merged($primary_lead_id, $secondary_lead_id, $lead_meta = null) {
    do_action('core_lead_merged', $primary_lead_id, $secondary_lead_id, $lead_meta);
}

/**
 * Canonical lead entry entity.
 */
function core_lead_engine_register_lead_entry_post_type() {
    register_post_type('lead_entry', array(
        'public' => false,
        'show_ui' => false,
        'show_in_menu' => false,
        'supports' => array(),
        'rewrite' => false,
        'exclude_from_search' => true,
    ));
}

add_action('init', 'core_lead_engine_register_lead_entry_post_type');

/**
 * Computes a deterministic fingerprint for the ingest payload.
 *
 * @param array $payload
 * @return string
 */
function core_lead_engine_fingerprint_ingest_payload(array $payload) {
    return substr(sha1(wp_json_encode($payload)), 0, 40);
}

/**
 * Creates (or reuses) the canonical lead entry for the ingest payload.
 *
 * @param array $payload
 * @return int|null
 */
function core_lead_engine_materialize_lead_entry(array $payload) {
    static $lead_cache = array();

    $fingerprint = core_lead_engine_fingerprint_ingest_payload($payload);

    if (isset($lead_cache[$fingerprint])) {
        return $lead_cache[$fingerprint];
    }

    $existing = get_page_by_path($fingerprint, OBJECT, 'lead_entry');
    if ($existing && !is_wp_error($existing)) {
        $lead_cache[$fingerprint] = (int) $existing->ID;
        return $lead_cache[$fingerprint];
    }

    $post_id = wp_insert_post(array(
        'post_type' => 'lead_entry',
        'post_status' => 'publish',
        'post_title' => 'Lead ' . $fingerprint,
        'post_name' => $fingerprint,
        'post_content' => '',
    ));

    if (is_wp_error($post_id) || empty($post_id)) {
        return null;
    }

    $lead_cache[$fingerprint] = (int) $post_id;
    return $lead_cache[$fingerprint];
}

/**
 * Handles canonical ingest completion by creating a lead entry.
 *
 * @param array $payload
 * @return void
 */
function core_lead_engine_handle_ingest_event(array $payload) {
    $lead_id = core_lead_engine_materialize_lead_entry($payload);

    if (empty($lead_id)) {
        return;
    }

    core_lead_engine_action_lead_created($lead_id, $payload);
}

add_action('core_ingest_event', 'core_lead_engine_handle_ingest_event', 10, 1);

/**
 * Canonical lead entity placeholder.
 */
class Core_Lead_Engine_Lead {
    // Placeholder for canonical Lead entity.
}

/**
 * Canonical lead meta placeholder.
 */
class Core_Lead_Engine_Lead_Meta {
    // Placeholder for canonical Lead meta structure.
}
