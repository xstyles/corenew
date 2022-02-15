<?php
// use function PHPSTORM_META\map;

class RepeaterFormStudentPurchase
{
    /**
     * The single instance of this class
     * @var RepeaterFormStudentPurchase
     */
    private static $instance = null;

    /**
     * Returns the single instance of the main plugin class.
     *
     * @return RepeaterFormStudentPurchase
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

    private $repeater_field_id = 1000;
    private $repeater_field_subject_id = 1030;
    private $repeater_field_student_email_id = 1040;

    public function __construct()
    {
        // Filters and Hooks for the Repeater Form with sub form (id 10) and repeater form (id 11)
        add_filter('gform_form_post_get_meta_' . $this->repeater_form_id, [$this, 'add_repeaters_to_form']);
        add_filter('gform_form_args', [$this,  'prepopulate_repeater_field_values']);
        add_filter(
            'gform_field_content_' . $this->repeater_form_id . '_' . $this->repeater_field_subject_id, 
            [$this, 'customize_subjects_field_label'], 
            10, 
            2
        );
        // add_filter('gform_validation_' . $this->repeater_form_id, [$this, 'return_validation'],10,1);
        add_action('gform_after_submission_' . $this->repeater_form_id, [$this,  'add_subject_to_cart_per_student']);
    }

    public function customize_subjects_field_label($field_content, $field)
    {
        $children = $this->_get_children_of_current_user();
        $repeater_index = $field->get_context_property('itemIndex');

        $field_content = str_replace($field->label, 'Choose a course for ' . $children[$repeater_index]->get('display_name'), $field_content);

        return $field_content;
    }

    public function prepopulate_repeater_field_values($args)
    {
        $children = $this->_get_children_of_current_user();

        $repeater_values = [];

        foreach ($children as $child) {
            $repeater_values['student_email'][] = $child->get('user_email');
            $repeater_values['subject'][] = '';
        }

        $args['field_values'] = $repeater_values;

        return $args;
    }

    public function add_repeaters_to_form($form)
    {
        $children = $this->_get_children_of_current_user();
        $count = count($children);

        $repeater = GF_Fields::create([
            'type'              => 'repeater',
            'allowsPrepopulate' => true,
            'description'      => 'Please select a subject course for each child',
            'id'               => $this->repeater_field_id, // The Field ID must be unique on the form
            'formId'           => $form['id'],
            'label'            => 'Team Members',
            'addButtonText'    => 'Add team member', // Optional
            'removeButtonText' => 'Remove team member', // Optional
            'maxItems'         => $count, // Optional
            'pageNumber'       => 1, // Ensure this is correct
            //   'fields'           => array($students, $subscriptions), // Add the fields here.
        ]);

        $fieldset_form = GFAPI::get_form($this->student_fieldset_form_id);

        $fields = [];

        foreach ($fieldset_form['fields'] as &$field) {
            $new_field = clone $field;

            if ($new_field->id == '3') {
                $subjects = $this->_get_subject_subscription_products();
                $new_field->choices = $subjects;
                $new_field->inputName = 'subject';
                $new_field->id = $this->repeater_field_subject_id;
            }

            if ($new_field->id == '11') {
                $new_field->allowsPrepopulate = true;
                $new_field->inputName = 'student_email';
                $new_field->id = $this->repeater_field_student_email_id;
            }

            $new_field->formId     = $form['id'];

            $fields[] = $new_field;
        }

        $repeater->fields = $fields;

        $form['fields'][] = $repeater;

        return $form;
    }

    public function add_subject_to_cart_per_student($form_data)
    {
        // var_dump($form_data);
        $data = $form_data[$this->repeater_field_id];

        $redirect_to_checkout = false;

        foreach ($data as $index => $students_info) {
            if (!empty($students_info[$this->repeater_field_subject_id])) {
                $redirect_to_checkout = true;

                WC()->cart->add_to_cart($students_info[$this->repeater_field_subject_id], 1, 0, [], [
                    'wcsg_gift_recipients_email' => $students_info[$this->repeater_field_student_email_id],
                ]);
            }
        }

        if ($redirect_to_checkout) {
            // Redirect to Checkout for payment
            wp_safe_redirect(wc_get_checkout_url());
        }
    }

//     public function return_validation($validation_result)
//     {
//         $form = $validation_result['form'];

//         foreach($form['fields'] as &$fields){

//             if(rgpost('input_') == null)

//             $validation['is_valid'] = false;

//             foreach ($form as &$field) {
                
            
//                 if (empty($form['fields'])) {
//                 $form->failed_validation = true;
//                 $form->validation_message = 'Please fill out atleast one field';
//                 break;
//                 }
//             }
//         }
        
//         $validation['form'] = $form;
//     }

//     private function _get_children_of_current_user()
//     {
//         return getChildrenOfUser(get_current_user_id());
//     }

//     private function _get_subject_subscription_products()
//     {
//         return getSubjectSubscriptionProducts();
//     }
// }

new RepeaterFormStudentPurchase();
