<?php
/**
 * Plugin Name: Delivery Slot Manager for WooCommerce
 * Description: Adds a delivery date & time picker before "Add to Cart" in WooCommerce and displays selected data in the cart.
 * Version: 1.0.3
 * Author: Parmanand Jha
 * License: GPL2
 * Requires Plugins: woocommerce
 * Contributors: jhaparmanand
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// ✅ Check if WooCommerce is active
function dsmwc_check_woocommerce_dependency() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'dsmwc_woocommerce_missing_notice');
        deactivate_plugins(plugin_basename(__FILE__));
    }
}
add_action('admin_init', 'dsmwc_check_woocommerce_dependency');

function dsmwc_woocommerce_missing_notice() {
    echo '<div class="error"><p><strong>Delivery Slot for WooCommerce</strong> requires WooCommerce to be installed and activated.</p></div>';
}

// ✅ Add Date & Time Picker before Add to Cart Button
function dsmwc_add_delivery_date_time_picker() {
    global $product;
    if ($product->is_type('simple')) {
        echo '<div class="delivery-slot">';
        echo '<label for="delivery_date">Select Delivery Date:</label>';
        echo '<input type="text" id="delivery_date" name="delivery_date" required readonly>';

        echo '<label for="delivery_time">Select Delivery Time:</label>';
        echo '<select id="delivery_time" name="delivery_time" required>';
        echo '<option value="">-- Select Time Slot --</option>';
        echo '<option value="07AM - 02PM">07AM - 02PM</option>';
        echo '<option value="02PM - 09PM">02PM - 09PM</option>';
        echo '</select>';

        // Add Nonce Field for security
        wp_nonce_field('dsmwc_delivery_slot_nonce_action', 'dsmwc_delivery_slot_nonce');

        echo '</div>';
    }
}
add_action('woocommerce_before_add_to_cart_button', 'dsmwc_add_delivery_date_time_picker');

// ✅ Save Delivery Slot to Cart Item Data with Nonce Verification
function dsmwc_save_delivery_slot_cart_item_data($cart_item_data, $product_id, $variation_id) {
    // Validate and sanitize nonce
    $nonce = isset($_POST['dsmwc_delivery_slot_nonce']) ? sanitize_text_field(wp_unslash($_POST['dsmwc_delivery_slot_nonce'])) : '';
    if (!wp_verify_nonce($nonce, 'dsmwc_delivery_slot_nonce_action')) {
        return $cart_item_data; // Security check failed
    }

    // Validate and sanitize delivery slot data
    if (!empty($_POST['delivery_date'])) {
        $cart_item_data['delivery_date'] = sanitize_text_field(wp_unslash($_POST['delivery_date']));
    }
    if (!empty($_POST['delivery_time'])) {
        $cart_item_data['delivery_time'] = sanitize_text_field(wp_unslash($_POST['delivery_time']));
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'dsmwc_save_delivery_slot_cart_item_data', 10, 3);

// ✅ Display Delivery Slot in Cart
function dsmwc_display_delivery_slot_cart_item($item_data, $cart_item) {
    if (!empty($cart_item['delivery_date'])) {
        $item_data[] = array(
            'name'  => 'Delivery Date',
            'value' => esc_html($cart_item['delivery_date'])
        );
    }
    if (!empty($cart_item['delivery_time'])) {
        $item_data[] = array(
            'name'  => 'Delivery Time',
            'value' => esc_html($cart_item['delivery_time'])
        );
    }
    return $item_data;
}
add_filter('woocommerce_get_item_data', 'dsmwc_display_delivery_slot_cart_item', 10, 2);

// ✅ Ensure Delivery Slot Appears in Cart Page
function dsmwc_display_delivery_slot_in_cart($item_name, $cart_item, $cart_item_key) {
    if (isset($cart_item['delivery_date']) && isset($cart_item['delivery_time'])) {
        $item_name .= '<p><strong>Delivery Date:</strong> ' . esc_html($cart_item['delivery_date']) . '</p>';
        $item_name .= '<p><strong>Delivery Time:</strong> ' . esc_html($cart_item['delivery_time']) . '</p>';
    }
    return $item_name;
}
add_filter('woocommerce_cart_item_name', 'dsmwc_display_delivery_slot_in_cart', 10, 3);

// ✅ Properly Enqueue CSS for Styling
function dsmwc_enqueue_delivery_slot_styles() {
    wp_enqueue_style('dsmwc-delivery-slot-css', plugin_dir_url(__FILE__) . 'assets/css/delivery-slot.css', array(), '1.0.0', 'all');
    wp_enqueue_style('dsmwc-jquery-ui-css', plugin_dir_url(__FILE__) . 'assets/css/jquery-ui.css', array(), '1.12.1', 'all');
}
add_action('wp_enqueue_scripts', 'dsmwc_enqueue_delivery_slot_styles');

// ✅ Enqueue jQuery UI Datepicker
function dsmwc_enqueue_datepicker_scripts() {
    if (is_product()) {
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('dsmwc-datepicker-script', plugin_dir_url(__FILE__) . 'assets/js/datepicker.js', array('jquery', 'jquery-ui-datepicker'), '1.0', true);
    }
}
add_action('wp_enqueue_scripts', 'dsmwc_enqueue_datepicker_scripts');
