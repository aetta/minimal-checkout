<?php
if (!defined('ABSPATH')) exit;

add_action('init', function () {
    if (is_admin()) return;
    if (!mct_wc_ready()) return;
    $opt = mct_get_options();
    if (!empty($opt['strip_wc_styles'])) add_filter('woocommerce_enqueue_styles', '__return_empty_array', 9999);
});

function mct_is_plugin_checkout_context()
{
    if (mct_is_checkout_page()) return true;
    if (!mct_wc_ready() || !WC()->session) return false;
    return (bool) WC()->session->get('mct_on');
}

add_action('template_redirect', function () {
    if (mct_wc_ready() && WC()->session && mct_is_checkout_page()) WC()->session->set('mct_on', 1);
    if (mct_is_checkout_page()) add_filter('woocommerce_is_checkout', '__return_true', 99);
}, 1);

function mct_remove_coupon_everywhere()
{
    remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
    remove_action('woocommerce_checkout_after_order_review', 'woocommerce_checkout_coupon_form', 10);
    remove_action('woocommerce_checkout_before_order_review', 'woocommerce_checkout_coupon_form', 10);
    remove_action('woocommerce_review_order_before_order_total', 'woocommerce_checkout_coupon_form', 10);
    remove_action('woocommerce_before_cart', 'woocommerce_checkout_coupon_form', 10);
    for ($p = -10; $p <= 50; $p++) {
        remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', $p);
        remove_action('woocommerce_checkout_after_order_review', 'woocommerce_checkout_coupon_form', $p);
        remove_action('woocommerce_checkout_before_order_review', 'woocommerce_checkout_coupon_form', $p);
        remove_action('woocommerce_review_order_before_order_total', 'woocommerce_checkout_coupon_form', $p);
        remove_action('woocommerce_before_cart', 'woocommerce_checkout_coupon_form', $p);
    }
}

function mct_move_coupon_once()
{
    static $done = false;
    if ($done) return;
    $done = true;
    if (!mct_wc_ready()) return;
    if (!mct_is_plugin_checkout_context()) return;
    mct_remove_coupon_everywhere();
    $opt = mct_get_options();
    if (!empty($opt['show_coupon'])) {
        if (!has_action('woocommerce_review_order_before_order_total', 'mct_render_coupon_block')) {
            add_action('woocommerce_review_order_before_order_total', 'mct_render_coupon_block', 9);
        }
    }
}
add_action('template_redirect', 'mct_move_coupon_once', 1);

function mct_render_coupon_block()
{
    static $printed = false;
    if ($printed) return;
    $printed = true;
    echo '<div class="mct-coupon-slot">';
    woocommerce_checkout_coupon_form();
    echo '</div>';
}

add_filter('body_class', function ($classes) {
    if (mct_is_checkout_page()) {
        $classes[] = 'mct-checkout';
        $opt = mct_get_options();
        $layout = isset($opt['layout']) ? $opt['layout'] : 'full';
        if ($layout === 'two') $classes[] = 'mct-two';
        elseif ($layout === 'two_sidebar') $classes[] = 'mct-two-sidebar';
        else $classes[] = 'mct-one';
        if (!empty($opt['hide_page_title'])) $classes[] = 'mct-hide-title';
    }
    return $classes;
});

add_filter('the_title', function ($title, $id) {
    if (!mct_is_checkout_page()) return $title;
    $opt = mct_get_options();
    $pid = intval(get_option('mct_page_id'));
    if (!empty($opt['hide_page_title']) && $pid && intval($id) === $pid) return '';
    return $title;
}, 10, 2);

add_filter('the_content', function ($c) {
    if (!mct_is_checkout_page()) return $c;
    $opt = mct_get_options();
    $layout_opt = isset($opt['layout']) ? $opt['layout'] : 'full';
    $layout_class = $layout_opt === 'two' ? 'mct-two' : ($layout_opt === 'two_sidebar' ? 'mct-two-sidebar' : 'mct-one');
    return '<div class="mct-scope ' . esc_attr($layout_class) . '">' . $c . '</div>';
}, 9);

add_action('wp_enqueue_scripts', function () {
    if (!is_checkout()) return;
    $opt = mct_get_options();
    if (!empty($opt['enable_css'])) {
        $css = MCT_DIR . 'assets/minimal-checkout.css';
        $css_ver = file_exists($css) ? filemtime($css) : '2.2.0';
        wp_enqueue_style('mct-style', MCT_URL . 'assets/minimal-checkout.css', [], $css_ver);
    }
}, 999);

add_shortcode('minimal_checkout', function () {
    if (!mct_wc_ready()) return '';
    if (WC()->cart === null) wc_load_cart();
    ob_start();
    if (class_exists('WC_Shortcode_Checkout')) echo WC_Shortcode_Checkout::output([]);
    else echo do_shortcode('[woocommerce_checkout]');
    return ob_get_clean();
});

function mct_apply_on_group($items)
{
    $opt = mct_get_options();
    $allowed = mct_allowed_keys();
    foreach ($items as $key => $cfg) if (!isset($allowed[$key])) unset($items[$key]);
    $p = 10;
    foreach ($items as $key => &$cfg) {
        if (!empty($opt['fields'][$key]['label'])) $cfg['label'] = $opt['fields'][$key]['label'];
        $cfg['priority'] = $p;
        $p += 10;
    }
    return $items;
}

