<?php
/**
 * Plugin Name: Core Lead Engine
 * Description: Canonical hooks and entity placeholders for the lead lifecycle foundation.
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

/**
 * Canonical action hooks for the lead lifecycle.
 */
function core_lead_engine_action_lead_created(array $lead_payload) {
    do_action('core_lead_created', $lead_payload);
}

function core_lead_engine_action_lead_updated($lead_id, $lead_meta = null) {
    do_action('core_lead_engine_lead_updated', $lead_id, $lead_meta);
}

function core_lead_engine_action_lead_deleted($lead_id, $lead_meta = null) {
    do_action('core_lead_engine_lead_deleted', $lead_id, $lead_meta);
}

function core_lead_engine_action_lead_merged($primary_lead_id, $secondary_lead_id, $lead_meta = null) {
    do_action('core_lead_engine_lead_merged', $primary_lead_id, $secondary_lead_id, $lead_meta);
}

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
