<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php
// Cargar la cabecera del tema Twenty Twenty-Four (incluye la barra de navegación)
echo do_blocks('<!-- wp:template-part {"slug":"header","area":"header","tagName":"header"} /-->');

// Obtener los detalles de la orden
$order_id = wc_get_order_id_by_order_key($_GET['key']);
$order = wc_get_order($order_id);
?>

<div class="order-received-page">
<h3>
    <?php
    $customer_first_name = $order ? $order->get_billing_first_name() : '';
    echo 'Gracias por tu compra' . (!empty($customer_first_name) ? ', ' . esc_html($customer_first_name) : '') . '!';
    ?>
</h3>

<?php if ($order) : ?>
    <div id="order-information">
        <p class="titulo-seccion">Número de orden: <?php echo esc_html($order->get_id()); ?></p>

        <ul class="order-products">
            <?php foreach ($order->get_items() as $item_id => $item) : ?>
                <?php
                $product = $item->get_product();
                $product_name = $item->get_name();
                $product_quantity = $item->get_quantity();
                $product_subtotal = $order->get_formatted_line_subtotal($item);
                $product_image = $product ? $product->get_image('thumbnail') : '<div class="placeholder-image"></div>';

                // Check if the product is linked to a LearnDash course
                $course_meta = get_post_meta($product->get_id(), '_related_course', true);

                // Handle serialized course_meta
                if (is_serialized($course_meta)) {
                    $course_meta = unserialize($course_meta);
                }

                // Extract the course ID
                $course_id = is_array($course_meta) && isset($course_meta[0]) ? $course_meta[0] : $course_meta;

                // Generate course URL if a valid course ID exists
                $course_url = !empty($course_id) && is_numeric($course_id) ? get_permalink($course_id) : null;
                ?>
                <li class="order-product-item">
                    <div class="product-flex-container">
                        <div class="product-image"><?php echo $product_image; ?></div>
                        <div class="product-details">
                            <?php if ($course_url) : ?>
                                <!-- Show only the product name without quantity for courses -->
                                <span><?php echo esc_html($product_name); ?></span>
                                <br><a href="<?php echo esc_url($course_url); ?>" class="button" style="display: inline-block; margin-top: 10px; padding: 5px 10px; background-color: black; color: #fff; text-decoration: none; border-radius: 3px; font-size: 12px;">Ir al Curso</a>
                            <?php else : ?>
                                <!-- Show product name with quantity for non-courses -->
                                <span><?php echo esc_html($product_quantity); ?> - <?php echo esc_html($product_name); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-total"><?php echo wp_kses_post($product_subtotal); ?></div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php else : ?>
    <h2>Orden no encontrada.</h2>
<?php endif; ?>

</div>

<?php if ($order): ?>
<?php
// Verificar si hay productos físicos en el pedido
$has_physical_products = false;
foreach ($order->get_items() as $item_id => $item) {
    $product = $item->get_product();
    if ($product && !$product->is_virtual()) {
        $has_physical_products = true;
        break;
    }
}

// Determinar clase adicional para el contenedor de Billing
$billing_class = $has_physical_products ? '' : ' only-billing';
?>

<section class="woocommerce-customer-details" id="info-clientes">
    <h3>Detalles del cliente</h3>
    <div class="woocommerce-columns woocommerce-columns--2 woocommerce-columns--addresses col2-set addresses">
        <!-- Billing Address -->
        <div class="woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1<?php echo esc_attr($billing_class); ?>">
            <h4><?php esc_html_e('Dirección de facturación', 'woocommerce'); ?></h4>
            <address>
                <?php if ($order->get_billing_first_name() || $order->get_billing_last_name()) : ?>
                    <?php echo esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()); ?><br>
                <?php endif; ?>
                <?php if ($order->get_billing_address_1()) : ?>
                    <?php echo esc_html($order->get_billing_address_1()); ?>
                    <?php if ($order->get_billing_address_2()) : ?>
                        , <?php echo esc_html($order->get_billing_address_2()); ?>
                    <?php endif; ?><br>
                <?php endif; ?>
                <?php 
                $billing_comuna = get_post_meta($order->get_id(), 'billing_comuna', true);
                if (!empty($billing_comuna)) : ?>
                    <?php echo esc_html($billing_comuna); ?><br>
                <?php endif; ?>
                <?php if ($order->get_billing_state()) : ?>
                    <?php echo esc_html($order->get_billing_state()); ?><br>
                <?php endif; ?>
                <?php if ($order->get_billing_phone()) : ?>
                    <?php echo wc_make_phone_clickable($order->get_billing_phone()); ?><br>
                <?php endif; ?>
                <?php if ($order->get_billing_email()) : ?>
                    <?php echo esc_html($order->get_billing_email()); ?>
                <?php endif; ?>
            </address>
        </div>

        <!-- Shipping Address -->
        <?php if ($has_physical_products): ?>
        <div class="woocommerce-column woocommerce-column--2 woocommerce-column--shipping-address col-2">
            <h4><?php esc_html_e('Dirección de envío', 'woocommerce'); ?></h4>
            <address>
                <?php if ($order->get_shipping_first_name() || $order->get_shipping_last_name()) : ?>
                    <?php echo esc_html($order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name()); ?><br>
                <?php endif; ?>
                <?php if ($order->get_shipping_address_1()) : ?>
                    <?php echo esc_html($order->get_shipping_address_1()); ?>
                    <?php if ($order->get_shipping_address_2()) : ?>
                        , <?php echo esc_html($order->get_shipping_address_2()); ?>
                    <?php endif; ?><br>
                <?php endif; ?>
                <?php 
                $shipping_comuna = get_post_meta($order->get_id(), 'shipping_comuna', true);
                if (!empty($shipping_comuna)) : ?>
                    <?php echo esc_html($shipping_comuna); ?><br>
                <?php endif; ?>
                <?php if ($order->get_shipping_state()) : ?>
                    <?php echo esc_html($order->get_shipping_state()); ?><br>
                <?php endif; ?>
                <?php if ($order->get_shipping_phone()) : ?>
                    <?php echo wc_make_phone_clickable($order->get_shipping_phone()); ?><br>
                <?php endif; ?>
            </address>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php endif; ?>

<?php


wp_footer();
?>
</body>
</html>
