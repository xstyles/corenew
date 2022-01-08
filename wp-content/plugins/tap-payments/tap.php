<?php
/*
Plugin Name: WooCommerce - Tap WebConnect
Description: Tap WebConnect is a plugin provided by Tap Payments that enables KNET, Visa & MasterCard for Woocommerce Version 2.0.0 or greater version.
Version: 1.1
Author: Tap Payments
*/

add_action('plugins_loaded', 'woocommerce_tap_init', 0);
//define tap payment padge location
define('tap_imgdir', WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) . '/assets/img/');

//viewing the content of tap template
function woocommerce_tap_init(){
	if(!class_exists('WC_Payment_Gateway')) return;

    if( isset($_GET['msg']) && !empty($_GET['msg']) ){
        add_action('the_content', 'tap_showMessage');
    }
    function tap_showMessage($content){
            return '<div class="'.htmlentities(sanitize_text_field($_GET['type'])).'">'.htmlentities(urldecode(sanitize_text_field($_GET['msg']))).'</div>'.$content;
    }

    /**
     * Gateway class
     */
	class WC_tap extends WC_Payment_Gateway{
		public function __construct(){
			$this->id 					= 'tap';
			$this->method_title 		= 'Tap';
			$this->method_description	= "Pay via Tap; you can pay securely with your debit or credit card.";
			$this->has_fields 			= false;
			$this->init_form_fields();
			$this->init_settings();
			$this->icon 			= tap_imgdir . 'logo.png';
						
			$this->title 			= $this->settings['title'];
			$this->redirect_page_id = $this->settings['redirect_page_id'];
			if ( $this->settings['testmode'] == "yes" ) {
				$this->liveurl 			= 'http://live.gotapnow.com/webpay.aspx';
			} else {
				$this->liveurl 			= 'https://www.gotapnow.com/webpay.aspx';
			}	

			$this->merchant_id      = $this->settings['merchant_id'];
			$this->username         = $this->settings['username'];
			$this->password         = $this->settings['password'];
			$this->apikey 			= $this->settings['apikey'];	
			$this->description 		= $this->settings['description'];	
			
			$this->msg['message'] 	= "";
			$this->msg['class'] 	= "";
					
			add_action('init', array(&$this, 'check_tap_response'));
			//update for woocommerce >2.0
			add_action( 'woocommerce_api_' . strtolower( get_class( $this ) ), array( $this, 'check_tap_response' ) );
			
			if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
				/* 2.0.0 */
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
			} else {
				/* 1.6.6 */
				add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
			}
			
			add_action('woocommerce_receipt_tap', array(&$this, 'receipt_page'));
		}
    
		function init_form_fields(){
			$this->form_fields = array(
				'enabled' => array(
					'title' 		=> __('Enable/Disable', 'kdc'),
					'type' 			=> 'checkbox',
					'label' 		=> __('Enable Tap Payment Module.', 'kdc'),
					'default' 		=> 'no',
					'description' 	=> 'Show in the Payment List as a payment option'
				),
      			'title' => array(
					'title' 		=> __('Title:', 'kdc'),
					'type'			=> 'text',
					'default' 		=> __('Tap', 'kdc'),
					'description' 	=> __('This controls the title which the user sees during checkout.', 'kdc'),
					'desc_tip' 		=> true
				),
      			'description' => array(
					'title' 		=> __('Description:', 'kdc'),
					'type' 			=> 'textarea',
					'default' 		=> __('pay via Tap; you can pay securely with your debit or credit card.', 'kdc'),
					'description' 	=> __('This controls the description which the user sees during checkout.', 'kdc'),
					'desc_tip' 		=> true
				),
      			'merchant_id' => array(
                    'title'       => __('Merchant ID', 'kdc'),
                    'type'        => 'text',
					          'value'       => '',
					          'description' => __( 'Get your Merchant ID from Tap.','woocommerce' ),
					          'default'     => '',
					          'desc_tip'    =>true,
                    'required'    =>true),
				'username' => array(
                    'title'       => __('API Username', 'kdc'),
                    'type'        => 'text',
					          'value'       => '',
					          'description' => __( 'Get your API credentials from Tap.','woocommerce' ),
					          'default'     => '',
					          'desc_tip'    =>true,
                    'required'    =>true),
				'apikey' => array(
                    'title'       => __('API Key', 'kdc'),
                    'type'        => 'text',
					          'value'       => '',
					          'description' => __( 'Get your API credentials from Tap.','woocommerce' ),
					          'default'     => '',
					          'desc_tip'    =>true,
                    'required'    =>true),
                'password' => array(
                    'title'       => __('API Password', 'kdc'),
                    'type'        => 'password',
					          'value'       => '',
                    'description' => __( 'Get your API credentials from Tap.', 'woocommerce' ),
          					'default'     => '',
          					'desc_tip'    => true,
                    'required'    => true
                   ),
      			'testmode' => array(
					'title' 		=> __('TEST Mode', 'kdc'),
					'type' 			=> 'checkbox',
					'label' 		=> __('Enable Tap TEST Transactions.', 'kdc'),
					'default' 		=> 'no',
					'description' 	=> __('Tick to run TEST Transaction on the Tap platform'),
					'desc_tip' 		=> true
                ),
      			'redirect_page_id' => array(
					'title' 		=> __('Return Page'),
					'type' 			=> 'select',
					'options' 		=> $this->tap_get_pages('Select Page'),
					'description' 	=> __('URL of success page', 'kdc'),
					'desc_tip' 		=> true
                )
			);
		}
        /**
         * Admin Panel Options
         * - Options for bits like 'title' and availability on a country-by-country basis
         **/
		public function admin_options(){
			echo '<h3>'.__('Tap', 'kdc').'</h3>';
			echo '<p>'.__('Tap works by sending the user to Tap to enter their payment information.').'</p>';
			echo '<table class="form-table">';
			// Generate the HTML For the settings form.
			$this -> generate_settings_html();
			echo '</table>';
		}
        /**
         *  There are no payment fields for techpro, but we want to show the description if set.
         **/
		function payment_fields(){
			if($this->description) echo wpautop(wptexturize($this->description));
		}
		/**
		* Receipt Page
		**/
		function receipt_page($order){
			echo '<p>'.__('Thank you for your order, please click the button below to pay with Tap.', 'kdc').'</p>';
			echo $this->generate_tap_form($order);
		}
		/**
		* Generate tap button link
		**/
		function generate_tap_form($order_id){
			global $woocommerce;
			$order = new WC_Order( $order_id );
			$txnid = $order_id.'_'.date("ymds");
			
			
			if ( $this->redirect_page_id == "" || $this->redirect_page_id == 0 ) {
				$redirect_url = $order->get_checkout_order_received_url();
			} else {
				$redirect_url = get_permalink($this->redirect_page_id);
			}

			//For wooCoomerce 2.0
			if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
				$redirect_url = add_query_arg( 'wc-api', get_class( $this ), $redirect_url );
			}

			//$productinfo = "Order $order_id";

			//$str = "$this->merchant_id|$txnid|$order->order_total|$productinfo|$order->billing_first_name|$order->billing_email|$order_id||||||||||$this->salt";
			//$hash = strtolower(hash('sha512', $str));

			$tap_args = array(
				'MEID' 			=> $this->merchant_id,
				'UName'			=> $this->username,
				'PWD'			=> $this->password,
				'ItemName1'		=> 'Order ID : '.$txnid,
				'ItemQty1'		=> '1',
				'OrdID' 		=> $order_id,
				'ItemPrice1' 	=> $order->order_total,
				'CurrencyCode'	=> strtoupper(get_woocommerce_currency()),
				'CstFName'		=> $order->billing_first_name.' '.$order->billing_last_name,
				'CstEmail' 		=> $order->billing_email,
				'CstMobile' 	=> $order->billing_phone,
				'ReturnURL' 	=> $redirect_url
			);
			$tap_args_array = array();
			foreach($tap_args as $key => $value){
				$tap_args_array[] = "<input type='hidden' name='$key' value='$value'/>";
			}
			
			return '	<form action="'.$this->liveurl.'" method="post" id="tap_payment_form">
  				' . implode('', $tap_args_array) . '
				<input type="submit" class="button-alt" id="submit_tap_payment_form" value="'.__('Pay via Tap', 'kdc').'" /> <a class="button cancel" href="'.$order->get_cancel_order_url().'">'.__('Cancel order &amp; restore cart', 'kdc').'</a>
					<script type="text/javascript">
					jQuery(function(){
					jQuery("body").block({
						message: "'.__('Thank you for your order. We are now redirecting you to Tap Payment Gateway to make payment.', 'kdc').'",
						overlayCSS: {
							background		: "#fff",
							opacity			: 0.6
						},
						css: {
							padding			: 20,
							textAlign		: "center",
							color			: "#555",
							border			: "3px solid #aaa",
							backgroundColor	: "#fff",
							cursor			: "wait",
							lineHeight		: "32px"
						}
					});
					jQuery("#submit_tap_payment_form").click();});
					</script>
				</form>';
		}
		/**
		* Process the payment and return the result
		**/
		function process_payment($order_id){
			global $woocommerce;
			$order = new WC_Order( $order_id );
			
			if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '>=' ) ) {
				/* 2.1.0 */
				$checkout_payment_url = $order->get_checkout_payment_url( true );
			} else {
				/* 2.0.0 */
				$checkout_payment_url = get_permalink( get_option ( 'woocommerce_pay_page_id' ) );
			}

			return array(
				'result' => 'success', 
				'redirect' => add_query_arg(
					'order', 
					$order->id, 
					add_query_arg(
						'key', 
						$order->order_key, 
						$checkout_payment_url						
					)
				)
        	);
		}
		/**
		* Check for valid Tap server callback
		**/
		function check_tap_response(){
			global $woocommerce;
			if( isset($_REQUEST['trackid']) && isset($_REQUEST['ref']) ){
				$order_id = sanitize_text_field($_REQUEST['trackid']);
				if($order_id != ''){
					try{
						$order = new WC_Order( $order_id );
						$hash = sanitize_text_field($_REQUEST['hash']);
						$status = sanitize_text_field($_REQUEST['result']);
						$str = 'x_account_id'.$this->merchant_id.'x_ref'.sanitize_text_field($_REQUEST['ref']).'x_resultSUCCESSx_referenceid'.$order_id.'';
						$checkhash = hash_hmac('sha256', $str, $this->apikey);
						$transauthorised = false;
						
						if( $order->status !=='completed' ){
							if($hash == $checkhash){
								$status = strtolower($status);
								if($status=="success"){
									$transauthorised = true;
									$this->msg['message'] = "Thank you for shopping with us. Your account has been charged and your transaction is successful.";
									$this->msg['class'] = 'woocommerce-message';
									if($order->status == 'processing'){
										$order->add_order_note('Tap ID: '.sanitize_text_field($_REQUEST['ref']).' ('.sanitize_text_field($_REQUEST['trackid']).')<br/>Payment Type: '.sanitize_text_field($_REQUEST['crdtype']).'<br/>Payment Ref: '.sanitize_text_field($_REQUEST['payid']));
									}else{
										$order->payment_complete();
										$order->add_order_note('Tap payment successful.<br/>Tap ID: '.sanitize_text_field($_REQUEST['ref']).' ('.sanitize_text_field($_REQUEST['trackid']).')<br/>Payment Type: '.sanitize_text_field($_REQUEST['crdtype']).'<br/>Payment Ref: '.sanitize_text_field($_REQUEST['payid']));
										$woocommerce -> cart -> empty_cart();
									}
								}else if($status=="pending"){
									$this->msg['message'] = "Thank you for shopping with us. Right now your payment status is pending. We will keep you posted regarding the status of your order through eMail";
									$this->msg['class'] = 'woocommerce-info';
									$order->add_order_note('Tap payment status is pending<br/>Tap ID: '.sanitize_text_field($_REQUEST['ref']).' ('.sanitize_text_field($_REQUEST['trackid']).')<br/>Payment Type: '.sanitize_text_field($_REQUEST['crdtype']).'<br/>Payment Ref: '.sanitize_text_field($_REQUEST['payid']));
									$order->update_status('on-hold');
									$woocommerce -> cart -> empty_cart();
								}else{
									$this->msg['class'] = 'woocommerce-error';
									$this->msg['message'] = "Thank you for shopping with us. However, the transaction has been declined.";
									$order->add_order_note('Transaction ERROR: '.sanitize_text_field($_REQUEST['error']).'<br/>Tap ID: '.sanitize_text_field($_REQUEST['ref']).' ('.sanitize_text_field($_REQUEST['trackid']).')');
								}
							}else{
								$this->msg['class'] = 'error';
								$this->msg['message'] = "Security Error. Illegal access detected.";
							}
							if($transauthorised==false){
								$order->update_status('failed');
							}
							//removed for WooCOmmerce 2.0
							//add_action('the_content', array(&$this, 'tap_showMessage'));
						}
					}catch(Exception $e){
                        // $errorOccurred = true;
                        $msg = "Error";
					}
				}

                $redirect_url = ($this->redirect_page_id=="" || $this->redirect_page_id==0)?get_site_url() . "/":get_permalink($this->redirect_page_id);
                //For wooCoomerce 2.0
                $redirect_url = add_query_arg( array('msg'=> urlencode($this->msg['message']), 'type'=>$this->msg['class']), $redirect_url );

                wp_redirect( $redirect_url );
                exit;

			}
		
		}
		
		/*
        //Removed For WooCommerce 2.0
		function tap_showMessage($content){
			return '<div class="box '.$this->msg['class'].'">'.$this->msg['message'].'</div>'.$content;
		}
		*/
		
		// get all pages
		function tap_get_pages($title = false, $indent = true) {
			$wp_pages = get_pages('sort_column=menu_order');
			$page_list = array();
			if ($title) $page_list[] = $title;
			foreach ($wp_pages as $page) {
				$prefix = '';
				// show indented child pages?
				if ($indent) {
                	$has_parent = $page->post_parent;
                	while($has_parent) {
                    	$prefix .=  ' - ';
                    	$next_page = get_post($has_parent);
                    	$has_parent = $next_page->post_parent;
                	}
            	}
            	// add to page list array array
            	$page_list[$page->ID] = $prefix . $page->post_title;
        	}
        	return $page_list;
    		}
		}
		/**
		* Add the Gateway to WooCommerce
		**/
		function woocommerce_add_tap_gateway($methods) {
			$methods[] = 'WC_tap';
			return $methods;
		}

		add_filter('woocommerce_payment_gateways', 'woocommerce_add_tap_gateway' );
	}
