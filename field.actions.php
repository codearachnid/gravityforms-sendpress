<?php

add_action( 'gform_after_submission', 'gravityforms_email_signup_subscriber_add', 10, 2 );
function gravityforms_email_signup_subscriber_add( $entry, $form ){

  if(!class_exists('SendPress_Data')){
    return;
  }

foreach ($form['fields'] as $key => $field) {
  if( $field->type == 'emailsignup' && $entry[ $field->id ] == 'signup-confirmed'){
    $listID = $field->emailSubscriberList; //SPNL()->validate->_int('listID');
    $status = $field->emailSubscriberStatus;
    $first_name = null;
    $last_name = null;
    $salutation = null;
    // simple text field
    if(!empty($entry[ $field->emailSubscriberName ])){
      $first_name = $entry[ $field->emailSubscriberName ];
      $last_name = $salutation = null;
    // GF speciality name field
    } else if ( !empty( $entry[ $field->emailSubscriberName . '.3' ] )) {
      $first_name = $entry[ $field->emailSubscriberName . '.3' ];
      $last_name =  $entry[ $field->emailSubscriberName . '.6' ];
      $salutation = $entry[ $field->emailSubscriberName . '.2' ];
    }
      $result = SendPress_Data::add_subscriber(
        array('firstname'=>$first_name,
        'email'=> $entry[ $field->emailSignup ],
        'lastname'=>$last_name,
        'phonenumber'=>null,
        'salutation'=>$salutation)
      );
    SendPress_Data::update_subscriber_status($listID, $result, $status ,false);
  }
}




}


add_action( 'gform_editor_js_set_default_values', 'gravityforms_email_signup_js_set_defaults' );
function gravityforms_email_signup_js_set_defaults(){
    ?>
    case "emailsignup" :
        field.inputs = null;
        field.label = 'Email Signup Confirmation';
        field.description = 'Yes, please use my email to sign up for the newsletter.';
        field.descriptionPlaceholder = 'Enter text to describe the checkbox';
    break;
    <?php
}

add_action( 'gform_field_standard_settings', 'gravityforms_email_signup_standard_settings', 10, 2 );
function gravityforms_email_signup_standard_settings( $position, $form_id ) {
    //create settings on position 25 (right after Field Label)
    if ( $position == 25 ) {
      $sendpress_campaign_list = SendPress_Data::get_lists();
        ?>
        <li class="field_email_signup field_setting">
            <label for="field_admin_label" class="section_label">
                <?php esc_html_e( 'Associate Email Field', 'gravityforms' ); ?>
                <?php gform_tooltip( 'form_field_email_signup' ) ?>
            </label>
            <select id="field_email_signup_field_associate">
            </select>
        </li>
        <li class="field_subscriber_name_associate field_setting">
            <label for="field_admin_label" class="section_label">
                <?php esc_html_e( 'Associate Name Field', 'gravityforms' ); ?>
                <?php gform_tooltip( 'form_field_subscriber_name_associate' ) ?>
            </label>
            <select id="field_subscriber_name_associate">
            </select>
        </li>
        <li class="field_email_subscriber_status field_setting">
            <label for="field_admin_label" class="section_label">
                <?php esc_html_e( 'Subscriber Status', 'gravityforms' ); ?>
                <?php gform_tooltip( 'form_field_email_subscriber_status' ) ?>
            </label>
            <?php if(class_exists('SendPress_Data')) : ?>
            <select id="field_email_signup_subscriber_status">
              <?php
  	    				$results =  SendPress_Data::get_statuses();
  	    				foreach($results as $status){
  	    					$selected = '';
  	    					if($status->status == 'Active'){
  	    						$selected = 'selected';
  	    					}
  	    					echo "<option value='$status->statusid' $selected>$status->status</option>";
  	    				}
  	    			?>

  	    		</select>
          <?php else : ?>
          You must have the SendPress plugin active.
        <?php endif; ?>
        </li>
        <li class="field_email_subscriber_list field_setting">
            <label for="field_admin_label" class="section_label">
                <?php esc_html_e( 'Associate Subscriber to List', 'gravityforms' ); ?>
                <?php gform_tooltip( 'form_field_email_subscriber' ) ?>
            </label>
            <select id="field_email_signup_subscriber">
              <?php foreach( $sendpress_campaign_list->posts as $list ) : ?>
                <option value="<?php echo $list->ID; ?>"><?php echo $list->post_title; ?></option>
              <?php endforeach; ?>
            </select>
        </li>
        <?php
    }
}

