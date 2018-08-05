<?php
/**
 * All fields related functionalities of Posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Defines a list of reserved meta keys for custom fields.
 *
 * @return array
 */
function pno_get_registered_default_meta_keys() {

	$keys = [
		'avatar',
		'first_name',
		'last_name',
		'email',
		'website',
		'description',
		'username',
		'password',
	];

	/**
	 * Allows developers to register additional default meta keys if needed.
	 *
	 * @param array $keys the list of registered default meta keys.
	 */
	return apply_filters( 'pno_registered_default_meta_keys', $keys );

}

/**
 * Determine default profile fields.
 *
 * @param string $key
 * @return boolean
 */
function pno_is_default_profile_field( $key ) {

	if ( ! $key ) {
		return;
	}

	$default = false;

	if ( in_array( $key, pno_get_registered_default_meta_keys() ) ) {
		$default = true;
	}

	return apply_filters( 'pno_is_default_field', (bool) $default );

}

/**
 * Retrieve the list of registered field types and their labels.
 *
 * @return array
 */
function pno_get_registered_field_types() {

	$types = [
		'text'          => esc_html__( 'Single text line' ),
		'textarea'      => esc_html__( 'Textarea' ),
		'checkbox'      => esc_html__( 'Checkbox' ),
		'email'         => esc_html__( 'Email address' ),
		'password'      => esc_html__( 'Password' ),
		'url'           => esc_html__( 'Website' ),
		'select'        => esc_html__( 'Dropdown' ),
		'radio'         => esc_html__( 'Radio' ),
		'number'        => esc_html__( 'Number' ),
		'multiselect'   => esc_html__( 'Multiselect' ),
		'multicheckbox' => esc_html__( 'Multiple checkboxes' ),
		'file'          => esc_html__( 'File' ),
		'editor'        => esc_html__( 'Text editor' ),
	];

	/**
	 * Allows developers to register a new field type.
	 *
	 * @since 0.1.0
	 * @param array $types all registered field types.
	 */
	$types = apply_filters( 'pno_registered_field_types', $types );

	asort( $types );

	return $types;

}

/**
 * Mark specific field types as "multi options". The custom fields
 * editor will allow generation of options for those field types.
 *
 * @return array
 */
function pno_get_multi_options_field_types() {

	$types = [
		'select',
		'multiselect',
		'multicheckbox',
		'radio',
	];

	return apply_filters( 'pno_multi_options_field_types', $types );

}

/**
 * Defines a list of default meta keys for fields that cannot be removed from the registration forms api.
 *
 * @return array
 */
function pno_get_registration_default_meta_keys() {

	$keys = [
		'username',
		'email',
		'password',
	];

	return apply_filters( 'pno_registration_default_meta_keys', $keys );

}

/**
 * Retrieve the list of registration form fields.
 *
 * @return void
 */