add_filter('woocommerce_default_address_fields', function ($fields) {
    $opt = mct_get_options();
    $allowed = mct_allowed_keys();
    foreach ($fields as $key => $cfg) {
        $cand = ['billing_' . $key => 1, 'shipping_' . $key => 1];
        if (!array_intersect_key($cand, $allowed)) unset($fields[$key]);
        if (!empty($opt['fields']['billing_' . $key]['label'])) $fields[$key]['label'] = $opt['fields']['billing_' . $key]['label'];
    }
    return $fields;
}, 9999);

add_filter('woocommerce_billing_fields',  'mct_apply_on_group', 9999);
add_filter('woocommerce_shipping_fields', 'mct_apply_on_group', 9999);

add_filter('woocommerce_checkout_fields', function ($fields) {
    if (isset($fields['billing']))  $fields['billing'] = mct_apply_on_group($fields['billing']);
    if (isset($fields['shipping'])) $fields['shipping'] = mct_apply_on_group($fields['shipping']);
    return $fields;
}, 9999);

add_filter('woocommerce_cart_needs_shipping_address', function () {
    $opt = mct_get_options();
    return !empty($opt['ship_to_different']);
});

add_filter('woocommerce_enable_order_notes_field', function () {
    $opt = mct_get_options();
    return !empty($opt['show_notes']);
});

add_filter('woocommerce_cart_item_name', function ($name, $cart_item, $cart_item_key) {
    if (!mct_is_plugin_checkout_context()) return $name;
    $opt = mct_get_options();
    if (empty($opt['show_thumbs'])) return $name;
    if (empty($cart_item['data']) || !is_a($cart_item['data'], 'WC_Product')) return $name;
    $product = $cart_item['data'];
    $image_id = method_exists($product, 'get_image_id') ? $product->get_image_id() : 0;
    if (!$image_id && method_exists($product, 'get_parent_id')) {
        $parent_id = $product->get_parent_id();
        if ($parent_id) {
            $parent = wc_get_product($parent_id);
            if ($parent && method_exists($parent, 'get_image_id')) $image_id = $parent->get_image_id();
        }
    }
    $thumb_html = '';
    if ($image_id) {
        $thumb_html = wp_get_attachment_image($image_id, 'woocommerce_thumbnail', false, ['class' => 'mct-item-thumb']);
    } elseif (function_exists('wc_placeholder_img_src')) {
        $src = wc_placeholder_img_src('woocommerce_thumbnail');
        if ($src) $thumb_html = '<img src="' . esc_url($src) . '" class="mct-item-thumb" alt="" />';
    }
    if (!$thumb_html) return $name;
    return '<span class="mct-item">' . $thumb_html . '<span class="mct-item-name">' . $name . '</span></span>';
}, 10, 3);

add_action('woocommerce_checkout_before_customer_details', function () {
    if (!mct_is_plugin_checkout_context()) return;
    echo '<div class="mct-grid"><div class="mct-card"><h3 class="mct-title">' . esc_html__('Billing details', 'minimal-checkout') . '</h3>';
}, 1);

add_action('woocommerce_checkout_after_customer_details', function () {
    if (!mct_is_plugin_checkout_context()) return;
    echo '</div>';
}, 1);

add_action('woocommerce_checkout_before_order_review_heading', function () {
    if (!mct_is_plugin_checkout_context()) return;
    echo '<div class="mct-card"><h3 class="mct-title">' . esc_html__('Your order', 'minimal-checkout') . '</h3>';
}, 1);

add_action('woocommerce_checkout_after_order_review', function () {
    if (!mct_is_plugin_checkout_context()) return;
    $opt = mct_get_options();
    $icons = mct_icons_map();
    $out = '';
    foreach ($opt['socials'] as $s) {
        $icon = isset($s['icon']) ? sanitize_key($s['icon']) : '';
        $url  = isset($s['url'])  ? esc_url_raw($s['url'])     : '';
        if (!$url || !isset($icons[$icon])) continue;
        $out .= '<a class="mct-social" href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . $icons[$icon] . '</a>';
    }

    if ($out) echo '<div class="mct-footer">' . $out . '</div>';

    $layout = isset($opt['layout']) ? $opt['layout'] : 'full';

    if ($layout !== 'two_sidebar') {
        echo '</div>';
    }
}, 10);

function mct_first_active_sidebar_id()
{
    $map = wp_get_sidebars_widgets();
    if (!is_array($map)) return '';
    foreach ($map as $id => $widgets) {
        if ($id === 'wp_inactive_widgets') continue;
        if (!empty($widgets) && is_array($widgets)) return $id;
    }
    return '';
}

function mct_capture_sidebar_html()
{
    ob_start();
    get_sidebar();
    $html = trim(ob_get_clean());
    if ($html !== '') return $html;
    $sid = mct_first_active_sidebar_id();
    if (!$sid) return '';
    ob_start();
    echo '<div class="mct-widgets">';
    dynamic_sidebar($sid);
    echo '</div>';
    return trim(ob_get_clean());
}

add_action('woocommerce_checkout_after_order_review', function () {
    if (!mct_is_plugin_checkout_context()) return;
    $opt = mct_get_options();
    if (!isset($opt['layout']) || $opt['layout'] !== 'two_sidebar') return;

    echo '</div>';

    $html = mct_capture_sidebar_html();
    if ($html !== '') echo '<aside class="mct-card mct-theme-sidebar">' . $html . '</aside>';
}, 20);

add_action('woocommerce_after_checkout_form', function () {
    if (!mct_is_plugin_checkout_context()) return;
    echo '</div>';
}, 999);
