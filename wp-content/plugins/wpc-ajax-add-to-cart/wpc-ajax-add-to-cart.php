<?php
/**
 * Plugin Name: WPC AJAX Add to Cart for WooCommerce
 * Plugin URI: https://wpclever.net/
 * Description: AJAX add to cart for WooCommerce products.
 * Version: 1.4.0
 * Author: WPClever
 * Author URI: https://wpclever.net
 * Text Domain: wpc-ajax-add-to-cart
 * Domain Path: /languages/
 * Requires at least: 4.0
 * Tested up to: 5.8
 * WC requires at least: 3.0
 * WC tested up to: 5.8
 */

defined( 'ABSPATH' ) || exit;

! defined( 'WOOAA_VERSION' ) && define( 'WOOAA_VERSION', '1.4.0' );
! defined( 'WOOAA_URI' ) && define( 'WOOAA_URI', plugin_dir_url( __FILE__ ) );
! defined( 'WOOAA_DISCUSSION' ) && define( 'WOOAA_DISCUSSION', 'https://wordpress.org/support/plugin/wpc-ajax-add-to-cart/' );
! defined( 'WPC_URI' ) && define( 'WPC_URI', WOOAA_URI );

include 'includes/wpc-dashboard.php';
include 'includes/wpc-menu.php';
include 'includes/wpc-kit.php';
include 'includes/wpc-notice.php';

if ( ! class_exists( 'WPCleverWooaa' ) ) {
	class WPCleverWooaa {
		protected static $instance;

		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
				self::$instance->init();
			}

			return self::$instance;
		}

		function init() {
			add_action( 'wp_enqueue_scripts', array( $this, 'wooaa_enqueue_scripts' ), 99 );
			add_action( 'wp_ajax_wooaa_add_to_cart_variable', array( $this, 'wooaa_add_to_cart_variable' ) );
			add_action( 'wp_ajax_nopriv_wooaa_add_to_cart_variable', array( $this, 'wooaa_add_to_cart_variable' ) );
			add_filter( 'plugin_row_meta', array( $this, 'wooaa_row_meta' ), 10, 2 );
		}

		function wooaa_enqueue_scripts() {
			wp_enqueue_script( 'wooaa-frontend', WOOAA_URI . 'assets/js/frontend.js', array(
				'jquery',
				'wc-add-to-cart'
			), WOOAA_VERSION, true );
			wp_localize_script( 'wooaa-frontend', 'wooaa_vars', array(
					'ajax_url' => admin_url( 'admin-ajax.php' )
				)
			);
		}

		function wooaa_add_to_cart_variable() {
			ob_start();

			if ( ! isset( $_POST['product_id'] ) ) {
				return;
			}

			$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
			$product           = wc_get_product( $product_id );
			$quantity          = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_POST['quantity'] ) );
			$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
			$product_status    = get_post_status( $product_id );
			$variation_id      = $_POST['variation_id'];
			$variation         = $_POST['variation'];

			if ( $product && 'variation' === $product->get_type() ) {
				$variation_id = $product_id;
				$product_id   = $product->get_parent_id();

				if ( empty( $variation ) ) {
					$variation = $product->get_variation_attributes();
				}
			}

			if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation ) && 'publish' === $product_status ) {
				do_action( 'woocommerce_ajax_added_to_cart', $product_id );

				if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
					wc_add_to_cart_message( array( $product_id => $quantity ), true );
				}

				WC_AJAX::get_refreshed_fragments();
			} else {
				$data = array(
					'error'       => true,
					'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id ),
				);

				wp_send_json( $data );
			}

			die();
		}

		function wooaa_row_meta( $links, $file ) {
			static $plugin;

			if ( ! isset( $plugin ) ) {
				$plugin = plugin_basename( __FILE__ );
			}

			if ( $plugin === $file ) {
				$row_meta = array(
					'support' => '<a href="' . esc_url( WOOAA_DISCUSSION ) . '" target="_blank">' . esc_html__( 'Community support', 'wpc-ajax-add-to-cart' ) . '</a>',
				);

				return array_merge( $links, $row_meta );
			}

			return (array) $links;
		}
	}

	WPCleverWooaa::instance();
}
