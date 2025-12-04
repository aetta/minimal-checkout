<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {
    add_options_page(
        esc_html__('Minimal Checkout', 'minimal-checkout'),
        esc_html__('Minimal Checkout', 'minimal-checkout'),
        'manage_options',
        MCT_SLUG,
        'mct_render_settings'
    );
});

add_action('admin_init', function () {
    register_setting(MCT_OPT, MCT_OPT, ['sanitize_callback' => 'mct_sanitize_options']);
    add_action('update_option_mct_page_id', function ($old, $new) {
        if (mct_wc_ready() && $new) update_option('woocommerce_checkout_page_id', intval($new));
    }, 10, 2);
});

function mct_sanitize_options($in)
{
    $in = is_array($in) ? $in : [];
    $d = mct_defaults();
    $out = $d;
    $allowed_layouts = ['full', 'two', 'two_sidebar'];
    $out['layout'] = isset($in['layout']) && in_array($in['layout'], $allowed_layouts, true) ? $in['layout'] : 'full';
    $out['enable_css'] = empty($in['enable_css']) ? 0 : 1;
    $out['strip_wc_styles'] = empty($in['strip_wc_styles']) ? 0 : 1;
    $out['show_coupon'] = empty($in['show_coupon']) ? 0 : 1;
    $out['show_notes'] = empty($in['show_notes']) ? 0 : 1;
    $out['ship_to_different'] = empty($in['ship_to_different']) ? 0 : 1;
    $out['hide_page_title'] = empty($in['hide_page_title']) ? 0 : 1;
    $out['show_thumbs'] = empty($in['show_thumbs']) ? 0 : 1;

    $out['fields'] = [];
    foreach ($d['fields'] as $k => $cfg) {
        $on = !empty($in['fields'][$k]['on']) ? 1 : 0;
        $label = isset($in['fields'][$k]['label']) ? sanitize_text_field($in['fields'][$k]['label']) : $cfg['label'];
        $out['fields'][$k] = ['on' => $on, 'label' => $label];
    }

    $icons = array_keys(mct_icons_map());
    $out['socials'] = [];
    if (isset($in['socials']) && is_array($in['socials'])) {
        for ($i = 0; $i < 5; $i++) {
            $row = isset($in['socials'][$i]) ? $in['socials'][$i] : ['icon' => 'facebook', 'url' => ''];
            $icon = in_array(sanitize_key($row['icon']), $icons, true) ? sanitize_key($row['icon']) : 'facebook';
            $url = esc_url_raw($row['url']);
            $out['socials'][] = ['icon' => $icon, 'url' => $url];
        }
    } else {
        $out['socials'] = $d['socials'];
    }
    return $out;
}

