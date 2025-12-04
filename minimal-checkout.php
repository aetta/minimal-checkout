<?php

/**
 * Plugin Name: Minimal Checkout
 * Plugin URI: https://github.com/aetta/minimal-checkout
 * Description: Configurable WooCommerce checkout with clean layout, field toggles and optional product thumbnails.
 * Version: 2.2.0
 * Author: aetta
 * Author URI: https://profiles.wordpress.org/aetta
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.8
 * Tested up to: 6.9
 * Requires PHP: 7.4
 * Text Domain: minimal-checkout
 * Domain Path: /languages
 * WC requires at least: 7.0
 * WC tested up to: 10.3.6
 */
if (!defined('ABSPATH')) exit;

define('MCT_OPT', 'mct_options');
define('MCT_SLUG', 'mct-settings');
define('MCT_DIR', plugin_dir_path(__FILE__));
define('MCT_URL', plugin_dir_url(__FILE__));

function mct_load_textdomain()
{
    load_plugin_textdomain('minimal-checkout', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'mct_load_textdomain');

add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
});

require_once MCT_DIR . 'includes/common.php';

register_activation_hook(__FILE__, function () {
    if (!mct_wc_ready()) return;
    $opt = wp_parse_args(get_option(MCT_OPT, []), mct_defaults());
    update_option(MCT_OPT, $opt);
    $pid = get_option('mct_page_id');
    if ($pid && get_post_status($pid) === 'publish') {
        if (!get_option('woocommerce_checkout_page_id')) update_option('woocommerce_checkout_page_id', $pid);
        return;
    }
    $existing = get_page_by_title('Minimal Checkout');
    if ($existing && $existing->post_status === 'publish') $pid = $existing->ID;
    else {
        $pid = wp_insert_post([
            'post_title' => 'Minimal Checkout',
            'post_content' => '[minimal_checkout]',
            'post_status' => 'publish',
            'post_type' => 'page'
        ]);
    }
    if (!is_wp_error($pid)) {
        update_option('mct_page_id', intval($pid));
        if (!get_option('woocommerce_checkout_page_id')) update_option('woocommerce_checkout_page_id', intval($pid));
    }
});

if (is_admin()) {
    require_once MCT_DIR . 'includes/admin.php';
} else {
    require_once MCT_DIR . 'includes/frontend.php';
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $url = admin_url('options-general.php?page=' . MCT_SLUG);
    array_unshift($links, '<a href="' . esc_url($url) . '">' . esc_html__('Settings', 'minimal-checkout') . '</a>');
    return $links;
});
