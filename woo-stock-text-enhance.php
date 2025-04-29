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

if ( !defined( 'PLUGIN_DIR_BASE_PATH' ) ) {
    define( 'PLUGIN_DIR_BASE_PATH', plugin_dir_path( __FILE__ ) );
}

if ( !defined( 'PLUGIN_DIR_BASE_URL' ) ) {
    define( 'PLUGIN_DIR_BASE_URL', plugin_dir_url( __FILE__ ) );
}

add_action( 'woocommerce_before_add_to_cart_form', 'custom_display_stock_status', 20 );
function custom_display_stock_status() {
    global $product;

    // Only for simple products
    if ( $product->is_type( 'simple' ) ) {

        // Get stock quantity
        $stock          = null;
        $stock_quantity = $product->get_stock_quantity();

        // Get availability status
        $availability       = $product->get_availability();
        $availability_text  = '';
        $availability_class = !empty( $availability['class'] ) ? $availability['class'] : '';

        switch ($availability_class) {
            case 'in-stock':
                $availability_text = 'In Stock';
                break;
            case 'out-of-stock':
                $availability_text = 'Coming Soon';
                break;
            case 'available-on-backorder':
                $availability_text = 'Available to Order';
                break;
            default:
                # code...
                break;
        }

        if ( 'in-stock' === $availability_class ) {
            $stock = $stock_quantity;
        }

        // file_put_contents( PLUGIN_DIR_BASE_PATH . 'logs/logs.txt', "Availability: " . json_encode( $availability ) . "\n", FILE_APPEND );

        // Output quantity + status
        if ( $availability_text ) {
            echo '<div class="show-stock-quantity">';
            echo '<p class="stock-label ' . esc_attr( $availability_class ) . '">' . esc_html( $stock ) . ' ' . esc_html( $availability_text ) . '</p>';
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

// enqueue assets
function wste_enqueue_assets( $page_now ) {

    // file_put_contents( PLUGIN_DIR_BASE_PATH . 'logs/logs', $page_now );
    if ( 'post.php' == $page_now ) {
        // enqueue admin js
        wp_enqueue_script( "admin-script", PLUGIN_DIR_BASE_URL . "assets/admin/js/admin-scripts.js", [ 'jquery' ], time(), true );
        // localize script to pass ajax url
        wp_localize_script( 'admin-script', 'wste_ajax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    }
}
add_action( 'admin_enqueue_scripts', 'wste_enqueue_assets' );


// require metabox file.
require_once PLUGIN_DIR_BASE_PATH . 'includes/class-wste-metabox.php';