<?php
/**
 * Plugin Name: Webinar Public UI
 * Description: Public landing and admin settings for live webinars (UI only).
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_shortcode('webinar_public', 'ui_webinar_public_render_shortcode');
add_shortcode('whieda_webinar_public', 'ui_webinar_public_render_shortcode');
add_shortcode('webinar_admin', 'ui_webinar_admin_render_shortcode');
add_shortcode('whieda_live_admin', 'ui_webinar_admin_render_shortcode');

function ui_webinar_public_get_core_webinar() {
    $webinar_id = apply_filters('core_webinar_get_current', null);
    if (empty($webinar_id)) {
        return null;
    }

    $webinar_data = apply_filters('core_webinar_get', (int) $webinar_id, array());
    if (empty($webinar_data)) {
        return null;
    }

    return $webinar_data;
}

function ui_webinar_public_get_legacy_webinar() {
    $legacy_options = array(
        'whieda_live_settings',
        'whieda_live_webinar',
        'whieda_webinar_settings',
    );

    foreach ($legacy_options as $option_key) {
        $option_value = get_option($option_key, array());
        if (!is_array($option_value) || empty($option_value)) {
            continue;
        }

        return array(
            'title' => sanitize_text_field($option_value['title'] ?? ''),
            'start_datetime' => sanitize_text_field($option_value['start_datetime'] ?? ''),
            'status' => sanitize_text_field($option_value['status'] ?? ''),
            'poster_id' => absint($option_value['poster_id'] ?? 0),
            'poster_url' => esc_url_raw($option_value['poster_url'] ?? ''),
            'stream_type' => sanitize_key($option_value['stream_type'] ?? ''),
            'stream_src' => esc_url_raw($option_value['stream_src'] ?? ''),
            'chat_src' => esc_url_raw($option_value['chat_src'] ?? ''),
            'cta_text' => sanitize_text_field($option_value['cta_text'] ?? ''),
            'cta_link' => esc_url_raw($option_value['cta_link'] ?? ''),
        );
    }

    return null;
}

function ui_webinar_public_get_webinar_data() {
    $core_webinar = ui_webinar_public_get_core_webinar();
    if (!empty($core_webinar)) {
        return $core_webinar;
    }

    return ui_webinar_public_get_legacy_webinar();
}

function ui_webinar_public_format_status_label($status) {
    switch ($status) {
        case 'live':
            return 'LIVE';
        case 'ended':
            return 'Завершён';
        case 'scheduled':
        default:
            return 'Запланирован';
    }
}

function ui_webinar_public_render_shortcode() {
    $webinar = ui_webinar_public_get_webinar_data();

    $title = $webinar['title'] ?? 'Онлайн-вебинар';
    $start_datetime = $webinar['start_datetime'] ?? '';
    $status = $webinar['status'] ?? 'scheduled';
    $cta_text = $webinar['cta_text'] ?? 'Перейти в комнату вебинара';
    $cta_link = $webinar['cta_link'] ?? home_url('/account/webinar_room/');

    $status_label = ui_webinar_public_format_status_label($status);

    ob_start();
    ?>
    <section class="ui-webinar-public" data-start-datetime="<?php echo esc_attr($start_datetime); ?>">
        <header class="ui-webinar-public__header">
            <h1 class="ui-webinar-public__title"><?php echo esc_html($title); ?></h1>
            <span class="ui-webinar-public__status"><?php echo esc_html($status_label); ?></span>
        </header>

        <div class="ui-webinar-public__schedule">
            <p class="ui-webinar-public__datetime">
                <?php echo $start_datetime ? esc_html($start_datetime) : esc_html__('Дата и время уточняются', 'ui-webinar-public'); ?>
            </p>
            <p class="ui-webinar-public__countdown" data-countdown>
                <?php echo esc_html__('До начала осталось: --:--:--', 'ui-webinar-public'); ?>
            </p>
        </div>

        <div class="ui-webinar-public__cta">
            <a class="ui-webinar-public__button" href="<?php echo esc_url($cta_link); ?>">
                <?php echo esc_html($cta_text); ?>
            </a>
        </div>
    </section>

    <script>
        (function() {
            var root = document.querySelector('.ui-webinar-public');
            if (!root) {
                return;
            }

            var countdownEl = root.querySelector('[data-countdown]');
            var startDate = root.getAttribute('data-start-datetime');
            if (!countdownEl || !startDate) {
                return;
            }

            var target = new Date(startDate);
            if (Number.isNaN(target.getTime())) {
                return;
            }

            function pad(value) {
                return String(value).padStart(2, '0');
            }

            function tick() {
                var diff = target.getTime() - Date.now();
                if (diff <= 0) {
                    countdownEl.textContent = '<?php echo esc_js(__('Вебинар начался', 'ui-webinar-public')); ?>';
                    return;
                }

                var totalSeconds = Math.floor(diff / 1000);
                var hours = Math.floor(totalSeconds / 3600);
                var minutes = Math.floor((totalSeconds % 3600) / 60);
                var seconds = totalSeconds % 60;

                countdownEl.textContent = '<?php echo esc_js(__('До начала осталось:', 'ui-webinar-public')); ?> ' +
                    pad(hours) + ':' + pad(minutes) + ':' + pad(seconds);

                requestAnimationFrame(function() {
                    setTimeout(tick, 1000);
                });
            }

            tick();
        })();
    </script>
    <?php

    return ob_get_clean();
}

function ui_webinar_admin_render_shortcode() {
    if (!current_user_can('edit_pages')) {
        return '<p class="ui-webinar-admin__notice">' .
            esc_html__('Доступ только для редакторов.', 'ui-webinar-public') .
            '</p>';
    }

    $webinar = ui_webinar_public_get_webinar_data();
    $webinar_id = $webinar['id'] ?? 0;

    $message = '';
    if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['ui_webinar_admin_nonce'])) {
        check_admin_referer('ui_webinar_admin_save', 'ui_webinar_admin_nonce');

        $payload = array(
            'webinar_id' => isset($_POST['webinar_id']) ? absint($_POST['webinar_id']) : 0,
            'title' => sanitize_text_field($_POST['title'] ?? ''),
            'start_datetime' => sanitize_text_field($_POST['start_datetime'] ?? ''),
            'status' => sanitize_text_field($_POST['status'] ?? 'scheduled'),
            'poster_id' => absint($_POST['poster_id'] ?? 0),
            'poster_url' => esc_url_raw($_POST['poster_url'] ?? ''),
            'stream_type' => sanitize_key($_POST['stream_type'] ?? ''),
            'stream_src' => esc_url_raw($_POST['stream_src'] ?? ''),
            'chat_src' => esc_url_raw($_POST['chat_src'] ?? ''),
            'cta_text' => sanitize_text_field($_POST['cta_text'] ?? ''),
            'cta_link' => esc_url_raw($_POST['cta_link'] ?? ''),
        );

        $updated_id = $payload['webinar_id'] ?: $webinar_id;
        do_action('core_webinar_upsert', $payload, $updated_id);

        $message = esc_html__('Сохранено.', 'ui-webinar-public');
        $webinar = ui_webinar_public_get_webinar_data();
        $webinar_id = $webinar['id'] ?? $updated_id;
    }

    $title = $webinar['title'] ?? '';
    $start_datetime = $webinar['start_datetime'] ?? '';
    $status = $webinar['status'] ?? 'scheduled';
    $poster_id = $webinar['poster_id'] ?? 0;
    $poster_url = $webinar['poster_url'] ?? '';
    $stream_type = $webinar['stream_type'] ?? '';
    $stream_src = $webinar['stream_src'] ?? '';
    $chat_src = $webinar['chat_src'] ?? '';
    $cta_text = $webinar['cta_text'] ?? '';
    $cta_link = $webinar['cta_link'] ?? '';

    ob_start();
    ?>
    <section class="ui-webinar-admin">
        <h1 class="ui-webinar-admin__title"><?php echo esc_html__('Настройки вебинара', 'ui-webinar-public'); ?></h1>
        <?php if (!empty($message)) : ?>
            <p class="ui-webinar-admin__message"><?php echo esc_html($message); ?></p>
        <?php endif; ?>
        <form method="post" class="ui-webinar-admin__form">
            <?php wp_nonce_field('ui_webinar_admin_save', 'ui_webinar_admin_nonce'); ?>
            <input type="hidden" name="webinar_id" value="<?php echo esc_attr((string) $webinar_id); ?>" />

            <label class="ui-webinar-admin__field">
                <span><?php echo esc_html__('Заголовок', 'ui-webinar-public'); ?></span>
                <input type="text" name="title" value="<?php echo esc_attr($title); ?>" />
            </label>

            <label class="ui-webinar-admin__field">
                <span><?php echo esc_html__('Дата и время (сайт)', 'ui-webinar-public'); ?></span>
                <input type="text" name="start_datetime" value="<?php echo esc_attr($start_datetime); ?>" />
            </label>

            <label class="ui-webinar-admin__field">
                <span><?php echo esc_html__('Статус', 'ui-webinar-public'); ?></span>
                <select name="status">
                    <option value="scheduled" <?php selected($status, 'scheduled'); ?>><?php echo esc_html__('Запланирован', 'ui-webinar-public'); ?></option>
                    <option value="live" <?php selected($status, 'live'); ?>><?php echo esc_html__('В эфире', 'ui-webinar-public'); ?></option>
                    <option value="ended" <?php selected($status, 'ended'); ?>><?php echo esc_html__('Завершён', 'ui-webinar-public'); ?></option>
                </select>
            </label>

            <label class="ui-webinar-admin__field">
                <span><?php echo esc_html__('Poster ID', 'ui-webinar-public'); ?></span>
                <input type="number" name="poster_id" value="<?php echo esc_attr((string) $poster_id); ?>" />
            </label>

            <label class="ui-webinar-admin__field">
                <span><?php echo esc_html__('Poster URL', 'ui-webinar-public'); ?></span>
                <input type="url" name="poster_url" value="<?php echo esc_attr($poster_url); ?>" />
            </label>

            <label class="ui-webinar-admin__field">
                <span><?php echo esc_html__('Тип стрима', 'ui-webinar-public'); ?></span>
                <select name="stream_type">
                    <option value="" <?php selected($stream_type, ''); ?>><?php echo esc_html__('—', 'ui-webinar-public'); ?></option>
                    <option value="obs" <?php selected($stream_type, 'obs'); ?>>OBS</option>
                    <option value="zoom" <?php selected($stream_type, 'zoom'); ?>>Zoom</option>
                    <option value="telegram" <?php selected($stream_type, 'telegram'); ?>>Telegram</option>
                </select>
            </label>

            <label class="ui-webinar-admin__field">
                <span><?php echo esc_html__('Источник стрима', 'ui-webinar-public'); ?></span>
                <input type="url" name="stream_src" value="<?php echo esc_attr($stream_src); ?>" />
            </label>

            <label class="ui-webinar-admin__field">
                <span><?php echo esc_html__('Источник чата', 'ui-webinar-public'); ?></span>
                <input type="url" name="chat_src" value="<?php echo esc_attr($chat_src); ?>" />
            </label>

            <label class="ui-webinar-admin__field">
                <span><?php echo esc_html__('CTA текст', 'ui-webinar-public'); ?></span>
                <input type="text" name="cta_text" value="<?php echo esc_attr($cta_text); ?>" />
            </label>

            <label class="ui-webinar-admin__field">
                <span><?php echo esc_html__('CTA ссылка', 'ui-webinar-public'); ?></span>
                <input type="url" name="cta_link" value="<?php echo esc_attr($cta_link); ?>" />
            </label>

            <button class="ui-webinar-admin__button" type="submit">
                <?php echo esc_html__('Сохранить', 'ui-webinar-public'); ?>
            </button>
        </form>
    </section>
    <?php

    return ob_get_clean();
}