add_action( 'gform_editor_js', 'gravityforms_email_signup_editor_script' );
function gravityforms_email_signup_editor_script(){
    ?>
    <script type='text/javascript'>
        function gform_field_emailSignup_select( form, field ){
          var gfsp_email_field = jQuery('#field_email_signup_field_associate'), gfsp_name_field = jQuery('#field_subscriber_name_associate');
          var associatedEmailFieldId = field.hasOwnProperty('emailSignup') ? field.emailSignup : null;
          var associatedNameFieldId = field.hasOwnProperty('emailSubscriberName') ? field.emailSignup : null;
          gfsp_email_field.empty().append('<option value="">Select email field</option>');
          gfsp_name_field.empty().append('<option value="">Select name field</option>');
          jQuery(form.fields).each(function(index,value){
            if( value.type != 'emailsignup'){
              var isEmailSelected = value.id == associatedEmailFieldId ? ' selected ' : '';
              var isNameSelected = value.id == associatedNameFieldId ? ' selected ' : '';
              gfsp_email_field.append('<option value="' + value.id + '"' + isEmailSelected + '>' + value.label + '</option>');
              gfsp_name_field.append('<option value="' + value.id + '"' + isNameSelected + '>' + value.label + '</option>');
            }
          });

          gfsp_email_field.change(function(){
            SetFieldProperty('emailSignup', jQuery( "option:selected", gfsp_email_field ).val());
          });
          gfsp_name_field.change(function(){
            SetFieldProperty('emailSubscriberName', jQuery( "option:selected", gfsp_name_field ).val());
          });
        }

        //adding setting to fields of type "emailsignup"
        fieldSettings.emailsignup += ', .field_email_signup, .field_subscriber_name_associate, .field_email_subscriber_status, .field_email_subscriber_list';

        //binding to the load field settings event to initialize the checkbox
        jQuery(document).on('gform_load_field_settings', function(event, field, form){
            gform_field_emailSignup_select( form, field );

            jQuery('#field_email_signup_subscriber').change(function(){
              SetFieldProperty('emailSubscriberList', jQuery( "option:selected", jQuery(this) ).val());
            });

            if( field.hasOwnProperty('emailSubscriberList') ){
              jQuery("#field_email_signup_subscriber option[value='" + field.emailSubscriberList + "']").attr("selected","selected");
            }


            jQuery('#field_email_signup_subscriber_status').change(function(){
              console.log(jQuery( "option:selected", jQuery(this) ).val());
              SetFieldProperty('emailSubscriberStatus', jQuery( "option:selected", jQuery(this) ).val());
            });

            if( field.hasOwnProperty('emailSubscriberStatus') ){
              jQuery("#form_field_email_subscriber_status option[value='" + field.emailSubscriberStatus + "']").attr("selected","selected");
            }

        });
        jQuery(document).on( 'gform_field_added', function( event, form, field ) {
          gform_field_emailSignup_select( form, field );
        } );
        jQuery(document).on('gform_field_deleted', function(event, form, fieldId){
          gform_field_emailSignup_select( form, fieldId );
        });
    </script>
    <?php
}

add_filter( 'gform_tooltips', 'gravityforms_email_signup_tooltips' );
function gravityforms_email_signup_tooltips( $tooltips ) {
   $tooltips['form_field_email_signup'] = "<h6>Select Email</h6>Choose which field to use for the signup action";
   $tooltips['form_field_email_subscriber'] = "<h6>Select Subscriber List</h6>Choose which SendPress Subscriber List to use for the signup action";
   $tooltips['form_field_email_subscriber_status'] = "<h6>Select Subscriber Status</h6>Choose the status level for a subscriber when signup is complete.";
   $tooltips['form_field_subscriber_name_associate'] = "<h6>Subscriber Name</h6>Choose the field to use for the subscriber's name during signup.";
   return $tooltips;
}
