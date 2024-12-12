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

// Include email customizations
require_once plugin_dir_path( __FILE__ ) . 'includes/email-customizations.php';
// Include the plugin's functions.php file
require_once plugin_dir_path(__FILE__) . 'functions.php';


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

// Override WooCommerce order details customer template
add_filter('woocommerce_locate_template', 'woo_check_override_order_details_customer_template', 10, 3);
function woo_check_override_order_details_customer_template($template, $template_name, $template_path) {
    if ($template_name === 'order/order-details-customer.php') {
        $plugin_template = plugin_dir_path(__FILE__) . 'templates/order/order-details-customer.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}

// Override WooCommerce order received template
add_filter('template_include', 'woo_check_override_order_received_template', 99);

function woo_check_override_order_received_template($template) {
    if (is_wc_endpoint_url('order-received')) {
        $custom_template = plugin_dir_path(__FILE__) . 'templates/checkout/order-received.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
}

// Override WooCommerce email addresses template
add_filter('woocommerce_locate_template', 'woo_check_override_email_addresses_template', 10, 3);
function woo_check_override_email_addresses_template($template, $template_name, $template_path) {
    if ($template_name === 'emails/email-addresses.php') {
        $plugin_template = plugin_dir_path(__FILE__) . 'templates/emails/email-addresses.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}

add_filter('woocommerce_locate_template', 'woo_check_override_myaccount_templates', 10, 3);
function woo_check_override_myaccount_templates($template, $template_name, $template_path) {
    // Define the path to your plugin's templates folder
    $plugin_path = plugin_dir_path(__FILE__) . 'templates/';

    // Check if the template belongs to the myaccount folder and exists in your plugin
    if (strpos($template_name, 'myaccount/') === 0) {
        $custom_template = $plugin_path . $template_name;
        if (file_exists($custom_template)) {
            error_log('Using custom template: ' . $custom_template); // Log the custom template being used
            return $custom_template;
        }
    }

    error_log('Using default template: ' . $template); // Log the default template
    return $template;
}


add_action('wp_enqueue_scripts', 'woo_check_enqueue_assets');

function woo_check_enqueue_assets() {

    wp_enqueue_script(
        'woo-check-comunas-chile',
        plugin_dir_url(__FILE__) . 'comunas-chile.js',
        array(), // No dependencies
        '1.0',
        true
    );
    
    // Enqueue on the Order Received page
    if (is_wc_endpoint_url('order-received')) {
        wp_enqueue_style(
            'woo-check-order-received-style',
            plugin_dir_url(__FILE__) . 'order-received.css',
            array(),
            '1.0'
        );
    }

    // Enqueue on the Checkout page or Edit Address page
    if (is_checkout() || is_wc_endpoint_url('edit-address')) {
        wp_enqueue_style(
            'woo-check-style', 
            plugin_dir_url(__FILE__) . 'woo-check-style.css', 
            array(), 
            '1.0'
        );

        wp_enqueue_script(
            'woo-check-autocomplete',
            plugin_dir_url(__FILE__) . 'woo-check-autocomplete.js',
            array('jquery', 'jquery-ui-autocomplete'),
            '1.0',
            true
        );

        wp_localize_script('woo-check-autocomplete', 'wooCheckData', array(
            'json_url' => plugin_dir_url(__FILE__) . 'comunas-chile.json',
        ));
    }
}

// Customize Checkout Fields Order and Add Comuna Autocomplete Field
add_filter('woocommerce_checkout_fields', 'customize_checkout_fields_order');
function customize_checkout_fields_order($fields) {
    // Eliminar los campos de ciudad
    unset($fields['billing']['billing_city']);
    unset($fields['shipping']['shipping_city']);

    // Reorganizar campos de facturación
    $fields['billing'] = array(
        'billing_first_name' => $fields['billing']['billing_first_name'],
        'billing_last_name'  => $fields['billing']['billing_last_name'],
        'billing_address_1'  => $fields['billing']['billing_address_1'],
        'billing_address_2'  => $fields['billing']['billing_address_2'],
        'billing_comuna'     => array(
            'label'       => 'Comuna',
            'placeholder' => 'Enter comuna',
            'required'    => true,
            'class'       => array('form-row-wide'),
            'priority'    => 60, // Changed from 40
        ),
        'billing_state'      => $fields['billing']['billing_state'],
        'billing_phone'      => $fields['billing']['billing_phone'],
        'billing_email'      => $fields['billing']['billing_email'],
    );

    // Reorganizar campos de envío
$fields['shipping'] = array(
    'shipping_first_name' => $fields['shipping']['shipping_first_name'],
    'shipping_last_name'  => $fields['shipping']['shipping_last_name'],
    'shipping_address_1'  => $fields['shipping']['shipping_address_1'],
    'shipping_address_2'  => $fields['shipping']['shipping_address_2'],
    'shipping_comuna'     => array(
        'label'       => 'Comuna',
        'placeholder' => 'Enter comuna',
        'required'    => true,
        'class'       => array('form-row-wide'),
        'priority'    => 60, // Adjusted from 40
    ),
    'shipping_state'      => $fields['shipping']['shipping_state'],
    'shipping_phone'      => array(
        'type'       => 'tel',
        'label'      => __('Teléfono de quien recibe', 'woocommerce'),
        'required'   => false,
        'class'      => array('form-row-wide'),
        'priority'   => 70, // Added priority to control placement
    ),
);

    return $fields;
}

// Save Billing and Shipping Comuna Fields to Order Meta
add_action('woocommerce_checkout_update_order_meta', 'save_comuna_order_meta');
function save_comuna_order_meta($order_id) {
    if (!empty($_POST['billing_comuna'])) {
        error_log('Saving billing_comuna: ' . sanitize_text_field($_POST['billing_comuna']));
        update_post_meta($order_id, 'billing_comuna', sanitize_text_field($_POST['billing_comuna']));
    }
    if (!empty($_POST['shipping_comuna'])) {
        error_log('Saving shipping_comuna: ' . sanitize_text_field($_POST['shipping_comuna']));
        update_post_meta($order_id, 'shipping_comuna', sanitize_text_field($_POST['shipping_comuna']));
    }
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

// Add custom CSS for field layout in two columns
function customize_checkout_field_layout() {
    ?>
    <style>
        .woocommerce-billing-fields__field-wrapper .form-row-first,
        .woocommerce-shipping-fields__field-wrapper .form-row-first,
        .woocommerce-billing-fields__field-wrapper .form-row-last,
        .woocommerce-shipping-fields__field-wrapper .form-row-last {
            width: 48%;
            float: left;
            margin-right: 4%;
        }
        .woocommerce-billing-fields__field-wrapper .form-row-last,
        .woocommerce-shipping-fields__field-wrapper .form-row-last {
            margin-right: 0;
        }
    </style>
    <?php
}
add_action('wp_head', 'customize_checkout_field_layout');

// Debugging function to check if billing_comuna and shipping_comuna are saved correctly
add_action('woocommerce_checkout_update_order_meta', 'debug_comuna_meta_after_save', 20, 2);
function debug_comuna_meta_after_save($order_id, $posted) {
    $order = wc_get_order($order_id);
    $billing_comuna = $order->get_meta('billing_comuna');
    $shipping_comuna = $order->get_meta('shipping_comuna');
    error_log('After save, billing_comuna: ' . print_r($billing_comuna, true));
    error_log('After save, shipping_comuna: ' . print_r($shipping_comuna, true));
}

/* EDIT SHIPPING ADDRESS MY ACCOUNT */

// Add or modify the Comuna field in the Edit Address form
add_filter('woocommerce_default_address_fields', 'add_comuna_field_to_edit_address');
function add_comuna_field_to_edit_address($fields) {
    // Add 'comuna' field with adjusted priority
    $fields['comuna'] = array(
        'label'       => __('Comuna', 'woocommerce'),
        'placeholder' => __('Enter comuna', 'woocommerce'),
        'required'    => true,
        'class'       => array('form-row-wide'),
        'priority'    => 60, // Adjust priority to place it below address_2
    );

    // Adjust other field priorities
    $fields['address_1']['priority'] = 50; // Street Address 1
    $fields['address_2']['priority'] = 55; // Street Address 2 (optional)
    $fields['state']['priority'] = 70;     // Region/State field
    $fields['phone']['priority'] = 80;    // Phone field

    // Remove unwanted fields
    unset($fields['city']);
    unset($fields['postcode']);
    unset($fields['company']);

    return $fields;
}


// Save Comuna and Phone values when the Edit Address form is submitted
add_action('woocommerce_customer_save_address', 'save_comuna_and_phone_fields_in_edit_address', 10, 2);
function save_comuna_and_phone_fields_in_edit_address($user_id, $load_address) {
    if ($load_address === 'billing' && isset($_POST['billing_comuna'])) {
        update_user_meta($user_id, 'billing_comuna', sanitize_text_field($_POST['billing_comuna']));
    }
    if ($load_address === 'shipping' && isset($_POST['shipping_comuna'])) {
        update_user_meta($user_id, 'shipping_comuna', sanitize_text_field($_POST['shipping_comuna']));
    }
    if (isset($_POST['shipping_phone'])) {
        update_user_meta($user_id, 'shipping_phone', sanitize_text_field($_POST['shipping_phone']));
    }
}

/* ENQUEUEING COMUNAS */

