jQuery(document).ready(function($) {
    // Change the label text for the City field in both billing and shipping sections
    $('label[for="billing_city"]').text('Comuna *');
    $('label[for="shipping_city"]').text('Comuna *');
});