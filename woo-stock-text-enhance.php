<?php

/**
 *  
 * Plugin Name: Woo Stock Text Enhance
 * Plugin URI:  https://github.com/shahjalal132/woo-stock-text-enhance
 * Author:      Sujon
 * Author URI:  https://github.com/mtmsujon
 * Description: Woo Stock Text Enhance
 * Version:     1.0.0
 * text-domain: wste
 * Domain Path: /languages
 * 
 */

defined( "ABSPATH" ) || exit( "Direct Access Not Allowed" );

add_action( 'woocommerce_before_add_to_cart_form', 'custom_display_stock_status', 20 );
function custom_display_stock_status() {
    global $product;

    // Only for simple products
    if ( $product->is_type( 'simple' ) ) {

        // Get stock quantity
        $stock_quantity = $product->get_stock_quantity();
        $stock_quantity = $stock_quantity > 0 ? $stock_quantity : '';

        // Get availability status
        $availability       = $product->get_availability();
        $availability_text  = !empty( $availability['availability'] ) ? $availability['availability'] : '';
        $availability_class = !empty( $availability['class'] ) ? $availability['class'] : '';

        // Output quantity + status
        if ( $availability_text ) {
            echo '<div class="show-stock-quantity">';
            echo '<p class="stock ' . esc_attr( $availability_class ) . '">' . esc_html( $stock_quantity ) . ' ' . esc_html( $availability_text ) . '</p>';
            echo '</div><br>';
        }
    }
}

add_filter( 'woocommerce_get_availability_text', 'custom_woocommerce_get_availability_text', 10, 2 );
function custom_woocommerce_get_availability_text( $availability, $product ) {
    if ( $product->is_in_stock() ) {
        $availability = __( 'In Stock', 'woocommerce' );
    } elseif ( !$product->is_in_stock() && $product->is_on_backorder( 1 ) ) {
        $availability = __( 'Available to Order', 'woocommerce' );
    } elseif ( !$product->is_in_stock() ) {
        $availability = __( 'Coming Soon', 'woocommerce' );
    }

    return $availability;
}