<?php

defined('ABSPATH') || exit;

function ui_webinar_entry_render_shortcode() {
    $registration_url = home_url('/signup/');
    if (function_exists('legacy_referral_append_ref')) {
        $registration_url = legacy_referral_append_ref($registration_url);
    }

    ob_start();
    ?>
    <section class="webinar-entry">
        <h2><?php echo esc_html__('Онлайн-вебинар', 'ui-webinar-entry'); ?></h2>
        <ul>
            <li><?php echo esc_html__('Что это: обзор ключевой темы в прямом эфире.', 'ui-webinar-entry'); ?></li>
            <li><?php echo esc_html__('Формат: короткая презентация и ответы на вопросы.', 'ui-webinar-entry'); ?></li>
            <li><?php echo esc_html__('Для кого: для тех, кто хочет разобраться без лишних деталей.', 'ui-webinar-entry'); ?></li>
            <li><?php echo esc_html__('Что дальше: получите материалы и ссылку на запись.', 'ui-webinar-entry'); ?></li>
        </ul>
        <a class="webinar-entry__cta" href="<?php echo esc_url($registration_url); ?>">
            <?php echo esc_html__('Перейти к регистрации', 'ui-webinar-entry'); ?>
        </a>
    </section>
    <?php

    return ob_get_clean();
}
