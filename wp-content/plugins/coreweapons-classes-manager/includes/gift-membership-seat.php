
<?php

use function PHPSTORM_META\map;

class GiftMembershipSeat
{
  /**
   * The single instance of this class
   * @var GiftMembershipSeat
   */
  private static $instance = null;

  /**
   * Returns the single instance of the main plugin class.
   *
   * @return GiftMembershipSeat
   */
  public static function instance()
  {
    if (is_null(self::$instance)) {
      self::$instance = new self;
    }

    return self::$instance;
  }

  private $membershipTypeField = '15';
  private $registration_form_id = '2';
  
  public function __construct()
  {
    // Membership purchase form hooks
    add_action('gform_after_submission_' . $this->registration_form_id, [$this, 'addMembershipToCart'], 10, 1);
  }

  public function addMembershipToCart($result)
  {
    // Get the value of field ID 1
    $recipient = rgpost('input_17');
    $product_id = rgpost('input_20'); // This field only appears for Individuals
    $entryIds = rgpost('input_26');


    // If this is an individual, then just add membership product to cart and send to checkout page
    if ($this->_isIndividual($result)) {
      // If no product found, short-circuit
      if (is_null($product_id) || $product_id == "") return;

      // Add product to user's cart
      WC()->cart->add_to_cart($product_id, 1);
    } else 
    if ($this->_isParent(($result))) {
      // If we're here, then the user is a parent and has children to gift subscriptions to.
      // Proceed to subscription product as gift to students
      $studentEntries = [];

      // $entryIds recieved here is a single (comma-concatenated) string
      // Use explode() to split the string by commas
      // Looping over each form entry, we pick the email and product_id from each form entry.
      foreach (explode(',', $entryIds) as $key => $value) {
        $entryId = $value;
        $studentEntry = GFAPI::get_entry($entryId);

        $obj = [
          'email' => rgar($studentEntry, '18'),
          'product_id' => rgar($studentEntry, '17'),
        ];

        $studentEntries[] = $obj;
      }

      for ($i = 0; $i < count($studentEntries); $i++) {
        $student = $studentEntries[$i];

        // TODO: Move this validation of recipient email to form-validation hook. It must be triggered before submit happens
        $isValid = WCS_Gifting::validate_recipient_emails([$student['email']]);

        if (!$isValid) throw new Error('Invalid email address. Please try a different email address.');

        $item_key = WC()->cart->add_to_cart($student['product_id'], 1, 0, [], [
          'wcsg_gift_recipients_email' => $student['email'],
        ]);
      }
    }

    // Redirect to Checkout for payment
    wp_safe_redirect(wc_get_checkout_url());
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

// add_action('plugins_loaded', ['GiftMembershipSeat', 'instance']);
new GiftMembershipSeat();
