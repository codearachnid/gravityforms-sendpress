<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

class GF_Field_Sendpress extends GF_Field {

	public $type = 'emailsignup';

	public function get_form_editor_field_title() {
		return esc_attr__( 'Email Signup', 'gravityforms' );
	}

	public function is_conditional_logic_supported(){
		return true;
	}

	function get_form_editor_field_settings() {
		return array(
			'prepopulate_field_setting',
			'label_setting',
      'description_setting',
      'visibility_setting',
      'admin_label_setting',
      'label_placement_setting'
		);
	}

	public function get_field_input( $form, $value = '', $entry = null ) {
		$form_id         = $form['id'];
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();

		$id       = (int) $this->id;
		$field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";

		$disabled_text = $is_form_editor ? 'disabled="disabled"' : '';
		$checked = $value == 'signup-confirmed' ? "checked='checked'" : '';

    $description   = strip_tags($this->get_description( $this->description, 'gsection_description' ));
		return sprintf( "<input name='input_%d' id='%s' type='checkbox' value='signup-confirmed' {$checked} {$disabled_text} /> <label for='%s' id='label_%d'>%s</label>", $id, $field_id, $field_id, $id, $description );
	}

	public function get_field_content( $value, $force_frontend_label, $form ) {
		$form_id         = $form['id'];
		$admin_buttons   = $this->get_admin_buttons();
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();
		$is_admin        = $is_entry_detail || $is_form_editor;
		$field_label     = $this->get_field_label( $force_frontend_label, $value );
		$field_id        = $is_admin || $form_id == 0 ? "input_{$this->id}" : 'input_' . $form_id . "_{$this->id}";
		$field_content   = ! $is_admin ? '{FIELD}' : $field_content = sprintf( "%s<label class='gfield_label' for='%s'>%s</label>{FIELD}", $admin_buttons, $field_id, esc_html( $field_label ) );

		return $field_content;
	}


	// $subscriberID = SendPress_Data::add_subscriber(array('firstname' => $first,'lastname' => $last,'email' => $email, 'phonenumber' => $phonenumber, 'salutation' => $salutation));


	// # FIELD FILTER UI HELPERS ---------------------------------------------------------------------------------------

	/**
	 * Returns the filter operators for the current field.
	 *
	 * @since 2.4
	 *
	 * @return array
	 */
	public function get_filter_operators() {
		$operators   = parent::get_filter_operators();
		$operators[] = 'contains';

		return $operators;
	}

}

GF_Fields::register( new GF_Field_Sendpress() );
