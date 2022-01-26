<?php
/**
 * Plugin Name: WooCommerce Subscriptions Gifting
 * Plugin URI: https://woocommerce.com/products/woocommerce-subscriptions-gifting/
 * Description: Allow customers to buy a subscription product for someone else, then share subscription management between the purchaser and recipient.
 * Author: WooCommerce
 * Author URI: https://woocommerce.com/
 * Version: 2.1.3
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text-Domain: woocommerce-subscriptions-gifting
 * Domain Path: /languages
 *
 * Woo: 2866545:ac28f8f74457de42a18d32c3ec48b272
 * WC requires at least: 3.0
 * WC tested up to: 4.8
 *
 * GitHub Plugin URI: woocommerce/woocommerce-subscriptions-gifting
 * GitHub Branch: trunk
 *
 * Copyright: © 2021 WooCommerce
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package WooCommerce Subscriptions Gifting
 * @since   1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main class.
 */
class WCS_Gifting {

	/**
	 * Path to main plugin file.
	 *
	 * @var string
	 */
	public static $plugin_file = __FILE__;

	/**
	 * Plugin's current version.
	 *
	 * @var string
	 */
	public static $version = '2.1.3';

	/**
	 * Minimum WooCommerce version required.
	 *
	 * @var string
	 */
	public static $wc_minimum_supported_version = '3.0';

	/**
	 * Minimum WooCommerce Subscription version required.
	 *
	 * @var string
	 */
	public static $wcs_minimum_supported_version = '2.2';

	/**
	 * Minimum WooCommerce Memberships version required for integration.
	 *
	 * @var string
	 */
	public static $wcm_minimum_supported_version = '1.4';

	/**
	 * Setup hooks & filters, when the class is initialised.
	 */
	public static function init() {

		add_action( 'init', __CLASS__ . '::maybe_activate' );

		register_deactivation_hook( __FILE__, __CLASS__ . '::deactivate' );

		add_action( 'wp_enqueue_scripts', __CLASS__ . '::gifting_scripts' );

		// Needs to run after Subscriptions has loaded its dependant classes.
		self::load_dependant_classes();

		add_action( 'woocommerce_subscription_before_actions', __CLASS__ . '::add_billing_period_table_row' );

		add_filter( 'woocommerce_get_formatted_subscription_total', __CLASS__ . '::get_formatted_recipient_total', 10, 2 );

		add_filter( 'wcs_renewal_order_meta_query', __CLASS__ . '::remove_renewal_order_meta_query', 11 );

		// Handle "_is_gifted_subscription" argument in wc_get_orders().
		add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', array( __CLASS__, 'handle_is_gifted_subscription_query_var' ), 10, 2 );
	}

	/**
	 * Don't carry recipient meta data to renewal orders.
	 *
	 * @param string $order_meta_query Renewal order meta-query.
	 */
	public static function remove_renewal_order_meta_query( $order_meta_query ) {
		$order_meta_query .= " AND `meta_key` NOT IN ('_recipient_user')";
		return $order_meta_query;
	}

	/**
	 * Loads classes after plugins for classes dependant on other plugin files.
	 */
	public static function load_dependant_classes() {
		require_once 'includes/class-wcsg-query.php';

		if ( function_exists( 'wc_memberships' ) ) {
			if ( version_compare( get_option( 'wc_memberships_version' ), self::$wcm_minimum_supported_version, '>=' ) ) {
				require_once 'includes/class-wcsg-memberships-integration.php';
			} else {
				add_action( 'admin_notices', 'WCS_Gifting::plugin_dependency_notices' );
			}
		}
	}

	/**
	 * Register/queue frontend scripts.
	 */
	public static function gifting_scripts() {
		wp_register_script( 'woocommerce_subscriptions_gifting', plugins_url( '/js/wcs-gifting.js', __FILE__ ), array( 'jquery' ), '2.0.0', true );
		wp_enqueue_script( 'woocommerce_subscriptions_gifting' );
	}

