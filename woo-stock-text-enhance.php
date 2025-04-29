<?php
/**
 * Plugin Name: Woo Stock Text Enhance
 * Plugin URI:  https://github.com/shahjalal132/woo-stock-text-enhance
 * Author:      Sujon
 * Author URI:  https://github.com/mtmsujon
 * Description: Enhances WooCommerce stock messages and shows custom labels for simple and variable products.
 * Version:     1.0.0
 * Text Domain: wste
 * Domain Path: /languages
 */

defined( "ABSPATH" ) || exit( "Direct Access Not Allowed" );

// Define plugin paths
if ( !defined( 'PLUGIN_DIR_BASE_PATH' ) ) {
    define( 'PLUGIN_DIR_BASE_PATH', plugin_dir_path( __FILE__ ) );
}
if ( !defined( 'PLUGIN_DIR_BASE_URL' ) ) {
    define( 'PLUGIN_DIR_BASE_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * For simple products: Show custom stock status.
 */
add_action( 'woocommerce_before_add_to_cart_form', 'custom_display_stock_status', 20 );
function custom_display_stock_status() {
    global $product;

    if ( $product->is_type( 'simple' ) ) {

        $stock          = null;
        $stock_quantity = $product->get_stock_quantity();

        $availability       = $product->get_availability();
        $availability_class = !empty( $availability['class'] ) ? $availability['class'] : '';
        $availability_text  = '';

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
        }

        if ( 'in-stock' === $availability_class ) {
            $stock = $stock_quantity;
        }

        if ( $availability_text ) {
            echo '<div class="show-stock-quantity">';
            echo '<p class="stock-label ' . esc_attr( $availability_class ) . '">' . esc_html( $stock ) . ' ' . esc_html( $availability_text ) . '</p>';
            echo '</div><br>';
        }
    }
}

/**
 * For variable products: Output variation stock info as JSON and show container.
 */
add_action( 'woocommerce_before_add_to_cart_form', 'wste_variable_product_stock_info', 25 );
function wste_variable_product_stock_info() {
    global $product;

    if ( $product->is_type( 'variable' ) ) {
        $variations     = $product->get_available_variations();
        $variation_data = [];

        foreach ( $variations as $variation ) {
            $stock_qty       = $variation['max_qty'] ?? 0;
            $is_in_stock     = $variation['is_in_stock'];
            $is_on_backorder = $variation['backorders_allowed'] && !$variation['is_in_stock'];

            if ( $is_in_stock ) {
                $label = 'In Stock';
            } elseif ( $is_on_backorder ) {
                $label = 'Available to Order';
            } else {
                $label = 'Coming Soon';
            }

            $variation_data[$variation['variation_id']] = [
                'stock_qty' => $stock_qty,
                'label'     => $label,
            ];
        }

        echo '<div id="wste-variation-stock-display" class="show-stock-quantity"></div>';
        echo '<script>
            window.wste_variation_stock_data = ' . json_encode( $variation_data ) . ';
        </script>';
    }
}

/**
 * Dynamic JS to show variation stock on selection.
 */
add_action( 'wp_footer', 'wste_inline_script_for_variation_stock', 100 );
function wste_inline_script_for_variation_stock() {
    if ( !is_product() )
        return;
    ?>
    <script>
        (function ($) {
            $(document).ready(function () {
                $('form.variations_form').on('show_variation', function (event, variation) {
                    let data = window.wste_variation_stock_data || {};
                    let stockBox = $('#wste-variation-stock-display');
                    let id = variation.variation_id;

                    if (data[id]) {
                        let qty = data[id].stock_qty;
                        let label = data[id].label;
                        let qtyStatus = `${qty} ${label}`;
                        stockBox.hide();
                        stockBox.html(`<p class="stock-label">${qty} ${label}</p>`);
                        $(".woocommerce-variation-availability p").html(qtyStatus);
                    } else {
                        stockBox.empty();
                    }
                });

                $('form.variations_form').on('hide_variation', function () {
                    $('#wste-variation-stock-display').empty();
                });
            });
        })(jQuery);
    </script>
    <?php
}

/**
 * Custom filter to override default WooCommerce availability text.
 */
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

// Optional: Include metabox file (if needed)
if ( file_exists( PLUGIN_DIR_BASE_PATH . 'includes/class-wste-metabox.php' ) ) {
    require_once PLUGIN_DIR_BASE_PATH . 'includes/class-wste-metabox.php';
}