function pno_get_registration_fields() {

	$fields = array(
		'username' => array(
			'label'       => esc_html__( 'Username' ),
			'type'        => 'text',
			'required'    => true,
			'placeholder' => '',
			'priority'    => 1,
		),
		'email'    => array(
			'label'       => __( 'Email address' ),
			'type'        => 'email',
			'required'    => true,
			'placeholder' => '',
			'priority'    => 2,
		),
		'password' => array(
			'label'    => __( 'Password' ),
			'type'     => 'password',
			'required' => true,
			'priority' => 3,
		),
	);

	if ( pno_get_option( 'enable_role_selection' ) ) {
		$fields['role'] = array(
			'label'    => __( 'Register as:' ),
			'type'     => 'select',
			'required' => true,
			'options'  => pno_get_allowed_user_roles(),
			'priority' => 99,
			'value'    => get_option( 'default_role' ),
		);
	}

	$fields['robo'] = [
		'label'    => esc_html__( 'If you\'re human leave this blank:' ),
		'type'     => 'text',
		'required' => false,
		'priority' => 100,
	];

	// Add a terms field is enabled.
	if ( pno_get_option( 'enable_terms' ) ) {
		$terms_page = pno_get_option( 'terms_page' );
		$terms_page = is_array( $terms_page ) && isset( $terms_page['value'] ) ? $terms_page['value'] : false;
		if ( $terms_page ) {
			$fields['terms'] = array(
				'label'    => apply_filters( 'pno_terms_text', sprintf( __( 'By registering to this website you agree to the <a href="%s" target="_blank">terms &amp; conditions</a>.' ), get_permalink( $terms_page ) ) ),
				'type'     => 'checkbox',
				'required' => true,
				'priority' => 101,
			);
		}
	}

	// Add privacy checkbox if privacy page is enabled.
	if ( get_option( 'wp_page_for_privacy_policy' ) ) {
		$fields['privacy'] = array(
			'label'    => apply_filters( 'pno_privacy_text', sprintf( __( 'I have read and accept the <a href="%1$s" target="_blank">privacy policy</a> and allow "%2$s" to collect and store the data I submit through this form.' ), get_permalink( get_option( 'wp_page_for_privacy_policy' ) ), get_bloginfo( 'name' ) ) ),
			'type'     => 'checkbox',
			'required' => true,
			'priority' => 102,
		);
	}

	// Add a password confirmation field.
	if ( pno_get_option( 'verify_password' ) && ! pno_get_option( 'disable_password' ) && isset( $fields['password'] ) ) {
		$fields['password_confirm'] = array(
			'label'    => esc_html__( 'Confirm password' ),
			'type'     => 'password',
			'required' => true,
			'priority' => $fields['password']['priority'] + 1,
		);
	}

	// Remove username field if the option is enabled.
	if ( pno_get_option( 'disable_username' ) && isset( $fields['username'] ) ) {
		unset( $fields['username'] );
	}

	// Remove the password field if option enabled.
	if ( pno_get_option( 'disable_password' ) && isset( $fields['password'] ) ) {
		unset( $fields['password'] );
	}

	// Now inject fields data from the database and add new fields if any.
	$fields_query_args = [
		'post_type'              => 'pno_signup_fields',
		'posts_per_page'         => 100,
		'nopaging'               => true,
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'fields'                 => 'ids',
		'post_status'            => 'publish',
	];

	$fields_query = new WP_Query( $fields_query_args );

	if ( $fields_query->have_posts() ) {

		foreach ( $fields_query->get_posts() as $field_id ) {

			$field = new PNO_Registration_Field( $field_id );

			if ( $field instanceof PNO_Registration_Field && $field->get_id() > 0 ) {
				if ( ! empty( $field->is_default_field() ) && isset( $fields[ $field->get_meta() ] ) ) {
					$fields[ $field->get_meta() ]['label']       = $field->get_label();
					$fields[ $field->get_meta() ]['description'] = $field->get_description();
					$fields[ $field->get_meta() ]['placeholder'] = $field->get_placeholder();
					if ( $field->get_priority() ) {
						$fields[ $field->get_meta() ]['priority'] = $field->get_priority();
					}
				} elseif ( ! $field->is_default_field() && ! isset( $fields[ $field->get_meta() ] ) ) {
					// The field does not exist so we now add it to the list of fields.
					$fields[ $field->get_meta() ] = [
						'label'       => $field->get_label(),
						'type'        => $field->get_type(),
						'description' => $field->get_description(),
						'placeholder' => $field->get_placeholder(),
						'required'    => $field->is_required(),
						'priority'    => $field->get_priority(),
					];

					if ( in_array( $field->get_type(), pno_get_multi_options_field_types() ) ) {
						$fields[ $field->get_meta() ]['options'] = $field->get_selectable_options();
					}
				}
			}
		}

		wp_reset_postdata();

	}

	/**
	 * Allows developers to register or deregister fields for the registration form.
	 *
	 * @since 0.1.0
	 * @param array $fields array containing the list of fields for the registration form.
	 */
	return apply_filters( 'pno_registration_fields', $fields );

}

/**
 * Defines the list of the fields for the account form.
 * If a user id is passed through the function,
 * the related user's value is loaded within the field.
 *
 * @param string $user_id
 * @param boolean $admin_request flag to determine if the function is an admin request or not.
 * @return void
 */
