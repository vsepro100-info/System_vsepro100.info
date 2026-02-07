<?php

defined('ABSPATH') || exit;

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
    if (function_exists('legacy_referral_append_ref')) {
        $cta_link = legacy_referral_append_ref($cta_link);
    }

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

    $is_admin = current_user_can('manage_options');
    $webinar = ui_webinar_public_get_webinar_data();
    $webinar_id = $webinar['id'] ?? 0;

    $message = '';
    if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['ui_webinar_admin_nonce'])) {
        check_admin_referer('ui_webinar_admin_save', 'ui_webinar_admin_nonce');

        $current_status = $webinar['status'] ?? 'scheduled';
        $current_stream_src = $webinar['stream_src'] ?? '';
        $current_chat_src = $webinar['chat_src'] ?? '';
        $current_poster_id = $webinar['poster_id'] ?? 0;
        $current_poster_url = $webinar['poster_url'] ?? '';

        $payload = array(
            'webinar_id' => isset($_POST['webinar_id']) ? absint($_POST['webinar_id']) : 0,
            'title' => sanitize_text_field($_POST['title'] ?? ''),
            'start_datetime' => sanitize_text_field($_POST['start_datetime'] ?? ''),
            'status' => $current_status,
            'poster_id' => absint($_POST['poster_id'] ?? $current_poster_id),
            'poster_url' => esc_url_raw($_POST['poster_url'] ?? $current_poster_url),
            'stream_type' => sanitize_key($_POST['stream_type'] ?? ''),
            'stream_src' => $is_admin ? esc_url_raw($_POST['stream_src'] ?? $current_stream_src) : $current_stream_src,
            'chat_src' => $is_admin ? esc_url_raw($_POST['chat_src'] ?? $current_chat_src) : $current_chat_src,
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
    $poster_id = $webinar['poster_id'] ?? 0;
    $poster_url = $webinar['poster_url'] ?? '';
    $stream_type = $webinar['stream_type'] ?? '';
    $stream_src = $webinar['stream_src'] ?? '';
    $chat_src = $webinar['chat_src'] ?? '';
    $cta_text = $webinar['cta_text'] ?? 'Перейти в комнату вебинара';
    $cta_link = $webinar['cta_link'] ?? home_url('/account/webinar_room/');

    wp_enqueue_media();

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
                <span><?php echo esc_html__('Постер', 'ui-webinar-public'); ?></span>
                <input type="hidden" name="poster_id" value="<?php echo esc_attr((string) $poster_id); ?>" data-poster-id />
                <input type="hidden" name="poster_url" value="<?php echo esc_attr($poster_url); ?>" data-poster-url />
                <div class="ui-webinar-admin__poster">
                    <div class="ui-webinar-admin__poster-preview" data-poster-preview>
                        <?php if (!empty($poster_url)) : ?>
                            <img src="<?php echo esc_url($poster_url); ?>" alt="<?php echo esc_attr__('Постер вебинара', 'ui-webinar-public'); ?>" />
                        <?php else : ?>
                            <span><?php echo esc_html__('Постер не выбран', 'ui-webinar-public'); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="ui-webinar-admin__poster-actions">
                        <button class="ui-webinar-admin__button" type="button" data-poster-upload>
                            <?php echo esc_html__('Загрузить постер', 'ui-webinar-public'); ?>
                        </button>
                        <button class="ui-webinar-admin__button" type="button" data-poster-clear>
                            <?php echo esc_html__('Убрать постер', 'ui-webinar-public'); ?>
                        </button>
                    </div>
                </div>
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
                <span><?php echo esc_html__('CTA текст', 'ui-webinar-public'); ?></span>
                <input type="text" name="cta_text" value="<?php echo esc_attr($cta_text); ?>" />
            </label>

            <label class="ui-webinar-admin__field">
                <span><?php echo esc_html__('CTA ссылка', 'ui-webinar-public'); ?></span>
                <input type="url" name="cta_link" value="<?php echo esc_attr($cta_link); ?>" />
            </label>

            <?php if ($is_admin) : ?>
                <details class="ui-webinar-admin__admin-settings">
                    <summary class="ui-webinar-admin__admin-summary">
                        <?php echo esc_html__('⚙️ Admin Settings', 'ui-webinar-public'); ?>
                    </summary>
                    <label class="ui-webinar-admin__field">
                        <span><?php echo esc_html__('Источник стрима (override)', 'ui-webinar-public'); ?></span>
                        <input type="url" name="stream_src" value="<?php echo esc_attr($stream_src); ?>" />
                    </label>

                    <label class="ui-webinar-admin__field">
                        <span><?php echo esc_html__('Источник чата (override)', 'ui-webinar-public'); ?></span>
                        <input type="url" name="chat_src" value="<?php echo esc_attr($chat_src); ?>" />
                    </label>
                </details>
            <?php endif; ?>

            <button class="ui-webinar-admin__button" type="submit">
                <?php echo esc_html__('Сохранить', 'ui-webinar-public'); ?>
            </button>
        </form>
    </section>
    <script>
        (function() {
            var form = document.querySelector('.ui-webinar-admin__form');
            if (!form || typeof wp === 'undefined' || !wp.media) {
                return;
            }

            var uploadButton = form.querySelector('[data-poster-upload]');
            var clearButton = form.querySelector('[data-poster-clear]');
            var posterIdField = form.querySelector('[data-poster-id]');
            var posterUrlField = form.querySelector('[data-poster-url]');
            var posterPreview = form.querySelector('[data-poster-preview]');

            function renderPreview(url) {
                if (!posterPreview) {
                    return;
                }

                if (url) {
                    posterPreview.innerHTML = '<img src="' + url + '" alt="<?php echo esc_js(__('Постер вебинара', 'ui-webinar-public')); ?>">';
                    return;
                }

                posterPreview.innerHTML = '<span><?php echo esc_js(__('Постер не выбран', 'ui-webinar-public')); ?></span>';
            }

            if (uploadButton) {
                uploadButton.addEventListener('click', function() {
                    var frame = wp.media({
                        title: '<?php echo esc_js(__('Выберите постер', 'ui-webinar-public')); ?>',
                        button: { text: '<?php echo esc_js(__('Использовать постер', 'ui-webinar-public')); ?>' },
                        multiple: false
                    });

                    frame.on('select', function() {
                        var attachment = frame.state().get('selection').first();
                        if (!attachment) {
                            return;
                        }

                        var data = attachment.toJSON();
                        if (posterIdField) {
                            posterIdField.value = data.id || '';
                        }
                        if (posterUrlField) {
                            posterUrlField.value = data.url || '';
                        }
                        renderPreview(data.url || '');
                    });

                    frame.open();
                });
            }

            if (clearButton) {
                clearButton.addEventListener('click', function() {
                    if (posterIdField) {
                        posterIdField.value = '';
                    }
                    if (posterUrlField) {
                        posterUrlField.value = '';
                    }
                    renderPreview('');
                });
            }
        })();
    </script>
    <?php

    return ob_get_clean();
}
