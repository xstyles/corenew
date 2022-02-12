<?php
/**
 * E-mails: New initial order.
 *
 * @package WooCommerce Subscriptions Gifting/Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handles e-mailing of the "New Initial Order" e-mail to recipients.
 */
class WCSG_Email_Recipient_New_Initial_Order extends WC_Email {

	/**
	 * Subscription owner name.
	 *
	 * @var string
	 */
	public $subscription_owner;

	/**
	 * Array of subscription post objects.
	 *
	 * @var WP_Post[]
	 */
	public $subscriptions;

	/**
	 * Recipient user ID.
	 *
	 * @var int
	 */
	public $wcsg_sending_recipient_email;

	/**
	 * Create an instance of the class.
	 */
	public function __construct() {

		$this->id             = 'recipient_completed_order';
		$this->title          = __( 'New Initial Order - Recipient', 'woocommerce-subscriptions-gifting' );
		$this->description    = __( 'This email is sent to recipients notifying them of subscriptions purchased for them.', 'woocommerce-subscriptions-gifting' );
		$this->customer_email = true;
		$this->heading        = __( 'New Order', 'woocommerce-subscriptions-gifting' );
		$this->subject        = __( 'Your new subscriptions at {site_title}', 'woocommerce-subscriptions-gifting' );
		$this->template_html  = 'emails/recipient-new-initial-order.php';
		$this->template_plain = 'emails/plain/recipient-new-initial-order.php';
		$this->template_base  = plugin_dir_path( WCS_Gifting::$plugin_file ) . 'templates/';

		// Trigger for this email.
		add_action( 'wcsg_new_order_recipient_notification', array( $this, 'trigger' ), 10, 2 );

		WC_Email::__construct();
	}

	/**
	 * Trigger function.
	 *
	 * @param int       $recipient_user          User ID.
	 * @param WP_Post[] $recipient_subscriptions Array of subscription post objects.
	 */
	public function trigger( $recipient_user, $recipient_subscriptions ) {

		if ( $recipient_user ) {
			$this->object             = get_user_by( 'id', $recipient_user );
			$this->recipient          = stripslashes( $this->object->user_email );
			$subscription             = wcs_get_subscription( $recipient_subscriptions[0] );
			$this->subscription_owner = WCS_Gifting::get_user_display_name( $subscription->get_user_id() );
			$this->subscriptions      = $recipient_subscriptions;
		}

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$this->wcsg_sending_recipient_email = $recipient_user;
		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

		unset( $this->wcsg_sending_recipient_email );
	}

	/**
	 * Returns the content for the HTML version of the e-mail.
	 */
	public function get_content_html() {
		ob_start();
		wc_get_template(
			$this->template_html,
			array(
				'email_heading'          => $this->get_heading(),
				'blogname'               => $this->get_blogname(),
				'recipient_user'         => $this->object,
				'subscription_purchaser' => $this->subscription_owner,
				'subscriptions'          => $this->subscriptions,
				'sent_to_admin'          => false,
				'plain_text'             => false,
				'email'                  => $this,
			),
			'',
			$this->template_base
		);
		return ob_get_clean();
	}

	/**
	 * Returns the content for the plain text version of the e-mail.
	 */
	public function get_content_plain() {
		ob_start();
		wc_get_template(
			$this->template_plain,
			array(
				'email_heading'          => $this->get_heading(),
				'blogname'               => $this->get_blogname(),
				'recipient_user'         => $this->object,
				'subscription_purchaser' => $this->subscription_owner,
				'subscriptions'          => $this->subscriptions,
				'sent_to_admin'          => false,
				'plain_text'             => true,
				'email'                  => $this,
			),
			'',
			$this->template_base
		);
		return ob_get_clean();

	}
}