function pno_get_account_fields( $user_id = false, $admin_request = false ) {

	$fields = [
		'avatar'      => [
			'label'              => esc_html__( 'Profile picture' ),
			'type'               => 'file',
			'required'           => false,
			'placeholder'        => '',
			'priority'           => 1,
			'allowed_mime_types' => [
				'jpg'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'gif'  => 'image/gif',
				'png'  => 'image/png',
			],
		],
		'first_name'  => [
			'label'       => esc_html__( 'First name' ),
			'type'        => 'text',
			'required'    => true,
			'placeholder' => '',
			'priority'    => 2,
		],
		'last_name'   => [
			'label'       => esc_html__( 'Last name' ),
			'type'        => 'text',
			'required'    => true,
			'placeholder' => '',
			'priority'    => 3,
		],
		'email'       => [
			'label'       => esc_html__( 'Email address' ),
			'type'        => 'email',
			'required'    => true,
			'placeholder' => '',
			'priority'    => 4,
		],
		'website'     => [
			'label'       => esc_html__( 'Website' ),
			'type'        => 'text',
			'required'    => false,
			'placeholder' => '',
			'priority'    => 5,
		],
		'description' => [
			'label'       => esc_html__( 'About me' ),
			'type'        => 'editor',
			'required'    => false,
			'placeholder' => '',
			'priority'    => 6,
		],
	];

	// Load fields from the database and merge it with the default settings.
	$fields_query_args = [
		'post_type'              => 'pno_users_fields',
		'posts_per_page'         => 100,
		'nopaging'               => true,
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'post_status'            => 'publish',
	];

	$fields_query = new WP_Query( $fields_query_args );

	if ( $fields_query->have_posts() ) {

		while ( $fields_query->have_posts() ) {

			$fields_query->the_post();

			$field = new PNO_Profile_Field( get_the_ID() );

			if ( $field instanceof PNO_Profile_Field && ! empty( $field->get_meta() ) ) {

				// Determine if the field is a default one so we can just merge it
				// to the existing default array.
				if ( isset( $fields[ $field->get_meta() ] ) ) {

					if ( $field->is_admin_only() === true && ! $admin_request ) {
						unset( $fields[ $field->get_meta() ] );
						continue;
					}

					$fields[ $field->get_meta() ]['label']       = $field->get_label();
					$fields[ $field->get_meta() ]['description'] = $field->get_description();
					$fields[ $field->get_meta() ]['placeholder'] = $field->get_placeholder();
					$fields[ $field->get_meta() ]['readonly']    = $field->is_read_only();

					if ( $field->get_meta() !== 'email' ) {
						$fields[ $field->get_meta() ]['required'] = $field->is_required();
					}

					if ( $field->get_custom_classes() ) {
						$fields[ $field->get_meta() ]['css_class'] = $field->get_custom_classes();
					}

					if ( $field->get_priority() ) {
						$fields[ $field->get_meta() ]['priority'] = $field->get_priority();
					}
				} else {

					// The field does not exist so we now add it to the list of fields.
					$fields[ $field->get_meta() ] = [
						'label'       => $field->get_label(),
						'type'        => $field->get_type(),
						'description' => $field->get_description(),
						'placeholder' => $field->get_placeholder(),
						'readonly'    => $field->is_read_only(),
						'required'    => $field->is_required(),
						'css_class'   => $field->get_custom_classes(),
						'priority'    => $field->get_priority(),
					];

					if ( in_array( $field->get_type(), pno_get_multi_options_field_types() ) ) {
						$fields[ $field->get_meta() ]['options'] = $field->get_selectable_options();
					}

					if ( $field->get_type() == 'file' && ! empty( $field->get_file_size() ) ) {
						$fields[ $field->get_meta() ]['max_size'] = $field->get_file_size();
					}
				}
			}
		}

		wp_reset_postdata();

	}

	// Load user's related values within the fields.
	if ( $user_id ) {

		$user = get_user_by( 'id', $user_id );

		if ( $user instanceof WP_User ) {
			foreach ( $fields as $key => $field ) {
				$value = false;
				if ( pno_is_default_profile_field( $key ) ) {
					switch ( $key ) {
						case 'email':
							$value = esc_attr( $user->user_email );
							break;
						case 'website':
							$value = esc_url( $user->user_url );
							break;
						case 'avatar':
							$value = esc_url( carbon_get_user_meta( $user_id, 'current_user_avatar' ) );
							break;
						default:
							$value = esc_html( get_user_meta( $user_id, $key, true ) );
							break;
					}
				} else {
					$value = carbon_get_user_meta( $user_id, $key );
				}
				if ( $value ) {
					$fields[ $key ]['value'] = $value;
				}
			}
		}
	}

	uasort( $fields, 'pno_sort_array_by_priority' );

	/**
	 * Allows developers to register or deregister custom fields within the
	 * user's account editing form.
	 *
	 * @param array $fields
	 * @param mixed $user_id
	 */
	return apply_filters( 'pno_account_fields', $fields, $user_id );

}

/**
 * Retrieve the classes for a given form field as an array.
 *
 * @param array $field
 * @param string $class
 * @return array
 */
function pno_get_form_field_class( $field_key, $field, $form = false, $class = '' ) {

	$classes = [ 'pno-field' ];

	if ( $field_key ) {
		$classes[] = 'pno-field-' . $field_key;
	}

	$classes[] = 'pno-field-' . $field['type'];
	$classes[] = 'form-group';
	$classes[] = 'col-sm-12';

	if ( isset( $field['css_class'] ) && ! empty( $field['css_class'] ) ) {
		$classes[] = esc_attr( $field['css_class'] );
	}

	$classes = array_map( 'esc_attr', $classes );

	/**
	 * Filters the list of CSS classes for the current form field.
	 *
	 * @param array $classes
	 * @param array $field
	 * @param string $form
	 * @param string $class
	 */
	$classes = apply_filters( 'pno_form_field_classes', $classes, $field_key, $field, $form, $class );

	return array_unique( $classes );

}

/**
 * Display the classes for a given form field.
 *
 * @param string $field_key
 * @param array $field
 * @param string $class
 * @return void
 */
function pno_form_field_class( $field_key, $field, $form = false, $class = '' ) {
	// Separates classes with a single space, collates classes for body element.
	echo 'class="' . join( ' ', pno_get_form_field_class( $field_key, $field, $form, $class ) ) . '"';
}

/**
 * Create an array of the selectable options of a field.
 *
 * @param array $options
 * @return array
 */
function pno_parse_selectable_options( $options = [] ) {

	$formatted_options = [];

	if ( is_array( $options ) && ! empty( $options ) ) {
		foreach ( $options as $key => $value ) {

			$option_title = $value['option_title'];
			$meta         = sanitize_title( $option_title );
			$meta         = str_replace( '-', '_', $meta );
			$option_value = $meta;

			$formatted_options[ $option_value ] = $option_title;

		}
	}

	return $formatted_options;

}
