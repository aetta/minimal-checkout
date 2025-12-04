<?php
if (!defined('ABSPATH')) exit;

function mct_wc_ready()
{
    return function_exists('WC') && class_exists('WooCommerce');
}

function mct_defaults()
{
    return [
        'layout' => 'full',
        'enable_css' => 1,
        'strip_wc_styles' => 1,
        'show_coupon' => 0,
        'show_notes' => 0,
        'ship_to_different' => 0,
        'hide_page_title' => 0,
        'show_thumbs' => 1,
        'fields' => [
            'billing_first_name' => ['on' => 1, 'label' => 'First name'],
            'billing_last_name'  => ['on' => 1, 'label' => 'Last name'],
            'billing_phone'      => ['on' => 1, 'label' => 'Phone'],
            'billing_email'      => ['on' => 0, 'label' => 'Email'],
            'billing_country'    => ['on' => 0, 'label' => 'Country'],
            'billing_state'      => ['on' => 0, 'label' => 'State'],
            'billing_city'       => ['on' => 0, 'label' => 'City'],
            'billing_postcode'   => ['on' => 0, 'label' => 'Postcode'],
            'billing_address_1'  => ['on' => 0, 'label' => 'Address'],
            'billing_address_2'  => ['on' => 0, 'label' => 'Address 2'],
            'billing_company'    => ['on' => 0, 'label' => 'Company'],
            'shipping_first_name' => ['on' => 0, 'label' => 'First name'],
            'shipping_last_name' => ['on' => 0, 'label' => 'Last name'],
            'shipping_country'   => ['on' => 0, 'label' => 'Country'],
            'shipping_state'     => ['on' => 0, 'label' => 'State'],
            'shipping_city'      => ['on' => 0, 'label' => 'City'],
            'shipping_postcode'  => ['on' => 0, 'label' => 'Postcode'],
            'shipping_address_1' => ['on' => 0, 'label' => 'Address'],
            'shipping_address_2' => ['on' => 0, 'label' => 'Address 2'],
            'shipping_company'   => ['on' => 0, 'label' => 'Company'],
        ],
        'socials' => [
            ['icon' => 'facebook', 'url' => ''],
            ['icon' => 'instagram', 'url' => ''],
            ['icon' => 'whatsapp', 'url' => ''],
            ['icon' => 'youtube', 'url' => ''],
            ['icon' => 'tiktok', 'url' => ''],
        ],
    ];
}

function mct_get_options()
{
    return wp_parse_args(get_option(MCT_OPT, []), mct_defaults());
}

function mct_allowed_keys()
{
    $opt = mct_get_options();
    $allowed = [];
    foreach ($opt['fields'] as $key => $cfg) {
        if (!empty($cfg['on'])) $allowed[$key] = true;
    }
    return $allowed;
}

function mct_icons_map()
{
    return [
        'facebook' => '<svg viewBox="0 0 24 24"><path d="M13 22v-9h3l1-4h-4V7c0-1.1.9-2 2-2h2V1h-3c-2.8 0-5 2.2-5 5v3H6v4h3v9h4z"/></svg>',
        'instagram' => '<svg viewBox="0 0 24 24"><path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5zm5 5a5 5 0 1 0 .001 10.001A5 5 0 0 0 12 7zm6.5-.9a1.1 1.1 0 1 0 0 2.2 1.1 1.1 0 0 0 0-2.2zM12 9a3 3 0 1 1 0 6 3 3 0 0 1 0-6z"/></svg>',
        'whatsapp' => '<svg viewBox="0 0 24 24"><path d="M17 14.5c-.3-.2-1.7-.9-2-.9s-.5.1-.7.4c-.2.2-.8.9-.9 1-.2.1-.3.2-.6.1s-1.2-.4-2.3-1.5-1.5-2-1.6-2.3.1-.4.1-.6c0-.2 0-.3-.1-.5-.2-.3-.9-1.7-1.1-2S6.5 6 6.2 6H6c-.2 0-.5.1-.7.3s-.7.7-.7 1.7 1 .9 1.1 1.1c.1.2 1.5 2.9 3.6 4.1 2.1 1.3 2.1.9 2.5.9.4 0 1.3-.5 1.5-1 .2-.5.2-.9.1-1 .1-.1.3-.1.5 0 .2 0 1.6.8 1.9.9.3.1.5.1.7 0 .2-.1 1-.6 1.1-.8.1-.1.1-.3 0-.4z"/><path d="M20.5 3.5A11 11 0 1 0 3 20.9L2 22l3.2-.8A11 11 0 1 0 20.5 3.5zM13 21A9 9 0 1 1 22 12 9 9 0 0 1 13 21z"/></svg>',
        'youtube' => '<svg viewBox="0 0 24 24"><path d="M23 8s0-3-3-3H4C1 5 1 8 1 8v4s0 3 3 3h16c3 0 3-3 3-3V8zm-13 6V8l6 3-6 3z"/></svg>',
        'tiktok' => '<svg viewBox="0 0 24 24"><path d="M14 3h3a5 5 0 0 0 5 5v3a8 8 0 0 1-5-2v8a6 6 0 1 1-6-6h1v3a3 3 0 1 0 3 3V3z"/></svg>',
    ];
}

function mct_is_checkout_page()
{
    $pid = get_option('mct_page_id');
    return $pid && is_page($pid);
}
