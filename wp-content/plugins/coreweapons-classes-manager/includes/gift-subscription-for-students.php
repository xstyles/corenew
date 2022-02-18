
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

  private $registration_form_id = '2';
  private $S_formId = '3';
  private $subject_purchase_subform_id = '5';
  private $subject_purchase_form_id = '8';
  private $list_form_id = '9';

  private $recipient_email_field = '17';
  private $subject_field = '18';

  public function __construct()
  {
    // Subject purchase form hooks
    // add_filter('gform_pre_render_' . $this->subject_purchase_form_id, [$this, 'populateSubjectPurchaseFormForParent']);
    // add_action('gform_after_submission_' . $this->subject_purchase_form_id, [$this, 'addCourseToCart'], 10, 1);

    //List column testing form for student purchase hooks

    // 
    // TESTING AREA
    // 
    // Setting column1 to dropdown for emails
    add_filter( 'gform_column_input_' . $this->list_form_id . '_1_1', [$this, 'set_column1'], 10, 5 );

    // Setting column2 to dropdown for products
    add_filter( 'gform_column_input_' . $this->list_form_id . '_1_2', [$this, 'set_column2'], 10, 5 );
    add_filter( 'gform_column_input_content_' . $this->list_form_id . '_1_2', [$this,'change_column2_content'], 10, 6 );
    add_filter( 'gform_field_validation_' . $this->list_form_id . '_1', [$this, 'validate_inputs'], 10, 4);
    // add_action('gform_after_submission_' . $this->list_form_id, [$this, 'check_results']);
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
    $tabindex = GFCommon::get_tabindex();
    // $new_input = '<textarea name="' . $input_field_name . '" ' . $tabindex . ' class="textarea medium" cols="50" rows="10">' . $value . '</textarea>';
    
    $unique_id = str_replace( '-', '', wp_generate_uuid4());

    $new_input = '';
    foreach ($input_info['choices'] as $key => $value) {
      // $input_field_name = 'input_' . $field->id . $key ;
      $input_field_name = 'input_' . $unique_id;

      $new_input .= '<input type="radio" name="' . $input_field_name . '[]' . '" ' . $tabindex . ' value="' . $value['value'] . '/>' . '<label for="' . $input_field_name . '">' . $value['text'] . '</label>';
    }

    // $new_input = '<div class="custom-radio-yay">' . $new_input . '</div>';

    return $new_input;
  }

  public function validate_inputs($result, $value, $form, $field) {
    if ( $field->type == 'list' ) {
      foreach ($value as $row_values) {
        // $emailField = $row_values['']
        var_dump($row_values);
      }
    }

    return $result;
  }

  public function check_results ($result)
  {
      $res = unserialize($result[1000][0][1]);
      
      return $result;
  }

  public function populateSubjectPurchaseFormForParent($form)
  {
    // $field_student_name = GF_Fields::create()
    $origForm = GFAPI::get_form($this->subject_purchase_subform_id);
    $children = $this->_getChildrenOfCurrentUser();
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
          $subjects = $this->_getSubjectSubscriptionProducts();
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

  private function _getChildrenOfCurrentUser()
  {
    $post_status = ['wc-active'];

    if (WP_DEBUG) {
      $post_status = ['wc-processing', 'wc-active'];
    }

    // $customerOrders = wc_get_orders([
    $customerOrders = WCS_Gifting::get_gifted_subscriptions([
      'customer_id' => get_current_user_id(),
      'post_status' => $post_status,
      'type' => '',
    ]);

    // Entries that must not appear
    // foo7 : foo7@test.com : 534
    // foo10 : foo10@test.com : 535
    // f0011 : foo11@test.com : 536

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

  private function _getSubjectSubscriptionProducts()
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

  private function _filter_children_with_subscription($children, $product_id)
  {
    $filtered_children = [];

    foreach ($children as $child) {
      $has_sub = $this->_user_has_subscription($child->get('ID'), $product_id);
      if (!$has_sub) $filtered_children[] = $child;
    }

    return $filtered_children;
  }

  private function _user_has_subscription($user_id, $product_id)
  {
    $post_status = ['wc-active'];

    if (WP_DEBUG) {
      $post_status = ['wc-processing', 'wc-active'];
    }

    return wcs_user_has_subscription($user_id, $product_id, 'wc-active', $post_status);
  }
}


// add_action('plugins_loaded', ['GiftSubscriptionForStudents', 'instance']);
new GiftSubscriptionForStudents();