	/**
	 * Determines if an email address belongs to the current user.
	 *
	 * @param string $email Email address.
	 * @return bool Returns whether the email address belongs to the current user.
	 */
	public static function email_belongs_to_current_user( $email ) {
		$emails_to_try = array();

		if ( is_user_logged_in() ) {
			$current_user    = wp_get_current_user();
			$emails_to_try[] = $current_user->user_email;
			$emails_to_try[] = $current_user->billing_email;
		}

		if ( is_checkout() && ! empty( $_POST['billing_email'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.CSRF.NonceVerification.NoNonceVerification
			$emails_to_try[] = sanitize_email( wp_unslash( $_POST['billing_email'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.CSRF.NonceVerification.NoNonceVerification
		}

		return in_array( $email, $emails_to_try, true );
	}

	/**
	 * Validates an array of recipient emails scheduling error notices if an error is found.
	 *
	 * @param array $recipients An array of recipient email addresses.
	 * @return bool Returns whether any errors have occurred.
	 */
	public static function validate_recipient_emails( $recipients ) {
		$invalid_email_found = false;
		$self_gifting_found  = false;

		if ( is_array( $recipients ) ) {
			foreach ( $recipients as $key => $recipient ) {
				$cleaned_recipient = sanitize_email( $recipient );
				if ( $recipient === $cleaned_recipient && is_email( $cleaned_recipient ) ) {
					if ( ! $self_gifting_found && self::email_belongs_to_current_user( $cleaned_recipient ) ) {
						wc_add_notice( __( 'Please enter someone else\'s email address.', 'woocommerce-subscriptions-gifting' ), 'error' );
						$self_gifting_found = true;
					}
				} elseif ( ! empty( $recipient ) && ! $invalid_email_found ) {
					wc_add_notice( __( ' Invalid email address.', 'woocommerce-subscriptions-gifting' ), 'error' );
					$invalid_email_found = true;
				}
			}
		}
		return ! ( $invalid_email_found || $self_gifting_found );
	}

	/**
	 * Attaches recipient information to a subscription cart item key when the recipient information is updated. If necessary
	 * combines cart items if the same cart key exists in the cart.
	 *
	 * @param object $item The item in the cart to be updated.
	 * @param string $key  Cart item key.
	 * @param array  $new_recipient_data The new recipient information for the item.
	 */
	public static function update_cart_item_key( $item, $key, $new_recipient_data ) {
		if ( empty( $item['wcsg_gift_recipients_email'] ) || $item['wcsg_gift_recipients_email'] != $new_recipient_data ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison

			$cart_item_data = self::add_cart_item_data( $item, $key, $new_recipient_data );
			$new_key        = WC()->cart->generate_cart_id( $item['product_id'], $item['variation_id'], $item['variation'], $cart_item_data );
			$cart_item      = WC()->cart->get_cart_item( $new_key );

			if ( $new_key != $key ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison

				if ( ! empty( $cart_item ) ) {
					$combined_quantity = $item['quantity'] + $cart_item['quantity'];

					WC()->cart->cart_contents[ $new_key ]['quantity'] = $combined_quantity;
					unset( WC()->cart->cart_contents[ $key ] );
				} else { // there is no item in the cart with the same new key.

					$item_cart_position = array_search( $key, array_keys( WC()->cart->cart_contents ), true );

					WC()->cart->cart_contents = array_merge(
						array_slice( WC()->cart->cart_contents, 0, $item_cart_position, true ),
						array( $new_key => WC()->cart->cart_contents[ $key ] ),
						array_slice( WC()->cart->cart_contents, $item_cart_position, count( WC()->cart->cart_contents ), true )
					);

					if ( empty( $new_recipient_data ) ) {
						unset( WC()->cart->cart_contents[ $new_key ]['wcsg_gift_recipients_email'] );
					} else {
						WC()->cart->cart_contents[ $new_key ]['wcsg_gift_recipients_email'] = $new_recipient_data;
					}

					unset( WC()->cart->cart_contents[ $key ] );
				}
			}
		}
	}

	/**
	 * Populates the cart item data that will be used by WooCommerce to generate a unique ID for the cart item. That is to
	 * avoid merging different products when they aren't the same. Previously the resubscribe status was ignored.
	 *
	 * @param array  $item               A cart item with all its data.
	 * @param string $key                A cart item key.
	 * @param array  $new_recipient_data Email address of the new recipient.
	 * @return array New cart item data.
	 */
	private static function add_cart_item_data( $item, $key, $new_recipient_data ) {
		// start with a clean slate.
		$cart_item_data = array();

		// Add the recipient email.
		if ( ! empty( $new_recipient_data ) ) {
			$cart_item_data = array( 'wcsg_gift_recipients_email' => $new_recipient_data );
		}

		// Add resubscribe data.
		if ( array_key_exists( 'subscription_resubscribe', $item ) ) {
			$cart_item_data = array_merge( $cart_item_data, array( 'subscription_resubscribe' => $item['subscription_resubscribe'] ) );
		}

		$cart_item_data = apply_filters( 'wcsg_cart_item_data', $cart_item_data, $item, $key, $new_recipient_data );

		return $cart_item_data;
	}

	/**
	 * Checks on each admin page load if Gifting plugin is activated.
	 *
	 * Apparently the official WP API is "lame" and it's far better to use an upgrade routine fired on admin_init: https://core.trac.wordpress.org/ticket/14170#comment:68
	 *
	 * @since 1.1
	 */
	public static function maybe_activate() {

		$is_active = get_option( WCSG_Admin::$option_prefix . '_is_active', false );

		if ( false == $is_active ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison

			update_option( WCSG_Admin::$option_prefix . '_is_active', true );

			set_transient( 'wcsg_show_activation_notice', true, DAY_IN_SECONDS );

			flush_rewrite_rules();

			do_action( 'woocommerce_subscriptions_gifting_activated' );
		}
	}

	/**
	 * Called when the plugin is deactivated. Deletes the is active flag and fires an action.
	 *
	 * @since 2.0.0
	 */
	public static function deactivate() {

		delete_option( WCSG_Admin::$option_prefix . '_is_active' );

		flush_rewrite_rules();

		do_action( 'woocommerce_subscriptions_gifting_deactivated' );
	}

	/**
	 * Renders the add recipient fields (including the checkbox and e-mail input).
	 *
	 * @param string $email           E-mail address.
	 * @param string $id              ID, for uniqueness on page.
	 * @param string $print_or_return Wether to print or return the HTML content. Optional. Default behaviour is to print the string. Pass 'return' to return the HTML content instead.
	 * @return string Returns the HTML string if $print_or_return is set to 'return', otherwise prints the HTML and nothing is returned.
	 * @since 2.1
	 */
	public static function render_add_recipient_fields( $email = '', $id = '', $print_or_return = 'print' ) {
		$output = wc_get_template_html(
			'html-add-recipient.php',
			self::get_add_recipient_template_args( $email, $id ),
			'',
			plugin_dir_path( self::$plugin_file ) . 'templates/'
		);

		if ( 'return' === $print_or_return ) {
			return $output;
		} else {
			echo $output; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped.
		}
	}

	/**
	 * Build the set of arguments to be passed to the "Add Recipient" template.
	 *
	 * @param string $email E-mail address.
	 * @param string $id    ID, for CSS uniqueness on page.
	 * @since 2.1
	 */
	public static function get_add_recipient_template_args( $email = '', $id = '' ) {
		$id = $id ? esc_attr( $id ) : '0';

		// E-mail field.
		$email_field_args = array(
			'placeholder'      => 'recipient@example.com',
			'class'            => array( 'woocommerce_subscriptions_gifting_recipient_email' ),
			'style_attributes' => array(),
		);

		if ( ! empty( $email ) && ( self::email_belongs_to_current_user( $email ) || ! is_email( $email ) ) ) {
			array_push( $email_field_args['class'], 'woocommerce-invalid' );
		}

		// "This is a gift" checkbox.
		$checkbox_field_args = array(
			'class'            => apply_filters( 'wcsg_recipient_checkbox_class', array() ),
			'style_attributes' => apply_filters( 'wcsg_recipient_checkbox_style_attributes', array() ),
			'disabled'         => apply_filters( 'wcsg_recipient_checkbox_disabled', false ),
			'checked'          => empty( $email ) ? apply_filters( 'wcsg_recipient_checkbox_checked', false ) : true,
		);

		$nonce_field  = '<input type="hidden" id="_wcsgnonce_' . $id . '" name="_wcsgnonce" value="' . wp_create_nonce( 'wcsg_add_recipient' ) . '" />';
		$nonce_field .= wp_referer_field( false );

		$args = array(
			'email'                      => $email,
			'id'                         => $id,
			'container_style_attributes' => apply_filters( 'wcsg_recipient_fields_style_attributes', empty( $email ) ? array( 'display: none;' ) : array(), $email ),
			'container_css_class'        => apply_filters( 'wcsg_recipient_fields_css_class', array(), $email ),
			'email_field_args'           => apply_filters( 'wcsg_recipient_email_field_args', $email_field_args, $email ),
			'checkbox_field_args'        => apply_filters( 'wcsg_recipient_checkbox_field_args', $checkbox_field_args, $email ),
			'nonce_field'                => $nonce_field,
		);

		return $args;
	}

	/**
	 * Adds row to subscription details table that displays subscription period for recipients.
	 *
	 * @param WC_Subscription $subscription Subscription object.
	 * @since 2.0.0
	 */
	public static function add_billing_period_table_row( $subscription ) {
		if ( ! wcsg_is_wc_subscriptions_pre( '2.2.19' ) && self::is_gifted_subscription( $subscription ) && get_current_user_id() == self::get_recipient_user( $subscription ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			$subscription_details  = array(
				'recurring_amount'      => '',
				'subscription_period'   => $subscription->get_billing_period(),
				'subscription_interval' => $subscription->get_billing_interval(),
				'initial_amount'        => '',
				'use_per_slash'         => false,
			);
			$billing_period_string = apply_filters( 'woocommerce_subscription_price_string_details', $subscription_details, $subscription );
			?>
			<tr>
				<td><?php echo esc_html_x( 'Renewing', 'table heading', 'woocommerce-subscriptions-gifting' ); ?></td>
				<td><?php echo esc_html( wcs_price_string( $billing_period_string ) ); ?></td>
			</tr>
			<?php
		}
	}

	/**
	 * Reformats the price of the subscription to hide it if the user is the recipient.
	 *
	 * @param string          $formatted_order_total The order total formatted.
	 * @param WC_Subscription $subscription          Subscription object.
	 * @since 2.0.0
	 */
	public static function get_formatted_recipient_total( $formatted_order_total, $subscription ) {
		global $wp;

		if ( ! wcsg_is_wc_subscriptions_pre( '2.2.19' ) && is_account_page() && isset( $wp->query_vars['subscriptions'] ) && self::is_gifted_subscription( $subscription ) && get_current_user_id() == self::get_recipient_user( $subscription ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			$formatted_order_total = '-';
		}
		return $formatted_order_total;
	}

	/**
	 * Returns a combination of the customer's first name, last name and email depending on what the customer has set.
	 *
	 * @param int  $user_id    The ID of the customer user.
	 * @param bool $strip_tags Whether to strip HTML tags in user name (defaulted to false).
	 */
	public static function get_user_display_name( $user_id, $strip_tags = false ) {

		$user = get_user_by( 'id', $user_id );
		$name = '';

		if ( ! empty( $user->first_name ) ) {
			$name = $user->first_name . ( ( ! empty( $user->last_name ) ) ? ' ' . $user->last_name : '' ) . ' (' . make_clickable( $user->user_email ) . ')';
		} else {
			$name = make_clickable( $user->user_email );
		}

		if ( $strip_tags ) {
			$name = wp_strip_all_tags( $name );
		}

		return $name;
	}

	/**
	 * Displays plugin dependency notices if required plugins are inactive or the installed version is less than a
	 * supported version.
	 */
	public static function plugin_dependency_notices() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			self::output_plugin_dependency_notice( 'WooCommerce' );

			return;
		}

		if ( ! class_exists( 'WC_Subscription' ) ) {
			self::output_plugin_dependency_notice( 'WooCommerce Subscriptions' );

			return;
		}

		if ( version_compare( get_option( 'woocommerce_subscriptions_active_version' ), self::$wcs_minimum_supported_version, '<' ) ) {
			self::output_plugin_dependency_notice( 'WooCommerce Subscriptions', self::$wcs_minimum_supported_version );
		}

		if ( version_compare( get_option( 'woocommerce_db_version' ), self::$wc_minimum_supported_version, '<' ) ) {
			self::output_plugin_dependency_notice( 'WooCommerce', self::$wc_minimum_supported_version );
		}

		if ( class_exists( 'WC_Memberships' ) && version_compare( get_option( 'wc_memberships_version' ), self::$wcm_minimum_supported_version, '<' ) ) {
			self::output_plugin_dependency_notice( 'WooCommerce Memberships', self::$wcm_minimum_supported_version );
		}
	}

	/**
	 * Prints a plugin dependency admin notice. If a required version is supplied an invalid version notice is printed,
	 * otherwise an inactive plugin notice is printed.
	 *
	 * @param string $plugin_name The plugin name.
	 * @param string $required_version The minimum supported version of the plugin.
	 */
	public static function output_plugin_dependency_notice( $plugin_name, $required_version = false ) {

		if ( current_user_can( 'activate_plugins' ) ) {
			if ( $required_version ) {
				?>
				<div id="message" class="error">
					<p>
					<?php
					if ( 'WooCommerce Memberships' === $plugin_name ) {
						// translators: 1$-2$: opening and closing <strong> tags, 3$ plugin name, 4$ required plugin version, 5$-6$: opening and closing link tags, leads to plugins.php in admin, 7$: line break, 8$-9$ Opening and closing small tags.
						printf( esc_html__( '%1$sWooCommerce Subscriptions Gifting Membership integration is inactive.%2$s In order to integrate with WooCommerce Memberships, WooCommerce Subscriptions Gifting requires %3$s %4$s or newer. %5$sPlease update &raquo;%6$s %7$s%8$sNote: All other WooCommerce Subscriptions Gifting features will remain available, however purchasing membership plans for recipients will fail to grant the membership to the gift recipient.%9$s', 'woocommerce-subscriptions-gifting' ), '<strong>', '</strong>', esc_html( $plugin_name ), esc_html( $required_version ), '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">', '</a>', '</br>', '<small>', '</small>' );
					} else {
						// translators: 1$-2$: opening and closing <strong> tags, 3$ plugin name, 4$ required plugin version, 5$-6$: opening and closing link tags, leads to plugins.php in admin.
						printf( esc_html__( '%1$sWooCommerce Subscriptions Gifting is inactive.%2$s This version of WooCommerce Subscriptions Gifting requires %3$s %4$s or newer. %5$sPlease update &raquo;%6$s', 'woocommerce-subscriptions-gifting' ), '<strong>', '</strong>', esc_html( $plugin_name ), esc_html( $required_version ), '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">', '</a>' );
					}
					?>
					</p>
				</div>
				<?php
			} else {
				switch ( $plugin_name ) {
					case 'WooCommerce Subscriptions':
						$plugin_url = 'http://www.woocommerce.com/products/woocommerce-subscriptions/';
						break;
					case 'WooCommerce':
						$plugin_url = 'http://wordpress.org/extend/plugins/woocommerce/';
						break;
					default:
						$plugin_url = '';
				}
				?>
				<div id="message" class="error">
					<p>
					<?php
						// translators: 1$-2$: opening and closing <strong> tags, 3$ plugin name, 4$:opening link tag, leads to plugin product page, 5$-6$: opening and closing link tags, leads to plugins.php in admin.
						printf( esc_html__( '%1$sWooCommerce Subscriptions Gifting is inactive.%2$s WooCommerce Subscriptions Gifting requires the %4$s%3$s%6$s plugin to be active to work correctly. Please %5$sinstall & activate %3$s &raquo;%6$s', 'woocommerce-subscriptions-gifting' ), '<strong>', '</strong>', esc_html( $plugin_name ), '<a href="' . esc_url( $plugin_url ) . '">', '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">', '</a>' );
					?>
					</p>
				</div>
				<?php
			}
		}
	}

	/**
	 * Checks whether a subscription is a gifted subscription.
	 *
	 * @param int|WC_Subscription $subscription either a subscription object or subscription's ID.
	 * @return bool
	 */
	public static function is_gifted_subscription( $subscription ) {
		$is_gifted_subscription = false;

		if ( ! $subscription instanceof WC_Subscription ) {
			$subscription = wcs_get_subscription( $subscription );
		}

		if ( wcs_is_subscription( $subscription ) ) {
			$recipient_user_id      = self::get_recipient_user( $subscription );
			$is_gifted_subscription = ! empty( $recipient_user_id ) && is_numeric( $recipient_user_id );
		}

		return $is_gifted_subscription;
	}

	/**
	 * Returns a list of all order item ids and their containing order ids that have been purchased for a recipient.
	 *
	 * @param int $recipient_user_id User ID.
	 * @return array
	 */
	public static function get_recipient_order_items( $recipient_user_id ) {
		global $wpdb;

			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT o.order_id, i.order_item_id
					FROM {$wpdb->prefix}woocommerce_order_itemmeta AS i
					INNER JOIN {$wpdb->prefix}woocommerce_order_items as o
					ON i.order_item_id=o.order_item_id
					WHERE meta_key = 'wcsg_recipient'
					AND meta_value = %s",
					'wcsg_recipient_id_' . $recipient_user_id
				),
				ARRAY_A
			);
	}

	/**
	 * Returns the user's shipping address.
	 *
	 * @param int $user_id User ID.
	 * @return array
	 */
	public static function get_users_shipping_address( $user_id ) {
		return array(
			'first_name' => get_user_meta( $user_id, 'shipping_first_name', true ),
			'last_name'  => get_user_meta( $user_id, 'shipping_last_name', true ),
			'company'    => get_user_meta( $user_id, 'shipping_company', true ),
			'address_1'  => get_user_meta( $user_id, 'shipping_address_1', true ),
			'address_2'  => get_user_meta( $user_id, 'shipping_address_2', true ),
			'city'       => get_user_meta( $user_id, 'shipping_city', true ),
			'state'      => get_user_meta( $user_id, 'shipping_state', true ),
			'postcode'   => get_user_meta( $user_id, 'shipping_postcode', true ),
			'country'    => get_user_meta( $user_id, 'shipping_country', true ),
		);
	}

	/**
	 * Determines if an order contains a gifted subscription.
	 *
	 * @param mixed $order the order id or order object to check.
	 * @return bool
	 */
	public static function order_contains_gifted_subscription( $order ) {

		if ( ! is_object( $order ) ) {
			$order = wc_get_order( $order );
		}

		if ( ! is_a( $order, 'WC_Order' ) ) {
			return false;
		}

		$contains_gifted_subscription = false;

		foreach ( wcs_get_subscriptions_for_order( $order ) as $subscription_id => $subscription ) {

			if ( self::is_gifted_subscription( $subscription ) ) {
				$contains_gifted_subscription = true;
				break;
			}
		}

		return $contains_gifted_subscription;
	}

	/**
	 * Retrieves the user id of the recipient stored in order item meta.
	 *
	 * @param mixed $order_item the order item to check.
	 * @return mixed bool|int The recipient user id or false if the order item is not gifted.
	 */
	public static function get_order_item_recipient_user_id( $order_item ) {

		if ( is_a( $order_item, 'WC_Order_Item' ) && $order_item->meta_exists( 'wcsg_recipient' ) ) {
			$raw_recipient_meta = $order_item->get_meta( 'wcsg_recipient' );
		} elseif ( isset( $order_item['item_meta']['wcsg_recipient'] ) ) {
			$raw_recipient_meta = $order_item['item_meta']['wcsg_recipient'][0];
		}

		return isset( $raw_recipient_meta ) ? substr( $raw_recipient_meta, strlen( 'wcsg_recipient_id_' ) ) : false;
	}

	/**
	 * Create a recipient user account.
	 *
	 * @param string $recipient_email Recipient's e-mail address.
	 * @return int ID for newly created user.
	 */
	public static function create_recipient_user( $recipient_email ) {
		$username = explode( '@', $recipient_email );
		$username = sanitize_user( $username[0], true );
		$counter  = 1;

		$original_username = $username;

		while ( username_exists( $username ) ) {
			$username = $original_username . $counter;
			$counter++;
		}

		$password = wp_generate_password();

		$recipient_user_id = wc_create_new_customer( $recipient_email, $username, $password );

		// set a flag to force the user to update/set account information on login.
		update_user_meta( $recipient_user_id, 'wcsg_update_account', 'true' );
		return $recipient_user_id;
	}

	/**
	 * Retrieve the recipient user ID from a subscription.
	 *
	 * @param WC_Subscription $subscription Subscription object.
	 * @return string The recipient's user ID. Returns an empty string if there is no recipient set.
	 */
	public static function get_recipient_user( $subscription ) {
		$recipient_user_id = '';

		if ( method_exists( $subscription, 'get_meta' ) ) {
			if ( $subscription->meta_exists( '_recipient_user' ) ) {
				$recipient_user_id = $subscription->get_meta( '_recipient_user' );
			}
		} else { // WC < 3.0.
			$recipient_user_id = $subscription->recipient_user;
		}

		return $recipient_user_id;
	}

	/**
	 * Set the recipient user ID on a subscription.
	 *
	 * @param WC_Subscription $subscription Subscription object.
	 * @param int             $user_id      The user ID of the user to set as the recipient on the subscription.
	 * @param string          $save         Whether to save the data or not, 'save' to save the data, otherwise it won't be saved.
	 * @param int             $meta_id      The meta ID of existing meta data if you wish to overwrite an existing recipient meta value.
	 */
	public static function set_recipient_user( &$subscription, $user_id, $save = 'save', $meta_id = '' ) {
		$current_user_id = absint( self::get_recipient_user( $subscription ) );

		if ( function_exists( 'wcs_set_objects_property' ) && false === wcsg_is_woocommerce_pre( '3.0' ) ) { // Subscriptions 2.2.0+.

			wcs_set_objects_property( $subscription, 'recipient_user', $user_id, $save, $meta_id );

		} else {

			$subscription->recipient_user = $user_id;

			if ( 'save' === $save ) {
				if ( ! empty( $meta_id ) ) {
					update_metadata_by_mid( 'post', $meta_id, $user_id, '_recipient_user' );
				} else {
					update_post_meta( $subscription->id, '_recipient_user', $user_id );
				}
			}
		}

		if ( 'save' === $save ) {
			do_action( 'woocommerce_subscriptions_gifting_recipient_changed', $subscription, $user_id, $current_user_id );
		}
	}

	/**
	 * Delete the recipient user ID on a subscription
	 *
	 * @param WC_Subscription $subscription Subscription object.
	 * @param string          $save         Whether to save the data or not, 'save' to save the data, otherwise it won't be saved.
	 * @param int             $meta_id      The meta ID of existing recipient meta data if you wish to only delete a field specified by ID.
	 */
	public static function delete_recipient_user( &$subscription, $save = 'save', $meta_id = '' ) {

		if ( function_exists( 'wcs_delete_objects_property' ) && false === wcsg_is_woocommerce_pre( '3.0' ) ) { // Subscriptions 2.2.0+ and WC 3.0+.

			wcs_delete_objects_property( $subscription, 'recipient_user', $save, $meta_id );

		} else {

			unset( $subscription->recipient_user );

			// Save the data.
			if ( 'save' === $save ) {
				if ( ! empty( $meta_id ) ) {
					delete_metadata_by_mid( 'post', $meta_id );
				} else {
					delete_post_meta( $subscription->id, '_recipient_user' );
				}
			}
		}
	}

	/**
	 * Retrieves a set of gifted subscriptions based on certain parameters.
	 *
	 * @see wc_get_orders()
	 *
	 * @param array $args Custom args for query, excluding 'type' and custom var 'is_gifted_subscription'.
	 * @return stdClass|WC_Order[]|int[]
	 *
	 * @since 2.0.3
	 */
	public static function get_gifted_subscriptions( $args = array() ) {
		$query_args = wp_parse_args(
			$args,
			array(
				'limit'   => -1,
				'status'  => 'any',
				'orderby' => 'date',
				'order'   => 'desc',
			)
		);

		$query_args['type']                   = 'shop_subscription';
		$query_args['is_gifted_subscription'] = true;

		return wc_get_orders( $query_args );
	}

	/**
	 * Handle custom WCS Gifting query vars to get subscriptions with 'WCS Gifting' meta.
	 *
	 * @param array $query      Args for WP_Query.
	 * @param array $query_vars Query vars from WC_Order_Query.
	 *
	 * @return array modified $query
	 * @since 2.0.3
	 */
	public static function handle_is_gifted_subscription_query_var( $query, $query_vars ) {
		if ( ! empty( $query_vars['is_gifted_subscription'] ) && true === $query_vars['is_gifted_subscription'] ) {
			$query['meta_query'][] = array(
				array(
					'key'     => '_recipient_user',
					'compare' => 'EXISTS',
				),
			);
		}

		return $query;
	}

	/**
	 * Does the site requires shipping address data for non-virtual products. Default: true
	 *
	 * @return bool
	 */
	public static function require_shipping_address_for_virtual_products() {
		return apply_filters( 'wcsg_require_shipping_address_for_virtual_products', true );
	}

	/**
	 * Counts the number of Gifted Subscriptions.
	 *
	 * @return int
	 * @since 2.1.0
	 */
	public static function get_gifted_subscriptions_count() {
		global $wpdb;

		$total = $wpdb->get_var(
			"SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON (p.ID = pm.post_id)
			WHERE p.post_type = 'shop_subscription'
			AND p.post_status NOT IN ( 'auto-draft', 'trash' )
			AND pm.meta_key = '_recipient_user'"
		);

		return intval( $total );
	}

	/**
	 * Register/queue admin scripts.
	 */
	public static function admin_scripts() {
		_deprecated_function( __METHOD__, '2.0.0', 'WCSG_Admin::enqueue_scripts()' );
	}

	/**
	 * Install wcsg
	 */
	public static function wcsg_install() {
		_deprecated_function( __METHOD__, '2.0.0', 'WCS_Gifting::maybe_activate()' );
	}

	/**
	 * Flush rewrite rules if they haven't been flushed since plugin activation
	 */
	public static function maybe_flush_rewrite_rules() {
		_deprecated_function( __METHOD__, '2.0.0', 'flush_rewrite_rules()' );
	}

	/**
	 * Overrides the default recent order template for gifted subscriptions
	 *
	 * @param string $located       Path to template.
	 * @param string $template_name Template name.
	 * @param array  $args          Arguments.
	 */
	public static function get_recent_orders_template( $located, $template_name, $args ) {
		_deprecated_function( __FUNCTION__, '2.0.0', 'WCSG_Template_Loader::get_recent_orders_template()' );
		WCSG_Template_Loader::get_recent_orders_template( $located, $template_name, $args );
	}

	/**
	 * Generates an array of arguments used to create the recipient email html fields.
	 *
	 * @param string $email E-mail address.
	 * @return array email_field_args A set of html attributes
	 * @deprecated 2.1
	 */
	public static function get_recipient_email_field_args( $email ) {
		_deprecated_function( __METHOD__, '2.1', 'WCS_Gifting::get_add_recipient_template_args()' );
		$args = self::get_add_recipient_template_args( $email );
		return $args['email_field_args'];
	}

	/**
	 * Generates an array of arguments used to create the recipient checkbox html fields
	 *
	 * @param string $email The email of the gift recipient.
	 * @return array checkbox_field_args A set of html attributes
	 * @since 2.0.2
	 * @deprecated 2.1
	 */
	public static function get_recipient_checkbox_field_args( $email ) {
		_deprecated_function( __METHOD__, '2.1', 'WCS_Gifting::get_add_recipient_template_args()' );
		$args = self::get_add_recipient_template_args( $email );
		return $args['checkbox_field_args'];
	}
}

/**
 * Loads the Gifting plugin if the dependency requirements are met.
 *
 * @since 2.0.1
 */
function wcsg_load() {
	// Load textdomain.
	load_plugin_textdomain( 'woocommerce-subscriptions-gifting', false, basename( dirname( __FILE__ ) ) . '/languages/' );

	/**
	 * Check if WooCommerce and Subscriptions are active.
	 */
	if ( ! class_exists( 'WooCommerce' ) || version_compare( get_option( 'woocommerce_db_version' ), WCS_Gifting::$wc_minimum_supported_version, '<' ) ) {
		add_action( 'admin_notices', 'WCS_Gifting::plugin_dependency_notices' );
		return;
	}

	if ( ! class_exists( 'WC_Subscription' ) || version_compare( get_option( 'woocommerce_subscriptions_active_version' ), WCS_Gifting::$wcs_minimum_supported_version, '<' ) ) {
		add_action( 'admin_notices', 'WCS_Gifting::plugin_dependency_notices' );
		return;
	}

	require_once 'includes/wcsg-compatibility-functions.php';
	require_once 'includes/class-wcsg-product.php';
	require_once 'includes/class-wcsg-cart.php';
	require_once 'includes/class-wcsg-checkout.php';
	require_once 'includes/class-wcsg-recipient-management.php';
	require_once 'includes/class-wcsg-recipient-details.php';
	require_once 'includes/class-wcsg-email.php';
	require_once 'includes/class-wcsg-download-handler.php';
	require_once 'includes/class-wcsg-admin.php';
	require_once 'includes/class-wcsg-recipient-addresses.php';
	require_once 'includes/class-wcsg-template-loader.php';
	require_once 'includes/class-wcsg-admin-system-status.php';

	if ( ! wcsg_is_woocommerce_pre( '3.4.0' ) && class_exists( 'WCS_Privacy' ) ) {
		require_once 'includes/privacy/class-wcsg-privacy.php';
		new WCSG_Privacy();
	}

	WCS_Gifting::init();
}
add_action( 'plugins_loaded', 'wcsg_load', 11 );

// Subscribe to automated translations.
add_action( 'woocommerce_translations_updates_for_woocommerce-subscriptions-gifting', '__return_true' );
