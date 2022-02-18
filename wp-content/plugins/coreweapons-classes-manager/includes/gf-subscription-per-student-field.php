<?php

if (!class_exists('GF_Field')) return;

include 'functions.php';

class StudentSubscriptionInputField extends GF_Field
{
    public $type = 'student_subscription_input_field';

    public $choices = [
        ['text' => 'Food Choice 1'],
        ['text' => 'Food Choice 2'],
        ['text' => 'Food Choice 3'],
    ];

    private $delivery_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    public function get_form_editor_field_title()
    {
        return esc_attr__('Student Subscription Select', 'txtdomain');
    }

    public function get_form_editor_button()
    {
        return [
            'group' => 'advanced_fields',
            'text'  => $this->get_form_editor_field_title(),
        ];
    }

    public function get_form_editor_field_settings()
    {
        return [
            'label_setting',
            'choices_setting',
            'description_setting',
            'rules_setting',
            'error_message_setting',
            'css_class_setting',
            'conditional_logic_field_setting',
        ];
    }

    public function is_value_submission_array()
    {
        return true;
    }

    public function get_field_input($form, $value = '', $entry = null)
    {
        if ($this->is_form_editor()) {
            return '';
        }

        $id = (int) $this->id;

        if ($this->is_entry_detail()) {
            $table_value = maybe_unserialize($value);
        } else {
            $table_value = $this->translateValueArray($value);
        }

        $table = '<table class="input-table" style="border: none; border-collapse: collapsed;">';
        $table .= '<tbody>';
        // $table .= '<tr>';
        // $table .= '<th>' . __('Course', 'txtdomain') . '</th>';

        // foreach ($this->choices as $choice) {
        //     $table .= '<th>' . $day . '</th>';
        // }
        // $table .= '</tr>';

        $children = $this->_get_children_of_current_user();

        foreach ($children as $childIndex => $child) {
            $table .= '<tr>';

            // $table .= '<td style="width: 0; height: 0; position: relative; visibility: hidden; padding: 0; margin: 0;">';
            $table .= '<td>';
            $table .= '<input type="text" value="' . $child->get('user_email') . '" hidden aria-hidden />';
            $table .= '<label>' . $child->get('display_name') . '</label>';
            $table .= '</td>';


            foreach ($this->choices as $courseIndex => $course) {
                $table .= '<td>';
                $table .= '<input type="radio" name="input_' . $id . '_' . $childIndex . '[]" id="input_' . $id . '_' . $childIndex . '_' . $courseIndex . '" class="product-item" value="' . $course['value'] . '" />';
                $table .= '<label for="input_' . $id . '_' . $childIndex . '_' . $courseIndex . '">' . $course['text'] . '</label>';
                $table .= '</td>';
            }

            $table .= '</tr>';
        }

        $table .= '</tbody></table>';

        $table .= '<style type="text/css">';
        $table .= 'table.input-table,';
        $table .= 'table.input-table td,';
        $table .= 'table.input-table tr,';
        $table .= 'tr td {';
        $table .= 'border: none;';
        $table .= '}';
        $table .= '</style>';

        return $table;
    }

    private function translateValueArray($value)
    {
        if (empty($value)) {
            return [];
        }
        $table_value = [];
        $counter = 0;

        $children = $this->_get_children_of_current_user();

        foreach ($children as $child_index => $child) {
            foreach ($this->choices as $course) {
                $table_value[$child->get('user_email')][$course['text']] = $value[$counter++];
            }
        }
        return $table_value;
    }

    public function get_value_save_entry($value, $form, $input_name, $lead_id, $lead)
    {
        if (empty($value)) {
            $value = '';
        } else {
            $table_value = $this->translateValueArray($value);
            $value = serialize($table_value);
        }
        return $value;
    }

    private function prettyListOutput($value)
    {
        $str = '<ul>';
        foreach ($value as $course => $days) {
            $week = '';
            foreach ($days as $day => $delivery_number) {
                if (!empty($delivery_number)) {
                    $week .= '<li>' . $day . ': ' . $delivery_number . '</li>';
                }
            }
            // Only add week if there were any requests at all
            if (!empty($week)) {
                $str .= '<li><h3>' . $course . '</h3><ul class="days">' . $week . '</ul></li>';
            }
        }
        $str .= '</ul>';
        return $str;
    }

    public function get_value_entry_list($value, $entry, $field_id, $columns, $form)
    {
        return __('Enter details to see delivery details', 'txtdomain');
    }

    public function get_value_entry_detail($value, $currency = '', $use_text = false, $format = 'html', $media = 'screen')
    {
        $value = maybe_unserialize($value);
        if (empty($value)) {
            return $value;
        }
        $str = $this->prettyListOutput($value);
        return $str;
    }

    public function get_value_merge_tag($value, $input_id, $entry, $form, $modifier, $raw_value, $url_encode, $esc_html, $format, $nl2br)
    {
        return $this->prettyListOutput($value);
    }

    public function is_value_submission_empty($form_id)
    {
        $value = rgpost('input_' . $this->id);
        foreach ($value as $input) {
            if (strlen(trim($input)) > 0) {
                return false;
            }
        }
        return true;
    }

    // 
    // Private methods
    // 

    private function _get_children_of_current_user()
    {
        return getChildrenOfCurrentUser();
    }
}

GF_Fields::register(new StudentSubscriptionInputField());
