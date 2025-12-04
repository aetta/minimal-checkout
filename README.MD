# Minimal Checkout

Configurable WooCommerce checkout with a clean, responsive layout, field toggles, optional product thumbnails, and a smarter coupon placement. Lightweight, no template overrides, theme-friendly.

## Features

- Layouts: **One column**, **Two columns**, **Two columns + theme sidebar**
- Optional product thumbnails in Order Review
- Coupon form inside Order Review (avoids theme duplicates)
- Toggle Woo default CSS on/off
- Enable/disable and relabel billing/shipping fields
- Optional “Ship to a different address” and “Order notes”
- Social icons (up to 5): Facebook, Instagram, WhatsApp, YouTube, TikTok
- Option to hide the page title on the checkout page

## Requirements

- WordPress 5.8+ (tested up to **6.9**)
- WooCommerce 7.0+ (tested up to **10.3.6**)
- PHP 7.4+ (tested on **8.2**)

## Compatibility

- Declares compatibility with:
  - HPOS (custom order tables)
  - Cart/Checkout Blocks
- CSS is scoped under `.mct-scope`. No template overrides.

## Installation

1. Upload and activate the plugin.
2. A page named **Minimal Checkout** is created automatically with the shortcode `[minimal_checkout]`.

## Configuration

Go to **Settings → Minimal Checkout**:

- **Layout:** One / Two / Two + Sidebar
- **Styles:** Enable plugin CSS; Strip Woo default CSS
- **Options:** Show coupon; Show order notes; Ship to different address; Hide page title; Show product thumbnails
- **Fields:** Enable/disable and relabel billing/shipping fields
- **Socials:** Up to 5 icons with URLs

## How it works

- Adds a wrapper `.mct-scope` and a `mct-checkout` body class to scope styles.
- Uses WooCommerce filters to whitelist fields and apply custom labels.
- Renders thumbnails via the `woocommerce_cart_item_name` filter.
- Moves the coupon into Order Review and hides theme-injected duplicates with CSS.

## Localization

- Text domain: `minimal-checkout`
- POT template: `languages/minimal-checkout.pot`

## Development

minimal-checkout/
├─ minimal-checkout.php
├─ uninstall.php
├─ includes/
│ ├─ admin.php
│ ├─ common.php
│ └─ frontend.php
├─ assets/
│ └─ minimal-checkout.css
├─ languages/
│ └─ minimal-checkout.pot
├─ README.md
└─ LICENSE

## Changelog

**2.2.0**

- Public release: One/Two/Two + Sidebar layouts
- Thumbnails in Order Review
- Coupon inside Order Review
- Field toggles and relabeling
- Optional stripping of Woo default CSS

## License

GPLv2 or later.
