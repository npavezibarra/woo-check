<?php
/*
Plugin Name: WooCommerce Checkout Customizer (woo-check)
Description: A plugin to customize the WooCommerce checkout page.
Version: 1.0
Author: Your Name
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Override WooCommerce checkout template
add_filter('woocommerce_locate_template', 'woo_check_override_checkout_template', 10, 3);
function woo_check_override_checkout_template($template, $template_name, $template_path) {
    if ($template_name === 'checkout/form-checkout.php') {
        $plugin_template = plugin_dir_path(__FILE__) . 'templates/checkout/form-checkout.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}

// Disable Order Notes field (removes the Additional Information section)
add_filter('woocommerce_enable_order_notes_field', '__return_false');

// Enqueue CSS and JavaScript only on the checkout page
function woo_check_enqueue_assets() {
    if (is_checkout()) {
        // Enqueue CSS file
        wp_enqueue_style(
            'woo-check-style', 
            plugin_dir_url(__FILE__) . 'woo-check-style.css', 
            array(), 
            '1.0'
        );

        // Enqueue JavaScript to update the label
        wp_enqueue_script(
            'woo-check-js',
            plugin_dir_url(__FILE__) . 'woo-check.js', // Add this file to your plugin root folder
            array('jquery'), 
            '1.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'woo_check_enqueue_assets');

// Customize field order, add Special Note field, and remove Postal Code
add_filter('woocommerce_checkout_fields', 'customize_checkout_fields_order');
function customize_checkout_fields_order($fields) {
    // Billing Fields Order
    $fields['billing']['billing_first_name']['priority'] = 10;
    $fields['billing']['billing_last_name']['priority'] = 20;
    $fields['billing']['billing_address_1']['priority'] = 30;
    $fields['billing']['billing_address_2']['priority'] = 40;
    $fields['billing']['billing_city']['priority'] = 50; // "Comuna" field
    $fields['billing']['billing_state']['priority'] = 60; // "Región" (State)
    $fields['billing']['billing_phone']['priority'] = 70;
    $fields['billing']['billing_email']['priority'] = 80;

    // Remove Postal Code
    unset($fields['billing']['billing_postcode']);
    unset($fields['shipping']['shipping_postcode']);

    // Add a custom "Special Note" field in Billing section
    $fields['billing']['billing_special_note'] = array(
        'type'        => 'textarea',
        'label'       => __('Special Note', 'woocommerce'),
        'placeholder' => __('Enter any special notes here', 'woocommerce'),
        'required'    => false,
        'priority'    => 90,
        'class'       => array('form-row-wide'),
    );

    // Shipping Fields Order to match billing
    $fields['shipping']['shipping_first_name']['priority'] = 10;
    $fields['shipping']['shipping_last_name']['priority'] = 20;
    $fields['shipping']['shipping_address_1']['priority'] = 30;
    $fields['shipping']['shipping_address_2']['priority'] = 40;
    $fields['shipping']['shipping_city']['priority'] = 50; // "Comuna" field
    $fields['shipping']['shipping_state']['priority'] = 60; // "Región" (State)
    
    // Add a phone field in the shipping section
    $fields['shipping']['shipping_phone'] = array(
        'type'     => 'tel',
        'label'    => __('Phone Number', 'woocommerce'),
        'required' => false,
        'priority' => 70,
        'class'    => array('form-row-wide'),
    );

    return $fields;
}

// Remove Company Name field
add_filter('woocommerce_checkout_fields', 'remove_company_name_field');
function remove_company_name_field($fields) {
    // Remove the Company Name field from both billing and shipping sections
    unset($fields['billing']['billing_company']);
    unset($fields['shipping']['shipping_company']);
    return $fields;
}

// Change "Town/City" label to "Comuna" using a high-priority filter
add_filter('woocommerce_checkout_fields', 'customize_city_label', 999);
function customize_city_label($fields) {
    // Change the label for the Town/City field to "Comuna" in billing and shipping sections
    $fields['billing']['billing_city']['label'] = __('Comuna', 'woocommerce');
    $fields['shipping']['shipping_city']['label'] = __('Comuna', 'woocommerce');
    return $fields;
}
