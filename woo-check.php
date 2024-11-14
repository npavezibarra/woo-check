<?php
/*
Plugin Name: WooCommerce Checkout Customizer (woo-check)
Description: A plugin to customize the WooCommerce checkout page with an autocomplete field for Comuna.
Version: 1.0
Author: Your Name
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Override WooCommerce checkout template if needed
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

        // Enqueue JavaScript for autocomplete functionality
        wp_enqueue_script(
            'woo-check-autocomplete',
            plugin_dir_url(__FILE__) . 'woo-check-autocomplete.js',
            array('jquery', 'jquery-ui-autocomplete'), // Dependencies
            '1.0',
            true
        );

        // Pass URL for the JSON file to JavaScript
        wp_localize_script('woo-check-autocomplete', 'wooCheckData', array(
            'json_url' => plugin_dir_url(__FILE__) . 'comunas-chile.json',
        ));
    }
}
add_action('wp_enqueue_scripts', 'woo_check_enqueue_assets');

// Customize Checkout Fields Order and Add Comuna Autocomplete Field
add_filter('woocommerce_checkout_fields', 'customize_checkout_fields_order');
function customize_checkout_fields_order($fields) {

    // Define billing fields
    $fields['billing'] = array(
        'billing_first_name' => $fields['billing']['billing_first_name'],
        'billing_last_name' => $fields['billing']['billing_last_name'],
        'billing_address_1' => $fields['billing']['billing_address_1'],
        'billing_address_2' => $fields['billing']['billing_address_2'],
        'billing_comuna' => array(
            'label'       => 'Comuna',
            'placeholder' => 'Enter comuna',
            'required'    => true,
            'class'       => array('form-row-wide'),
            'priority'    => 40,
        ),
        'billing_state' => $fields['billing']['billing_state'],
        'billing_phone' => $fields['billing']['billing_phone'],
        'billing_email' => $fields['billing']['billing_email'],
    );

    // Define shipping fields
    $fields['shipping'] = array(
        'shipping_first_name' => $fields['shipping']['shipping_first_name'],
        'shipping_last_name' => $fields['shipping']['shipping_last_name'],
        'shipping_address_1' => $fields['shipping']['shipping_address_1'],
        'shipping_address_2' => $fields['shipping']['shipping_address_2'],
        'shipping_comuna' => array(
            'label'       => 'Comuna',
            'placeholder' => 'Enter comuna',
            'required'    => true,
            'class'       => array('form-row-wide'),
            'priority'    => 40,
        ),
        'shipping_state' => $fields['shipping']['shipping_state'],
        'shipping_phone' => array(
            'type'     => 'tel',
            'label'    => __('Teléfono de quien recibe', 'woocommerce'),
            'required' => false,
            'class'    => array('form-row-wide'),
        ),
    );

    return $fields;
}

// Remove Company and Postal Code Fields
add_filter('woocommerce_checkout_fields', 'remove_unwanted_checkout_fields');
function remove_unwanted_checkout_fields($fields) {
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['shipping']['shipping_company']);
    unset($fields['shipping']['shipping_postcode']);
    return $fields;
}
