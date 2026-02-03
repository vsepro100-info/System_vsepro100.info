<?php
/**
 * Plugin Name: Legacy Referral Flow
 * Description: Восстанавливает legacy-поток ?ref → wh_ref и публичные карточки консультанта.
 * Version: 0.1.0
 * Author: vsepro100.info
 * Author URI: https://vsepro100.info
 */

defined('ABSPATH') || exit;

define('LEGACY_REFERRAL_COOKIE', 'wh_ref');

function legacy_referral_capture_ref() {
    if (!isset($_GET['ref'])) {
        return;
    }

    $ref = sanitize_user(wp_unslash($_GET['ref']));
    if ($ref === '') {
        return;
    }

    setcookie(
        LEGACY_REFERRAL_COOKIE,
        $ref,
        time() + MONTH_IN_SECONDS,
        COOKIEPATH ?: '/',
        COOKIE_DOMAIN
    );

    $_COOKIE[LEGACY_REFERRAL_COOKIE] = $ref;
}

add_action('init', 'legacy_referral_capture_ref');

function legacy_referral_get_ref() {
    if (!isset($_COOKIE[LEGACY_REFERRAL_COOKIE])) {
        return '';
    }

    $ref = wp_unslash($_COOKIE[LEGACY_REFERRAL_COOKIE]);
    return sanitize_user($ref);
}

function legacy_referral_append_ref($url) {
    $ref = legacy_referral_get_ref();
    if ($ref === '') {
        return $url;
    }

    return add_query_arg('ref', $ref, $url);
}

if (!function_exists('whieda_contact_block')) {
    function whieda_contact_block($meta) {
        ob_start(); ?>
    <div class="contact-block-wrapper">
        <h3 class="title">💬 Связаться прямо сейчас</h3>
        <div class="contact-columns">
            <div>
                <h4>📲 Связаться со мной</h4>
                <?php if ($meta('telegram')) : ?>
                    <p><img src="/wp-content/uploads/icons/telegram.png" style="width:20px;vertical-align:middle;"> <a href="https://t.me/<?php echo esc_attr(ltrim($meta('telegram'), '@')); ?>" target="_blank">@<?php echo esc_html(ltrim($meta('telegram'), '@')); ?></a></p>
                <?php endif; ?>
                <?php if ($meta('whatsapp')) : ?>
                    <p><img src="/wp-content/uploads/icons/whatsapp.png" style="width:20px;vertical-align:middle;"> <a href="https://wa.me/<?php echo esc_attr(preg_replace('/\D/', '', $meta('whatsapp'))); ?>" target="_blank"><?php echo esc_html($meta('whatsapp')); ?></a></p>
                <?php endif; ?>
            </div>
            <div>
                <h4>✨ Я в соцсетях</h4>
                <div class="social-icons">
                    <?php
                    $socials = array('instagram', 'vk', 'facebook', 'youtube', 'tiktok', 'unilive');
                    foreach ($socials as $key) {
                        $link = $meta($key);
                        if ($link) {
                            $url = strpos($link, 'http') === 0 ? $link : 'https://' . ltrim($link, '/');
                            ?>
                            <a href="<?php echo esc_url($url); ?>" target="_blank">
                                <img src="/wp-content/uploads/icons/<?php echo esc_attr($key); ?>.png" alt="<?php echo esc_attr($key); ?>" style="width:40px;height:40px;">
                            </a>
                        <?php }
                    } ?>
                </div>
            </div>
        </div>
    </div>
    <?php
        return ob_get_clean();
    }
}

