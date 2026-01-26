<?php
/**
 * Plugin Name: Webinar Room UI
 * Description: UI-only shortcode for webinar room with MVP logic.
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

add_shortcode('webinar_room', 'ui_webinar_room_render_shortcode');

/**
 * @param array<string, string> $atts
 * @return string
 */
function ui_webinar_room_render_shortcode($atts = array()) {
    $atts = shortcode_atts(
        array(
            'webinar_id' => 'default_webinar',
            'mode' => 'live',
        ),
        $atts,
        'webinar_room'
    );

    $webinar_id = (string) sanitize_text_field($atts['webinar_id']);
    if ($webinar_id === '') {
        $webinar_id = 'default_webinar';
    }

    $mode = (string) sanitize_text_field($atts['mode']);
    if (!in_array($mode, array('live', 'recording'), true)) {
        $mode = 'live';
    }

    $lead_id = 0;
    if (isset($_GET['lead_id'])) {
        $lead_id = absint($_GET['lead_id']);
    } else {
        $query_lead_id = get_query_var('lead_id');
        if (!empty($query_lead_id)) {
            $lead_id = absint($query_lead_id);
        }
    }

    $status_label = $mode === 'live' ? 'LIVE' : 'Запись';
    $video_label = $mode === 'live' ? 'Идёт онлайн-вебинар' : 'Запись вебинара';

    ob_start();
    ?>
    <section
        class="ui-webinar-room"
        data-webinar-id="<?php echo esc_attr($webinar_id); ?>"
        data-mode="<?php echo esc_attr($mode); ?>"
        data-lead-id="<?php echo esc_attr((string) $lead_id); ?>"
        data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
    >
        <div class="ui-webinar-room__screen" data-screen="entry">
            <header class="ui-webinar-room__header">
                <h2><?php echo esc_html__('Онлайн-вебинар', 'ui-webinar-room'); ?></h2>
                <span class="ui-webinar-room__status"><?php echo esc_html($status_label); ?></span>
            </header>
            <button class="ui-webinar-room__button" type="button" data-action="enter">
                <?php echo esc_html__('Войти на вебинар', 'ui-webinar-room'); ?>
            </button>
        </div>

        <div class="ui-webinar-room__screen" data-screen="viewing" hidden>
            <div class="ui-webinar-room__video">
                <iframe
                    title="<?php echo esc_attr($video_label); ?>"
                    src="about:blank"
                    loading="lazy"
                ></iframe>
                <p class="ui-webinar-room__video-label">
                    <?php echo esc_html($video_label); ?>
                </p>
            </div>
            <button class="ui-webinar-room__button" type="button" data-action="finish">
                <?php echo esc_html__('Завершить просмотр', 'ui-webinar-room'); ?>
            </button>
        </div>

        <div class="ui-webinar-room__screen" data-screen="complete" hidden>
            <p class="ui-webinar-room__complete-text">
                <?php echo esc_html__('Вебинар завершён', 'ui-webinar-room'); ?>
            </p>
            <button class="ui-webinar-room__button" type="button" data-action="consult">
                <?php echo esc_html__('Задать вопрос консультанту', 'ui-webinar-room'); ?>
            </button>
        </div>
    </section>

    <script>
        (function() {
            var roots = document.querySelectorAll('.ui-webinar-room');
            if (!roots.length) {
                return;
            }

            roots.forEach(function(root) {
                var webinarId = root.getAttribute('data-webinar-id') || 'default_webinar';
                var leadId = root.getAttribute('data-lead-id') || '';
                var ajaxUrl = root.getAttribute('data-ajax-url') || '';

                var entryButton = root.querySelector('[data-action="enter"]');
                var finishButton = root.querySelector('[data-action="finish"]');

                function showScreen(screenName) {
                    var screens = root.querySelectorAll('[data-screen]');
                    screens.forEach(function(screen) {
                        if (screen.getAttribute('data-screen') === screenName) {
                            screen.removeAttribute('hidden');
                        } else {
                            screen.setAttribute('hidden', 'hidden');
                        }
                    });

                    if (screenName === 'complete') {
                        emitCompleted();
                    }
                }

                function emitEntered() {
                    if (root.dataset.entered === '1') {
                        return;
                    }
                    root.dataset.entered = '1';

                    try {
                        var url = new URL(window.location.href);
                        url.searchParams.set('webinar_id', webinarId);
                        if (leadId) {
                            url.searchParams.set('lead_id', leadId);
                        }
                        fetch(url.toString(), {
                            credentials: 'same-origin'
                        }).catch(function() {});
                    } catch (error) {
                    }
                }

                function emitCompleted() {
                    if (root.dataset.completed === '1' || !ajaxUrl) {
                        return;
                    }
                    root.dataset.completed = '1';

                    var body = new URLSearchParams();
                    body.set('action', 'client_webinar_completed');
                    body.set('webinar_id', webinarId);
                    if (leadId) {
                        body.set('lead_id', leadId);
                    }

                    fetch(ajaxUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                        },
                        body: body.toString()
                    }).catch(function() {});
                }

                if (entryButton) {
                    entryButton.addEventListener('click', function() {
                        showScreen('viewing');
                        emitEntered();
                    });
                }

                if (finishButton) {
                    finishButton.addEventListener('click', function() {
                        showScreen('complete');
                    });
                }
            });
        })();
    </script>
    <?php

    return ob_get_clean();
}
