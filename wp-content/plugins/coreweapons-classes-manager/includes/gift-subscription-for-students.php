
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

  private $membershipTypeField = '15';
  private $individualMembershipProductField = 'input_20';
  private $studentMembershipProductField = 'input_20';

  private $registration_form_id = '2';
  private $S_formId = '3';
  private $subject_purchase_subform_id = '5';
  private $subject_purchase_form_id = '8';

  private $recipient_email_field = '17';
  private $subject_field = '18';

  public function __construct()
  {
    // Membership purchase form hooks
    add_action('gform_after_submission_' . $this->registration_form_id, [$this, 'addMembershipToCart'], 10, 1);

    // Subject purchase form hooks
    add_filter('gform_pre_render_' . $this->subject_purchase_form_id, [$this, 'populateSubjectPurchaseFormForParent']);
    // add_filter('gform_form_update_meta_' . $this->subject_purchase_form_id, [$this, 'truncateSubjectPurchaseFormFields'], 10, 3);
    // add_filter('gform_pre_submission_filter_' . $this->subject_purchase_form_id, [$this, 'truncateSubjectPurchaseFormFields'], 10, 3);
    add_action('gform_after_submission_' . $this->subject_purchase_form_id, [$this, 'addCourseToCart'], 10, 1);

    // 
    // TESTING AREA
    // 
    // Setting column1 to dropdown for emails
    add_filter( 'gform_column_input_6_22_1', [$this, 'set_column1'], 10, 5 );

    // Setting column2 to dropdown for products
    add_filter( 'gform_column_input_6_22_2', [$this, 'set_column2'], 10, 5 );
    add_filter( 'gform_column_input_content_6_22_2', [$this,'change_column2_content'], 10, 6 );

    add_action('gform_after_submission_6', [$this, 'check_results']);

  }

  function set_column1( $input_info, $field, $column, $value, $form_id ) {
    // return array( 'type' => 'select', 'choices' => 'First Choice,Second Choice' );
    return array( 
      'type' => 'select', 
      // 'choices' => 'First Choice,Second Choice' 
      'choices' => [
        [
          'text' => 'First Choice',
          'value' => 'userEmail1',
        ],
        [
          'text' => 'Second Choice',
          'value' => 'userEmail2',
        ],
      ],
    );
  }

  function set_column2( $input_info, $field, $column, $value, $form_id ) {
    // return array( 'type' => 'select', 'choices' => 'First Choice,Second Choice' );
    return array( 
      'type' => 'select', 
      // 'choices' => 'First Choice,Second Choice' 
      'choices' => [
        [
          'text' => 'First Product',
          'value' => 'product_1',
        ],
        [
          'text' => 'Second Product',
          'value' => 'product_2',
        ],
      ],
    );
  }

  function change_column2_content( $input, $input_info, $field, $text, $value, $form_id ) {
    //build field name, must match List field syntax to be processed correctly
    $input_field_name = 'input_' . $field->id . '[]';
    $tabindex = GFCommon::get_tabindex();
    // $new_input = '<textarea name="' . $input_field_name . '" ' . $tabindex . ' class="textarea medium" cols="50" rows="10">' . $value . '</textarea>';
    
    $new_input = '';
    foreach ($input_info['choices'] as $key => $value) {
      $new_input .= '<input type="radio" name="' . $input_field_name . '" ' . $tabindex . ' value="' . $value['value'] . '/>' . '<label for="' . $input_field_name . '">' . $value['text'] . '</label>';
    }

    $new_input = '<div class="custom-radio-yay">' . $new_input . '</div>';

    return $new_input;
  }

  public function check_results ($result)
  {
      $res = unserialize($result[1000][0][22]);
      
      return $result;
  }

  public function populateStudentsField($form)
  {
    $children = $this->getChildrenOfCurrentUser();

    foreach ($form['fields'] as &$field) {
      $studentsFieldId = '16';
      $studentDisplaynameFieldId = '17';

      if ($field->id == $studentsFieldId) {
        $field->choices = $children;
      }

      if ($field->id == $studentDisplaynameFieldId) {
        $field->label = "Student";
        $field->input = "student";
        $field->defaultValue = "StudentName";
      }
    }

    return $form;
  }

  public function populateSubjectPurchaseFormForParent($form)
  {
    // $field_student_name = GF_Fields::create()
    $origForm = GFAPI::get_form($this->subject_purchase_subform_id);
    $children = $this->getChildrenOfCurrentUser();
    $newFields = [];

    $form['field_map'] = [];
    $fieldDataType = null;

    foreach ($children as $index => $child) {
      foreach ($origForm['fields'] as $key => &$field) {
        $newField = clone $field;

        if ($field->id == $this->recipient_email_field) {
          $newField->visibility = 'hidden';
          $newField->allowsPrepopulate = true;
          $newField->defaultValue = $child->get('user_email');
          $fieldDataType = 'email';
        }

        if ($field->id == $this->subject_field) {
          $subjects = $this->getSubjectSubscriptionProducts();
          $childname = $child->get('display_name');
          $newField->label = "Choose a course for $childname";
          $newField->choices = $subjects;
          $fieldDataType = 'product';
        }

        $newField->id         = $field->id + 1000 + $index;
        // $newField->id         = (int)($newField->id . '00' . );
        $newField->formId     = $form['id'];

        $newFields[] = $newField;

        // array_push($form['field_map'], [
        //   'id' => $newField->id,
        //   'type' => $fieldDataType,
        //   'child_id' => $child->get('ID'),
        // ]);
      }
    }

    // array_push($form['fields'], ...$newFields);
    $form['fields'] = $newFields;

    // Save modified form object
    // https://docs.gravityforms.com/how-to-add-field-to-form-using-gfapi/#save-the-modified-form-object-
    GFAPI::update_form($form);

    return $form;
  }

  public function truncateSubjectPurchaseFormFields($form)
  {
    // if ($meta_name == 'display_meta') {
    //   $form['fields'] = [];
    // }
    $form['fields'] = [];
    // GFAPI::update_form($form);

    return $form;
  }

  public function addCourseToCart($form)
  {
    $studentsWithProduct = [];

    foreach ($form['fields'] as $key => $value) {
      if (str_starts_with($key, $this->subject_field)) {
        if (!empty($value)) {
          $suffix = str_replace($this->subject_field, '', $key);
          $email = $form['fields'][$this->recipient_email_field . '__' . $suffix];

          $studentsWithProduct = [
            'email' => $email,
            'product_id' => $value,
          ];
        }
      }
      $students_info[] = $studentsWithProduct;
    }

    for ($i = 0; $i < count($studentsWithProduct); $i++) {
      $student = $studentsWithProduct[$i];

      // TODO: Move this validation of recipient email to form-validation hook. It must be triggered before submit happens
      $isValid = WCS_Gifting::validate_recipient_emails([$student['email']]);

      if (!$isValid) throw new Error('Invalid email address. Please try a different email address.');

      $item_key = WC()->cart->add_to_cart($students_info['product_id'], 1, 0, [], [
        'wcsg_gift_recipients_email' => $students_info['email'],
      ]);
    }



    // Redirect to Checkout for payment
    wp_safe_redirect(wc_get_checkout_url());
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
            return $user;
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
