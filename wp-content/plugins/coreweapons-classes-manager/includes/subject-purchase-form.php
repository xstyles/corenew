<?php
// use function PHPSTORM_META\map;

class SubjectPurchaseForm
{
    /**
     * The single instance of this class
     * @var SubjectPurchaseForm
     */
    private static $instance = null;

    /**
     * Returns the single instance of the main plugin class.
     *
     * @return SubjectPurchaseForm
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private $student_fieldset_form_id = '10';
    private $repeater_form_id = '11';

    public function __construct()
    {
        // Filters and Hooks for the Repeater Form with sub form (id 10) and repeater form (id 11)
        // add_filter('gform_form_post_get_meta_' . $this->repeater_form_id, [$this, 'add_repeaters_to_form']);
        // add_filter('gform_pre_render_' . $this->repeater_form_id, [$this,  'prerender_fields'], 10, 3);
        // add_filter('gform_form_args', [$this,  'prepopulate_repeater_field_values']);
        // add_action('gform_after_submission_' . $this->repeater_form_id, [$this,  'add_subject_to_cart_per_student']);


        // filter and hooks for form id 12 (Subject Purchase form with checkboxes)
        add_filter('gform_pre_render_12', [$this, 'populate_students_checkboxes'], 10, 1);
        add_action('gform_after_submission_12', [$this, 'add_subjects_to_cart'], 10, 1);
    }

    public function populate_students_checkboxes($form)
    {
        $children = $this->_get_children_of_current_user();

        $children_options = array_map(function ($child) {
            return [
                'text' => $child->get('display_name'),
                'value' => $child->get('user_email')
            ];
        }, $children);

        foreach ($form['fields'] as &$field) {
            if ($field->id == '7' || $field->id == '4' || $field->id == '9') {
                $field->allowsPrepopulate = true;
                $field->choices = $children_options;
            }
        }

        return $form;
    }

    public function add_subjects_to_cart($entry, $form)
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

    // public function prerender_fields($form, $ajax, $field_values)
    // {
    //     $chilren = $this->_get_children_of_current_user();

    //     foreach ($form['fields'] as $field) {
    //     }


    //     return $form;
    // }

    // public function prepopulate_repeater_field_values($args)
    // {
    //     $children = $this->_get_children_of_current_user();

    //     $repeater_values = [];

    //     foreach ($children as $child) {
    //         $repeater_values['student_email'][] = $child->get('user_email');
    //         $repeater_values['student_name'][] = $child->get('display_name');
    //         $repeater_values['student_name_html'][] = $child->get('display_name');
    //         $repeater_values['subject'][] = '';
    //     }

    //     $args['field_values'] = $repeater_values;

    //     return $args;
    // }

    // public function add_repeaters_to_form($form)
    // {
    //     $children = $this->_get_children_of_current_user();
    //     $count = count($children);

    //     $repeater = GF_Fields::create([
    //         'type'              => 'repeater',
    //         'allowsPrepopulate' => true,
    //         'description'      => 'Please select a subject course for each child',
    //         'id'               => 1000, // The Field ID must be unique on the form
    //         'formId'           => $form['id'],
    //         'label'            => 'Team Members',
    //         'addButtonText'    => 'Add team member', // Optional
    //         'removeButtonText' => 'Remove team member', // Optional
    //         'maxItems'         => $count, // Optional
    //         'pageNumber'       => 1, // Ensure this is correct
    //         //   'fields'           => array($students, $subscriptions), // Add the fields here.
    //     ]);

    //     $fieldset_form = GFAPI::get_form($this->student_fieldset_form_id);

    //     $fields = [];

    //     foreach ($fieldset_form['fields'] as &$field) {
    //         $new_field = clone $field;

    //         if ($new_field->id == '5') {
    //             $new_field->allowsPrepopulate = true;
    //             $new_field->inputName = 'student_name';
    //         }

    //         if ($new_field->id == '10') {
    //             $new_field->allowsPrepopulate = true;
    //             $new_field->inputName = 'student_name_html';
    //         }

    //         if ($new_field->id == '11') {
    //             $new_field->allowsPrepopulate = true;
    //             $new_field->inputName = 'student_email';
    //             // $new_field->visibility = 'hidden';
    //         }

    //         if ($new_field->id == '3') {
    //             $subjects = $this->_get_subject_subscription_products();
    //             $new_field->choices = $subjects;
    //             $new_field->inputName = 'subject';
    //         }

    //         $new_field->id         = $field->id + 1000;
    //         $new_field->formId     = $form['id'];

    //         $fields[] = $new_field;
    //     }

    //     $repeater->fields = $fields;

    //     $form['fields'][] = $repeater;

    //     return $form;
    // }

    private function _get_children_of_current_user()
    {
        return getChildrenOfUser(get_current_user_id());
    }

    private function _get_subject_subscription_products()
    {
        return getSubjectSubscriptionProducts();
    }
}

new SubjectPurchaseForm();
