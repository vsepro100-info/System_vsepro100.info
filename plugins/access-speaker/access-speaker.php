<?php
/**
 * Plugin Name: Speaker Access
 * Description: Добавляет роль Спикер и capability speaker для управления вебинарами и CTA.
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

/**
 * Ensures the canonical speaker role and capabilities exist.
 *
 * @return void
 */
function access_speaker_ensure_role() {
    $capability = 'speaker';

    $speaker_role = get_role('speaker');
    if (!$speaker_role) {
        add_role(
            'speaker',
            'Спикер',
            array(
                'read' => true,
                $capability => true,
            )
        );
    } else {
        $speaker_role->add_cap('read');
        $speaker_role->add_cap($capability);
    }

    $admin_role = get_role('administrator');
    if ($admin_role) {
        $admin_role->add_cap($capability);
    }
}

register_activation_hook(__FILE__, 'access_speaker_ensure_role');
add_action('init', 'access_speaker_ensure_role');
