<?php

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

register_activation_hook(ACCESS_SPEAKER_PLUGIN_FILE, 'access_speaker_ensure_role');
add_action('init', 'access_speaker_ensure_role');