function mct_render_settings()
{
    if (!current_user_can('manage_options')) return;
    $opt = mct_get_options();
    $page_id = intval(get_option('mct_page_id'));
    $icons = array_keys(mct_icons_map());

    echo '<div class="wrap"><h1>' . esc_html__('Minimal Checkout', 'minimal-checkout') . '</h1>';
    echo '<h2 class="nav-tab-wrapper"><a class="nav-tab nav-tab-active" href="#mct-layout">' . esc_html__('Layout', 'minimal-checkout') . '</a><a class="nav-tab" href="#mct-fields">' . esc_html__('Fields', 'minimal-checkout') . '</a><a class="nav-tab" href="#mct-socials">' . esc_html__('Socials', 'minimal-checkout') . '</a></h2>';

    echo '<form method="post" action="options.php">';
    settings_fields(MCT_OPT);

    echo '<div id="mct-layout" class="mct-tab">';
    echo '<table class="form-table">';

    echo '<tr><th>' . esc_html__('Layout', 'minimal-checkout') . '</th><td>';
    echo '<label><input type="radio" name="' . esc_attr(MCT_OPT) . '[layout]" value="full" ' . checked($opt['layout'], 'full', false) . '> ' . esc_html__('One column', 'minimal-checkout') . '</label><br/>';
    echo '<label><input type="radio" name="' . esc_attr(MCT_OPT) . '[layout]" value="two" ' . checked($opt['layout'], 'two', false) . '> ' . esc_html__('Two columns', 'minimal-checkout') . '</label><br/>';
    echo '<label><input type="radio" name="' . esc_attr(MCT_OPT) . '[layout]" value="two_sidebar" ' . checked($opt['layout'], 'two_sidebar', false) . '> ' . esc_html__('Two columns + theme sidebar', 'minimal-checkout') . '</label>';
    echo '</td></tr>';

    echo '<tr><th>' . esc_html__('Enable plugin CSS', 'minimal-checkout') . '</th><td><label><input type="checkbox" name="' . esc_attr(MCT_OPT) . '[enable_css]" value="1" ' . checked($opt['enable_css'], 1, false) . '> ' . esc_html__('Enable', 'minimal-checkout') . '</label></td></tr>';

    echo '<tr><th>' . esc_html__('Strip WooCommerce styles', 'minimal-checkout') . '</th><td><label><input type="checkbox" name="' . esc_attr(MCT_OPT) . '[strip_wc_styles]" value="1" ' . checked($opt['strip_wc_styles'], 1, false) . '> ' . esc_html__('Disable WC default CSS', 'minimal-checkout') . '</label></td></tr>';

    echo '<tr><th>' . esc_html__('Show coupon box', 'minimal-checkout') . '</th><td><label><input type="checkbox" name="' . esc_attr(MCT_OPT) . '[show_coupon]" value="1" ' . checked($opt['show_coupon'], 1, false) . '></label></td></tr>';

    echo '<tr><th>' . esc_html__('Show order notes', 'minimal-checkout') . '</th><td><label><input type="checkbox" name="' . esc_attr(MCT_OPT) . '[show_notes]" value="1" ' . checked($opt['show_notes'], 1, false) . '></label></td></tr>';

    echo '<tr><th>' . esc_html__('Ship to a different address', 'minimal-checkout') . '</th><td><label><input type="checkbox" name="' . esc_attr(MCT_OPT) . '[ship_to_different]" value="1" ' . checked($opt['ship_to_different'], 1, false) . '></label></td></tr>';

    echo '<tr><th>' . esc_html__('Hide page title', 'minimal-checkout') . '</th><td><label><input type="checkbox" name="' . esc_attr(MCT_OPT) . '[hide_page_title]" value="1" ' . checked(!empty($opt['hide_page_title']), 1, false) . '></label></td></tr>';

    echo '<tr><th>' . esc_html__('Show product thumbnails', 'minimal-checkout') . '</th><td><label><input type="checkbox" name="' . esc_attr(MCT_OPT) . '[show_thumbs]" value="1" ' . checked(!empty($opt['show_thumbs']), 1, false) . '></label></td></tr>';

    echo '<tr><th>' . esc_html__('Checkout page', 'minimal-checkout') . '</th><td>';
    wp_dropdown_pages(['name' => 'mct_page_id', 'echo' => 1, 'show_option_none' => '— ' . esc_html__('Select', 'minimal-checkout') . ' —', 'option_none_value' => 0, 'selected' => $page_id]);
    echo '</td></tr>';

    echo '</table></div>';

    echo '<div id="mct-fields" class="mct-tab" style="display:none">';
    echo '<table class="widefat striped"><thead><tr><th>' . esc_html__('Field key', 'minimal-checkout') . '</th><th>' . esc_html__('Enabled', 'minimal-checkout') . '</th><th>' . esc_html__('Label', 'minimal-checkout') . '</th></tr></thead><tbody>';
    foreach ($opt['fields'] as $key => $cfg) {
        echo '<tr>';
        echo '<td><code>' . esc_html($key) . '</code></td>';
        echo '<td><input type="checkbox" name="' . esc_attr(MCT_OPT) . '[fields][' . esc_attr($key) . '][on]" value="1" ' . checked(!empty($cfg['on']), 1, false) . '></td>';
        echo '<td><input type="text" class="regular-text" name="' . esc_attr(MCT_OPT) . '[fields][' . esc_attr($key) . '][label]" value="' . esc_attr($cfg['label']) . '"></td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';

    echo '<div id="mct-socials" class="mct-tab" style="display:none">';
    echo '<table class="widefat striped"><thead><tr><th>#</th><th>' . esc_html__('Icon', 'minimal-checkout') . '</th><th>' . esc_html__('URL', 'minimal-checkout') . '</th></tr></thead><tbody>';
    for ($i = 0; $i < 5; $i++) {
        $row = isset($opt['socials'][$i]) ? $opt['socials'][$i] : ['icon' => 'facebook', 'url' => ''];
        echo '<tr>';
        echo '<td>' . esc_html($i + 1) . '</td>';
        echo '<td><select name="' . esc_attr(MCT_OPT) . '[socials][' . esc_attr($i) . '][icon]">';
        foreach ($icons as $ic) echo '<option value="' . esc_attr($ic) . '" ' . selected($row['icon'], $ic, false) . '>' . esc_html($ic) . '</option>';
        echo '</select></td>';
        echo '<td><input type="url" class="regular-text" name="' . esc_attr(MCT_OPT) . '[socials][' . esc_attr($i) . '][url]" value="' . esc_attr($row['url']) . '"></td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';

    submit_button(esc_html__('Save changes', 'minimal-checkout'));
    echo '</form>';
    echo '<style>.nav-tab-wrapper+.mct-tab{margin-top:12px}</style>';
    echo '<script>document.querySelectorAll(".nav-tab-wrapper .nav-tab").forEach(function(t,i){t.addEventListener("click",function(e){e.preventDefault();document.querySelectorAll(".nav-tab").forEach(function(x){x.classList.remove("nav-tab-active")});t.classList.add("nav-tab-active");document.querySelectorAll(".mct-tab").forEach(function(x,j){x.style.display=j===i?"block":"none"});});});</script>';
    echo '</div>';
}
