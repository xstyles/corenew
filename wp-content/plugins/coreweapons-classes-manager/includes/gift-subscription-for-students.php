<?php

class GiftSubscriptionForStudents
{
  /**
   * The single instance of this class
   * @var GiftSubscriptionForStudents
   */
  private static $instance = null;

  /**
   * Returns the single instance of the main plugin class.
   *
   * @return GiftSubscriptionForStudents
   */
  public static function instance()
  {
    if (is_null(self::$instance)) {
      self::$instance = new self;
    }

    return self::$instance;
  }

  private $membershipTypeField = 'input_15';
  private $individualMembershipProductField = 'input_20';
  private $studentMembershipProductField = 'input_20';


  public function __construct()
  {
    $formId = '2';
    $S_formId ='3';
    add_action('gform_after_submission_' . $formId, [$this, 'addSubToCartPerStudent'], 10, 1);
    // add_action('gform_after_submission_' . $S_formId, [$this, 'getstudententries'],10,1);
    // add_filter('gform_validation_'. $formId, [$this, 'addFilter'], 10, 1);
    // add_filter('woocommerce_add_to_cart_redirect',[$this, 'addFilter']);
  }  



  public function addSubToCartPerStudent($result)
  {
    // Get the value of field ID 1
    $recipient = rgpost('input_17');
    $product_id = rgpost('input_20');
    $entryIds = rgpost('input_26');

    $studentEntries = [];

    foreach (explode(',', $entryIds) as $key => $value) {
      $entryId = $value;
      $studentEntry = GFAPI::get_entry($entryId);

      $obj = [
        'email' => rgar($studentEntry, 'input_18'),
        'productId' => rgar($studentEntry, 'input_17'),
      ];

      $studentEntries[] = $obj;
    }


    
   
    var_dump($result);
    echo $result;
    global $woocommerce;
   
    // If no product found, short-circuit
    if (is_null($product_id) || $product_id == "") return;
    
    // If this is an individual, then just add membership product to cart and send to checkout page
    if ($this->_isIndividual($result)) {
      WC()->cart->add_to_cart($product_id, 1);
      return wp_safe_redirect( wc_get_checkout_url() );
    }

    // If we're here, then the user is a parent and has children to gift subscriptions to.
    // Proceed to subscription product as gift to students
    // TODO: 1. Get student information from $result
    // TODO: 2. Loop the lines below for each student retrieved from $result

    // $students_count = 0; // Step 1. above

    // for ($i=0; $i < $students_count; $i++) { 
      // $item_key = WC()->cart->add_to_cart($product_id, 1);
      // $item = WC()->cart->get_cart_item($item_key);
  
      // $new_recipient_data = [
      //   'email' => $recipient,
      // ];
  
      // WCS_Gifting::update_cart_item_key( $item, $item_key, $recipient );
    // }
    
    // wp_safe_redirect( wc_get_checkout_url() );
    
    
  }

  private function _isIndividual($formData)
  {
    return $formData[$this->membershipTypeField] == 'individual';
  }

  private function _isParent($formData)
  {
    return $formData[$this->membershipTypeField] == 'parent';
  }
}

// add_action('plugins_loaded', ['GiftSubscriptionForStudents', 'instance']);
new GiftSubscriptionForStudents();