function legacy_referral_render_contact_card() {
    $ref = legacy_referral_get_ref();
    if (!$ref) {
        return 'Не удалось определить консультанта.';
    }

    $user = get_user_by('login', sanitize_user($ref));
    if (!$user) {
        return 'Консультант не найден.';
    }

    $user_id = $user->ID;
    $meta = fn($key) => trim((string) get_user_meta($user_id, $key, true));

    $photo = $meta('photo');
    $about = wpautop($meta('about'));
    $is_approved = $meta('is_approved');
    $first_name = $meta('first_name');
    $last_name = $meta('last_name');

    ob_start(); ?>
    <style>
    .whieda-contact-wrapper {
        width: 100%;
        box-sizing: border-box;
    }

    body.page-id-1163 .whieda-contact-wrapper {
        max-width: 860px;
        margin: 20px auto;
        padding: 24px;
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    @media (max-width: 768px) {
        .whieda-contact-wrapper {
            margin: 0 !important;
            padding: 0 !important;
            background: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }
    }

    .contact-block-wrapper {
        background: #f0f7ff;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 40px;
    }

    .contact-block-wrapper h3.title {
        text-align: center;
        margin-bottom: 24px;
        font-size: 20px;
    }

    @media (max-width: 768px) {
        .contact-block-wrapper {
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 24px;
        }
    }

    .contact-columns {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        align-items: start;
    }

    .contact-columns h4 {
        margin-bottom: 12px;
        font-size: 18px;
    }

    .social-icons {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    .photo-block {
        text-align: center;
        margin-bottom: 20px;
    }

    .photo-block img {
        width: 120px;
        height: 120px;
        border-radius: 16px;
        object-fit: cover;
    }

    .whieda-consultant-card h2 {
        text-align: center;
        font-size: clamp(20px, 4vw, 26px);
        margin-bottom: 10px;
    }

    .whieda-badge {
        text-align: center;
        margin-bottom: 20px;
    }

    .whieda-badge span {
        background: #8C20FF;
        color: #fff;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 14px;
    }

    .whieda-about {
        margin-bottom: 30px;
    }

    .whieda-about-content {
        padding: 10px 15px;
        background: #f8f8f8;
        border-left: 4px solid #8C20FF;
        border-radius: 8px;
        line-height: 1.6;
    }

    .whieda-video-block {
        margin-bottom: 40px;
    }

    .whieda-video-block div {
        background: #f8f8f8;
        border-left: 4px solid #8C20FF;
        padding: 15px;
        border-radius: 8px;
        line-height: 1.6;
    }

    .whieda-reasons-grid {
        margin: 40px 0;
    }

    .reasons-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
    }

    .reason-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        padding: 20px;
        text-align: center;
        transition: box-shadow 0.3s;
    }

    .reason-card:hover {
        box-shadow: 0 6px 20px rgba(0,0,0,0.12);
    }

    .reason-icon {
        font-size: 32px;
        margin-bottom: 12px;
    }

    .reason-text {
        font-size: 16px;
        line-height: 1.4;
    }

    .site-links {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 12px;
    }

    .site-links a {
        display: block;
        padding: 10px 12px;
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 8px;
        text-decoration: none;
        color: #333;
        font-weight: 500;
    }
    </style>

    <div class="whieda-contact-wrapper">
        <div class="whieda-consultant-card">
            <?php if ($photo) : ?>
                <div class="photo-block">
                    <img src="<?php echo esc_url($photo); ?>" alt="Фото консультанта">
                </div>
            <?php endif; ?>

            <?php if ($first_name || $last_name) : ?>
                <h2><?php echo esc_html("$first_name $last_name"); ?></h2>
            <?php endif; ?>

            <?php if ($is_approved) : ?>
                <div class="whieda-badge">
                    <span>Официальный партнёр WHIEDA</span>
                </div>
            <?php endif; ?>

            <?php if ($about) : ?>
                <div class="whieda-about">
                    <h3>🙋‍♂️ О себе</h3>
                    <div class="whieda-about-content"><?php echo $about; ?></div>
                </div>
            <?php endif; ?>

            <!-- ✅ Первый блок контактов -->
            <?php echo whieda_contact_block($meta); ?>

            <!-- ✅ Почему мой выбор — WHIEDA? -->
            <div class="whieda-video-block">
                <h3>💬 Почему мой выбор — WHIEDA?</h3>
                <div>
                    Потому что это реальная международная компания с продуктами, офисами и деньгами. <br><br>
                    ❌ Это не просто «онлайн-стартап». <br>
                    ✅ Это готовая система, где можно зарабатывать с первого месяца и улучшить здоровье своей семьи.
                </div>
            </div>

            <!-- ✅ Видео-слайдер -->
            <?php echo do_shortcode('[whieda_video_slider]'); ?>

            <!-- ✅ Блок преимуществ -->
            <div class="whieda-reasons-grid">
                <h3 style="text-align:center; margin-bottom:24px;">💬 Почему вам стоит связаться со мной прямо сейчас?</h3>
                <div class="reasons-grid">
                    <div class="reason-card"><div class="reason-icon">💡</div><div class="reason-text">Помогу выбрать лучшее решение для вашей ситуации</div></div>
                    <div class="reason-card"><div class="reason-icon">⏱️</div><div class="reason-text">Вы сэкономите время и деньги</div></div>
                    <div class="reason-card"><div class="reason-icon">🤝</div><div class="reason-text">Всегда на связи и помогу в любой момент</div></div>
                    <div class="reason-card"><div class="reason-icon">📈</div><div class="reason-text">Получите проверенную стратегию для быстрого старта</div></div>
                    <div class="reason-card"><div class="reason-icon">❤️</div><div class="reason-text">Поддержка и ответы на все вопросы</div></div>
                    <div class="reason-card"><div class="reason-icon">🎯</div><div class="reason-text">Фокус на ваших целях и результатах</div></div>
                </div>
            </div>

            <!-- ✅ Второй блок контактов -->
            <?php echo whieda_contact_block($meta); ?>

            <!-- ✅ Мои сайты -->
            <div style="margin-top:40px;">
                <h3>🌐 Мои сайты</h3>
                <div class="site-links">
                    <?php
                    $links = array(
                        array('💼', 'Как сетевику зарабатывать без закупок', '/setevikam'),
                        array('👶', 'Доход для мам в декрете — с заботой о семье', '/mamam'),
                        array('👵', 'Как улучшить здоровье и доход на пенсии', '/pensioneram'),
                        array('💻', 'Как фрилансеру создать дополнительный доход', '/frilans'),
                        array('📈', 'Готовая бизнес-система под ключ за 1 день', '/biznes'),
                        array('❤️', 'Что выбрать для здоровья всей семьи', '/zdorove'),
                        array('🚫', 'Доход без рисков и вложений — это реально?', '/bez-vlozheniy'),
                        array('📲', 'Онлайн-подработка с телефона за 1 час в день', '/podrabotka'),
                        array('🛒', 'Готовый интернет-магазин WHIEDA — без затрат', '/shop'),
                        array('🎯', 'WHIEDA для арбитражников: белое, выгодное, CPA', '/arbitrazh'),
                        array('🎤', 'Как монетизировать блог через WHIEDA', '/blogeram'),
                        array('🎓', 'Дополнительный доход для студентов без графика', '/studentam'),
                    );
                    foreach ($links as $link) {
                        $emoji = $link[0];
                        $title = $link[1];
                        $slug = $link[2];
                        $url = $slug . '/?ref=' . rawurlencode($ref);
                        echo '<a href="' . esc_url($url) . '" target="_blank">' . esc_html($emoji . ' ' . $title) . '</a>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php

    return ob_get_clean();
}

add_shortcode('whieda_contact_card', 'legacy_referral_render_contact_card');

function legacy_referral_render_public_contact_info() {
    if (legacy_referral_get_ref() !== '') {
        return do_shortcode('[whieda_contact_card]');
    }

    return do_shortcode('[core_web_form]');
}

add_shortcode('public_contact_info', 'legacy_referral_render_public_contact_info');
