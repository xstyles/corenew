
<?php 

use function PHPSTORM_META\map;

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

  /**
   * TODO: 
   * 1. replace 2 dropdowns from form id 5 with 1 text input and 1 (image) radio button input
   * 2. loop over each child and create duplicates of form (id: 5) inputs:
   *     2.1 set text input to child email address, 
   *     2.2 set radio button label to include child name `Choose a course for {student}`
   * 3. use the fields from step 2 in new form
   * 4. add afterSubmit hook for new form that adds recipients for each product chosen
   *    use the following to add to cart with recipient email address:
   *    WC()->cart->add_to_cart($student['product_id'], 1, 0, [], [
   *      'wcsg_gift_recipients_email' => $student['email'],
   *    ]);
   */

  private $membershipTypeField = 'input_15';
  private $individualMembershipProductField = 'input_20';
  private $studentMembershipProductField = 'input_20';

  private $registration_form_id = '2';
  private $S_formId = '3';
  private $subject_purchase_subform_id = '5';
  private $subject_purchase_form_id = '7';

  public function __construct()
  {
    add_action('gform_after_submission_' . $this->registration_form_id, [$this, 'addSubToCartPerStudent'], 10, 1);
    // add_action('gform_after_submission_' . $this->S_formId, [$this, 'getstudententries'],10,1);
    // add_filter('gform_validation_'. $this->registration_form_id, [$this, 'addFilter'], 10, 1);
    // add_filter('woocommerce_add_to_cart_redirect',[$this, 'addFilter']);
    // add_filter('gform_pre_render_' . $this->subject_purchase_subform_id, [$this, 'populateStudentsField'],9,1);
    // add_filter('gform_pre_render_' . $this->subject_purchase_subform_id, [$this, 'add_readonly_script'],10,1);
    // // add_filter('gform_pre_render_5', 'add_readonly_script');
    // add_filter('gform_pre_render_' . $this->subject_purchase_form_id, [$this, 'populateSubjectPurchaseFormForParent']);
    
  }



  public function populateStudentsField($form)
  {
    $children = $this->getChildrenOfCurrentUser();

    foreach ($form['fields'] as &$field) {
      $studentsFieldId = '16';
      $studentDisplaynameFieldId = '17';
      $field_value = 'helo';

      if ($field->id == $studentsFieldId) {
        $field->choices = $children;
      }

      if ($field->id == $studentDisplaynameFieldId) {
        $field->value = $field_value;
        $field->size = "small";
        $field->label = "Student";
        $field->input = "student";
        $field->inputName = "Fodod";
        $field->placeholder = $field_value;
        $field->defaultValue = "StudentName";
      }
    }

    return $form;
  }

  public function populateSubjectPurchaseFormForParent($form)
  {
    $repeater = GF_Fields::create(array(
      'type'             => 'repeater',
      'description'      => 'Please select a subject course for each child',
      'id'               => 1000, // The Field ID must be unique on the form
      'formId'           => $form['id'],
      'label'            => 'Team Members',
      'addButtonText'    => 'Add team member', // Optional
      'removeButtonText' => 'Remove team member', // Optional
      'maxItems'         => 3, // Optional
      'pageNumber'       => 1, // Ensure this is correct
      // 'fields'           => array($students, $subscriptions), // Add the fields here.
    ));

    // $field_student_name = GF_Fields::create()
    $origForm = GFAPI::get_form($this->subject_purchase_subform_id);
    add_filter('gform_field_value_student_display_name', function($value) {
      return 'Hello world!';
    });
    foreach ($origForm['fields'] as $field) {
      if ($field->id == '16') {
        $children = $this->getChildrenOfCurrentUser();
        $field->choices = $children;
      }

      if ($field->id == '6') {
        $subjects = $this->getSubjectSubscriptionProducts();
        $field->choices = $subjects;
      }

      $field->id         = $field->id + 1000;
      $field->formId     = $form['id'];

      if (is_array($field->inputs)) {
        foreach ($field->inputs as &$input) {
          $input['id'] = (string) ($input['id'] + 1000);
        }
      }
    }




    $repeater->fields = $origForm['fields'];

    $form['fields'][] = $repeater;

    return $form;
  }

  public function addSubToCartPerStudent($result)
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

  private function getChildrenOfCurrentUser()
  {
    $post_status = ['wc-active'];

    if (WP_DEBUG) {
      $post_status = ['wc-processing', 'wc-active'];
    }

    // $customerOrders = wc_get_orders([
    $customerOrders = WCS_Gifting::get_gifted_subscriptions([
      'customer_id' => get_current_user_id(),
      'post_status' => $post_status,
    ]);


    $children = array_map(function ($order) {
      $targetKey = '_recipient_user';
      $metaData = $order->get_meta_data();

      if (count($metaData)) {
        // Loop over meta_data objects
        for ($i = 0; $i < count($metaData); $i++) {
          $data = $metaData[$i]->get_data();

          if ($data['key'] == $targetKey) {
            $user = get_user_by('id', $data['value']);
            return [
              'text' => $user->get('display_name'),
              'value' => $data['value'],
            ];
          }
        }
      }
    }, $customerOrders);

    return $children;
  }

  private function getSubjectSubscriptionProducts()
  {
    $subscriptions = wc_get_products([
      'type' => 'subscription',
      'category' => 'subjects',
    ]);

    $subjects = array_map(function ($subscription) {
      return [
        'text' => $subscription->get_name(),
        'value' => $subscription->get_id(),
      ];
    }, $subscriptions);

    return $subjects;
  }
}

// add_action('plugins_loaded', ['GiftSubscriptionForStudents', 'instance']);
new GiftSubscriptionForStudents();
