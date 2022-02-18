<?php

class CW_Field_Repeater extends GF_Field_Repeater
{
    function __construct() {
        parent::__construct();
    }

    /**
     * Returns the markup for the sub field.
     *
     * @since 2.4
     *
     * @param GF_Field     $field
     * @param string|array $value
     * @param array        $form
     * @param array        $entry
     *
     * @return string
     */
    public function get_sub_field_content($field, $value, $form, $entry)
    {

        $validation_status = $field->failed_validation;

        if (empty($field->fields)) {
            // Validation will be handled later inside GF_Field_Repeater::get_sub_field_input so temporarily set failed_validation to false.
            $field->failed_validation = false;
        }

        if (!class_exists('GFFormDisplay')) {
            require_once(GFCommon::get_base_path() . '/form_display.php');
        }

        $field_content = $this->get_field_content($field, $value, true, $form['id'], $form);

        $field->failed_validation = $validation_status;

        return $field_content;
    }

    /**
	 * @param GF_Field  	$field
	 * @param string 		$value
	 * @param bool   		$force_frontend_label
	 * @param int   		$form_id
	 * @param null|array   	$form
	 *
	 * @return string
	 */
	public function get_field_display_content( $field, $value = '', $force_frontend_label = false, $form_id = 0, $form = null ) {

		$field_label   = $field->get_field_label( $force_frontend_label, $value );
		$admin_buttons = $field->get_admin_buttons();

		$input_type = GFFormsModel::get_input_type( $field );

		$is_form_editor  = GFCommon::is_form_editor();
		$is_entry_detail = GFCommon::is_entry_detail();
		$is_admin        = $is_form_editor || $is_entry_detail;

		if ( $input_type == 'adminonly_hidden' ) {
			$field_content = ! $is_admin ? '{FIELD}' : sprintf( "%s<label class='gfield_label' >%s</label>{FIELD}", $admin_buttons, esc_html( $field_label ) );
		} else {
			$field_content = $field->get_field_content( $value, $force_frontend_label, $form );
		}

		$value = $field->get_value_default_if_empty( $value );

		$field_content = str_replace( '{FIELD}', GFCommon::get_field_input( $field, $value, 0, $form_id, $form ), $field_content );

		$field_content = gf_apply_filters( array( 'gform_field_content', $form_id, $field->id ), $field_content, $field, $value, 0, $form_id );

		return $field_content;
	}
}
