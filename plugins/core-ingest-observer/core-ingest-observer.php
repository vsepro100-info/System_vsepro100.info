<?php
/**
 * Plugin Name: Core Ingest Observer
 * Description: Технический диагностический логгер для core_ingest_event.
 * Version: 0.1.1
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

define('CORE_INGEST_OBSERVER_METRICS_OPTION', 'core_ingest_observer_metrics');

add_action('core_ingest_event', 'core_ingest_observer_log_event', 10, 1);
add_action('admin_menu', 'core_ingest_observer_register_admin_page');

function core_ingest_observer_log_event(array $lead_meta) {
    $timestamp = current_time('c');
    $metrics = get_option(
        CORE_INGEST_OBSERVER_METRICS_OPTION,
        array(
            'total' => 0,
            'by_subtype' => array(),
            'last_ingest' => null,
        )
    );

    if (!is_array($metrics)) {
        $metrics = array(
            'total' => 0,
            'by_subtype' => array(),
            'last_ingest' => null,
        );
    }

    $metrics['total'] = isset($metrics['total']) ? (int) $metrics['total'] + 1 : 1;
    $metrics['by_subtype'] = isset($metrics['by_subtype']) && is_array($metrics['by_subtype'])
        ? $metrics['by_subtype']
        : array();

    $subtype = 'unknown';
    if (!empty($lead_meta['subtype']) && is_string($lead_meta['subtype'])) {
        $subtype = $lead_meta['subtype'];
    } elseif (!empty($lead_meta['source']) && is_string($lead_meta['source'])) {
        $subtype = $lead_meta['source'];
    } elseif (!empty($lead_meta['ingest_subtype']) && is_string($lead_meta['ingest_subtype'])) {
        $subtype = $lead_meta['ingest_subtype'];
    }

    if (!isset($metrics['by_subtype'][$subtype])) {
        $metrics['by_subtype'][$subtype] = 0;
    }

    $metrics['by_subtype'][$subtype] = (int) $metrics['by_subtype'][$subtype] + 1;
    $metrics['last_ingest'] = $timestamp;

    update_option(CORE_INGEST_OBSERVER_METRICS_OPTION, $metrics, false);
    error_log("[Core Ingest Observer] core_ingest_event received at {$timestamp}");
}

function core_ingest_observer_register_admin_page() {
    add_management_page(
        __('Ingest Metrics', 'core-ingest-observer'),
        __('Ingest Metrics', 'core-ingest-observer'),
        'manage_options',
        'core-ingest-observer-metrics',
        'core_ingest_observer_render_admin_page'
    );
}

function core_ingest_observer_render_admin_page() {
    $metrics = get_option(
        CORE_INGEST_OBSERVER_METRICS_OPTION,
        array(
            'total' => 0,
            'by_subtype' => array(),
            'last_ingest' => null,
        )
    );

    if (!is_array($metrics)) {
        $metrics = array(
            'total' => 0,
            'by_subtype' => array(),
            'last_ingest' => null,
        );
    }

    $total = isset($metrics['total']) ? (int) $metrics['total'] : 0;
    $last_ingest = !empty($metrics['last_ingest']) ? $metrics['last_ingest'] : __('Never', 'core-ingest-observer');
    $by_subtype = isset($metrics['by_subtype']) && is_array($metrics['by_subtype'])
        ? $metrics['by_subtype']
        : array();

    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Ingest Metrics', 'core-ingest-observer'); ?></h1>

        <table class="widefat striped" aria-describedby="ingest-metrics-summary">
            <thead>
                <tr>
                    <th scope="col"><?php echo esc_html__('Metric', 'core-ingest-observer'); ?></th>
                    <th scope="col"><?php echo esc_html__('Value', 'core-ingest-observer'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo esc_html__('Total ingest events', 'core-ingest-observer'); ?></td>
                    <td><?php echo esc_html($total); ?></td>
                </tr>
                <tr>
                    <td><?php echo esc_html__('Last ingest timestamp', 'core-ingest-observer'); ?></td>
                    <td><?php echo esc_html($last_ingest); ?></td>
                </tr>
            </tbody>
        </table>

        <h2><?php echo esc_html__('Events per subtype', 'core-ingest-observer'); ?></h2>
        <table class="widefat striped" aria-describedby="ingest-metrics-subtypes">
            <thead>
                <tr>
                    <th scope="col"><?php echo esc_html__('Subtype', 'core-ingest-observer'); ?></th>
                    <th scope="col"><?php echo esc_html__('Count', 'core-ingest-observer'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($by_subtype)) : ?>
                    <tr>
                        <td colspan="2"><?php echo esc_html__('No subtype data recorded yet.', 'core-ingest-observer'); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($by_subtype as $subtype => $count) : ?>
                        <tr>
                            <td><?php echo esc_html($subtype); ?></td>
                            <td><?php echo esc_html((int) $count); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
