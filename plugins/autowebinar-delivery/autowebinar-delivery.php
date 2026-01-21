<?php
/**
 * Plugin Name: AutoWebinar Delivery
 * Description: Каркасный плагин для хуков доставки AutoWebinar (без логики).
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_action('init', 'autowebinar_delivery_init');

function autowebinar_delivery_init() {
    do_action('autowebinar_delivery_init');
}

function autowebinar_delivery_payload(array $payload) {
    do_action('autowebinar_delivery_payload', $payload);
}
