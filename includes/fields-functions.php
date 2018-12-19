<?php
/**
 * All fields related functionalities of Posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
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
		'listing_title',
		'listing_description',
		'listing_email_address',
		'listing_phone_number',
		'listing_website',
		'listing_video',
		'listing_social_media_profiles',
		'listing_categories',
		'listing_tags',
		'listing_regions',
		'listing_opening_hours',
		'listing_featured_image',
		'listing_gallery',
		'listing_zipcode',
		'listing_location',
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
 * @param string $key field key.
 * @return boolean
 */
function pno_is_default_field( $key ) {

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
 * This function is also used within the custom fields editor selection,
 * therefore sometimes it might be necessary to exclude some types from the list
 * hence why we have an exclude parameter.
 *
 * @param array $exclude the list of field types to exclude from the list.
 * @return array
 */
function pno_get_registered_field_types( $exclude = [] ) {

	$types = [
		'text'                  => esc_html__( 'Single text line' ),
		'textarea'              => esc_html__( 'Textarea' ),
		'checkbox'              => esc_html__( 'Checkbox' ),
		'email'                 => esc_html__( 'Email address' ),
		'password'              => esc_html__( 'Password' ),
		'url'                   => esc_html__( 'Website' ),
		'select'                => esc_html__( 'Dropdown' ),
		'radio'                 => esc_html__( 'Radio' ),
		'number'                => esc_html__( 'Number' ),
		'multiselect'           => esc_html__( 'Multiselect' ),
		'multicheckbox'         => esc_html__( 'Multiple checkboxes' ),
		'file'                  => esc_html__( 'File' ),
		'editor'                => esc_html__( 'Text editor' ),
		'social-profiles'       => esc_html__( 'Social profiles selector' ),
		'listing-category'      => esc_html__( 'Listing category selector' ),
		'listing-tags'          => esc_html__( 'Listing tags selector' ),
		'term-select'           => esc_html__( 'Taxonomy dropdown' ),
		'term-multiselect'      => esc_html__( 'Taxonomy multiselect' ),
		'term-checklist'        => esc_html__( 'Taxonomy check list' ),
		'listing-opening-hours' => esc_html__( 'Opening hours' ),
		'listing-location'      => esc_html__( 'Map' ),
	];

	/**
	 * Allows developers to register a new field type.
	 *
	 * @since 0.1.0
	 * @param array $types all registered field types.
	 */
	$types = apply_filters( 'pno_registered_field_types', $types );

	if ( ! empty( $exclude ) && is_array( $exclude ) ) {
		foreach ( $exclude as $type_to_exclude ) {
			if ( isset( $types[ $type_to_exclude ] ) ) {
				unset( $types[ $type_to_exclude ] );
			}
		}
	}

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
 * Retrieve the list of registration form fields.
 *
 * @return array
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

	// Now inject fields data from the database and add new fields if any.
	$fields_query_args = [
		'post_type'              => 'pno_signup_fields',
		'posts_per_page'         => 100,
		'nopaging'               => true,
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'post_status'            => 'publish',
		'fields'                 => 'ids',
	];

	$fields_query = new PNO\Database\Queries\Registration_Fields( [ 'number' => 100 ] );

	if ( isset( $fields_query->items ) && is_array( $fields_query->items ) ) {

		foreach ( $fields_query->items as $registration_field ) {

			$field = $registration_field;

			if ( $field instanceof PNO\Field\Registration && $field->get_post_id() > 0 ) {

				if ( pno_is_default_field( $field->get_object_meta_key() ) && isset( $fields[ $field->get_object_meta_key() ] ) ) {

					$fields[ $field->get_object_meta_key() ]['label']       = $field->get_label();
					$fields[ $field->get_object_meta_key() ]['description'] = $field->get_description();
					$fields[ $field->get_object_meta_key() ]['placeholder'] = $field->get_placeholder();

					if ( $field->get_priority() ) {
						$fields[ $field->get_object_meta_key() ]['priority'] = $field->get_priority();
					}
				} else {

					// The field does not exist so we now add it to the list of fields.
					$fields[ $field->get_object_meta_key() ] = [
						'label'       => $field->get_label(),
						'type'        => $field->get_type(),
						'description' => $field->get_description(),
						'placeholder' => $field->get_placeholder(),
						'required'    => $field->is_required(),
						'priority'    => $field->get_priority(),
					];

					if ( in_array( $field->get_type(), pno_get_multi_options_field_types() ) ) {
						$fields[ $field->get_object_meta_key() ]['options'] = $field->get_options();
					}
				}
			}
		}

		wp_reset_postdata();

	}

	// Remove username field if the option is enabled.
	if ( pno_get_option( 'disable_username' ) && isset( $fields['username'] ) ) {
		unset( $fields['username'] );
	}

	// Remove the password field if option enabled.
	if ( pno_get_option( 'disable_password' ) && isset( $fields['password'] ) ) {
		unset( $fields['password'] );
	}

	/**
	 * Allows developers to register or deregister fields for the registration form.
	 * Fields here are yet to be formatted for the Form object.
	 *
	 * @since 0.1.0
	 * @param array $fields array containing the list of fields for the registration form.
	 * @return array the list of fields yet to be formatted.
	 */
	$fields = apply_filters( 'pno_registration_fields', $fields );

	uasort( $fields, 'pno_sort_array_by_priority' );

	return $fields;

}

/**
 * Defines the list of the fields for the account form.
 * If a user id is passed through the function,
 * the related user's value is loaded within the field.
 *
 * @param string $user_id user id.
 * @return array
 */
function pno_get_account_fields( $user_id = false ) {

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
	$fields_query = new PNO\Database\Queries\Profile_Fields( [ 'number' => 100 ] );

	if ( isset( $fields_query->items ) && is_array( $fields_query->items ) ) {

		foreach ( $fields_query->items as $account_field ) {

			$field = $account_field;

			if ( $field instanceof PNO\Field\Profile && ! empty( $field->get_object_meta_key() ) ) {

				// Determine if the field is a default one so we can just merge it
				// to the existing default array.
				if ( isset( $fields[ $field->get_object_meta_key() ] ) ) {

					if ( $field->is_admin_only() === true ) {
						unset( $fields[ $field->get_object_meta_key() ] );
						continue;
					}

					$fields[ $field->get_object_meta_key() ]['label']       = $field->get_label();
					$fields[ $field->get_object_meta_key() ]['description'] = $field->get_description();
					$fields[ $field->get_object_meta_key() ]['placeholder'] = $field->get_placeholder();
					$fields[ $field->get_object_meta_key() ]['readonly']    = $field->is_readonly();

					if ( $field->get_object_meta_key() !== 'email' ) {
						$fields[ $field->get_object_meta_key() ]['required'] = $field->is_required();
					}
				} else {

					if ( $field->is_admin_only() === true ) {
						continue;
					}

					// The field does not exist so we now add it to the list of fields.
					$fields[ $field->get_object_meta_key() ] = [
						'label'       => $field->get_label(),
						'type'        => $field->get_type(),
						'description' => $field->get_description(),
						'placeholder' => $field->get_placeholder(),
						'readonly'    => $field->is_readonly(),
						'required'    => $field->is_required(),
						'priority'    => $field->get_priority(),
					];

					if ( in_array( $field->get_type(), pno_get_multi_options_field_types() ) ) {
						$fields[ $field->get_object_meta_key() ]['options'] = $field->get_options();
					}
				}

				if ( $field->get_priority() ) {
					$fields[ $field->get_object_meta_key() ]['priority'] = $field->get_priority();
				}

				if ( $field->get_type() == 'file' && ! empty( $field->get_maxsize() ) ) {
					$fields[ $field->get_object_meta_key() ]['max_size'] = $field->get_maxsize();
				}

				if ( $field->get_type() === 'file' && ! empty( $field->get_allowed_mime_types() ) ) {
					$fields[ $field->get_object_meta_key() ]['allowed_mime_types'] = $field->get_allowed_mime_types();
				}

				if ( $field->get_type() === 'file' && $field->is_multiple() ) {
					$fields[ $field->get_object_meta_key() ]['multiple'] = true;
				}

			}
		}
	}

	// Load user's related values within the fields.
	if ( $user_id ) {

		$user = get_user_by( 'id', $user_id );

		if ( $user instanceof WP_User ) {
			foreach ( $fields as $key => $field ) {
				$value = false;
				if ( pno_is_default_field( $key ) ) {
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

	// Remove the avatar field when option disabled.
	if ( ! pno_get_option( 'allow_avatars' ) && isset( $fields['avatar'] ) ) {
		unset( $fields['avatar'] );
	}

	/**
	 * Allows developers to register or deregister custom fields within the
	 * user's account editing form. Fields here are yet to be formatted for the form functionality.
	 *
	 * @param array $fields the list of fields.
	 * @param mixed $user_id if a user id is given we load the matching user meta.
	 * @return array list of fields yet to be formatted.
	 */
	$fields = apply_filters( 'pno_account_fields', $fields, $user_id );

	uasort( $fields, 'pno_sort_array_by_priority' );

	return $fields;

}

/**
 * Retrieve the classes for a given form field as an array.
 *
 * @param PNO\Form\Field $field field object.
 * @param string         $class optional classes.
 * @return array
 */
function pno_get_form_field_class( $field, $class = '' ) {

	$classes = [ 'pno-field' ];

	$classes[] = 'pno-field-' . sanitize_title( strtolower( $field->get_object_meta_key() ) );

	$classes[] = 'pno-field-' . $field->get_type();
	$classes[] = 'form-group';

	if ( isset( $class ) && ! empty( $class ) ) {
		$classes[] = esc_attr( $class );
	}

	$classes = array_map( 'esc_attr', $classes );

	/**
	 * Filters the list of CSS classes for the current form field.
	 *
	 * @param array $classes the list of classes.
	 * @param array $field the field being processed.
	 * @param string $class additional classes if any.
	 */
	$classes = apply_filters( 'pno_form_field_classes', $classes, $field, $class );

	return array_unique( $classes );

}

/**
 * Display the classes for a given form field.
 *
 * @param PNO\Form\Field $field field object.
 * @param string         $class optional classes.
 * @return void
 */
function pno_form_field_class( $field, $class = '' ) {
	echo 'class="' . join( ' ', pno_get_form_field_class( $field, $class ) ) . '"'; //phpcs:ignore
}

/**
 * Retrieve classes for a PNO\Form\Field input.
 *
 * @param PNO\Form\Field $field field object.
 * @param string         $class additional classes if any.
 * @return array
 */
function pno_get_form_field_input_class( $field, $class = '' ) {

	$classes = [ 'form-control' ];

	if ( $field->get_type() === 'textarea' ) {
		$classes[] = 'input-text';
	} elseif ( $field->get_type() === 'checkbox' || $field->get_type() === 'multicheckbox' || $field->get_type() === 'term-checklist' ) {
		$classes[] = 'custom-control-input';
	} elseif ( $field->get_type() === 'select' || $field->get_type() === 'term-select' || $field->get_type() === 'term-multiselect' ) {
		$classes[] = 'custom-select';
	} else {
		$classes[] = 'input-' . $field->get_type();
	}

	if ( isset( $class ) && ! empty( $class ) ) {
		$classes[] = esc_attr( $class );
	}

	$classes = array_map( 'esc_attr', $classes );

	/**
	 * Filters the list of CSS classes for the current form field input.
	 *
	 * @param array $classes the list of classes.
	 * @param array $field the field being processed.
	 * @param string $class additional classes if any.
	 */
	$classes = apply_filters( 'pno_form_field_input_classes', $classes, $field, $class );

	return array_unique( $classes );

}

/**
 * Display classes for a form field input element.
 *
 * @param PNO\Form\Field $field field object.
 * @param string         $class optional classes.
 * @return void
 */
function pno_form_field_input_class( $field, $class = '' ) {
	echo 'class="' . join( ' ', pno_get_form_field_input_class( $field, $class ) ) . '"'; //phpcs:ignore
}

/**
 * Create an array of the selectable options of a field.
 *
 * @param array $options options for the field.
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

/**
 * Create an array of the selectable options of a term-select field.
 *
 * @param string $taxonomy the name of the taxonomy for which we're retrieving terms.
 * @return array
 */
function pno_parse_selectable_taxonomy_options( $taxonomy ) {

	$formatted_options = [];

	if ( $taxonomy ) {

		$args = array(
			'hide_empty' => false,
		);

		/**
		 * Allow developers to modify the get_terms arguments when retrieving options for a term-select field.
		 *
		 * @param array $args get_terms arguments.
		 * @return array
		 */
		$terms = get_terms( $taxonomy, apply_filters( 'pno_parse_field_taxonomy_options', $args ) );

		if ( is_array( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$formatted_options[ absint( $term->term_id ) ] = sanitize_text_field( $term->name );
			}
		}
	}

	return $formatted_options;

}

/**
 * Get js settings for the listings submission form.
 *
 * @return array
 */
function pno_get_listings_submission_form_js_vars() {

	$js_settings = [
		'selected_listing_type'       => isset( $_POST['pno_listing_type_id'] ) && ! empty( sanitize_text_field( $_POST['pno_listing_type_id'] ) ) ? absint( $_POST['pno_listing_type_id'] ) : false, // phpcs: ignore
		'max_multiselect'             => absint( pno_get_option( 'submission_categories_amount' ) ),
		'subcategories_on_submission' => pno_get_option( 'submission_categories_sublevel' ) ? true : false,
		'ajax'                        => admin_url( 'admin-ajax.php' ),
		'get_tags_nonce'              => wp_create_nonce( 'pno_get_tags_from_categories_for_submission' ),
		'get_starter_tags_nonce'      => wp_create_nonce( 'pno_get_tags' ),
		'get_subcategories_nonce'     => wp_create_nonce( 'pno_get_subcategories' ),
		'days'                        => pno_get_days_of_the_week(),
		'is_editing_mode'             => is_page( pno_get_listing_editing_page_id() ),
		'editing_listing_id'          => is_page( pno_get_listing_editing_page_id() ) && isset( $_GET['listing_id'] ) ? absint( $_GET['listing_id'] ) : false,
	];

	return apply_filters( 'pno_listings_submission_form_js_vars', $js_settings );

}

/**
 * Retrieve the list of fields for the listings submission form.
 *
 * @return array
 */
function pno_get_listing_submission_fields( $listing_id = false ) {

	$fields = [
		'listing_title'                 => [
			'label'         => esc_html__( 'Listing title' ),
			'type'          => 'text',
			'required'      => true,
			'priority'      => 1,
			'default_field' => true,
		],
		'listing_description'           => [
			'label'         => esc_html__( 'Description' ),
			'type'          => 'editor',
			'required'      => true,
			'priority'      => 2,
			'default_field' => true,
		],
		'listing_email_address'         => [
			'label'         => esc_html__( 'Email address' ),
			'type'          => 'email',
			'required'      => false,
			'priority'      => 3,
			'default_field' => true,
		],
		'listing_phone_number'          => [
			'label'         => esc_html__( 'Phone number' ),
			'type'          => 'text',
			'required'      => false,
			'priority'      => 4,
			'default_field' => true,
		],
		'listing_website'               => [
			'label'         => esc_html__( 'Website' ),
			'type'          => 'url',
			'required'      => false,
			'priority'      => 5,
			'default_field' => true,
		],
		'listing_video'                 => [
			'label'         => esc_html__( 'Video' ),
			'type'          => 'url',
			'required'      => false,
			'priority'      => 6,
			'default_field' => true,
		],
		'listing_social_media_profiles' => [
			'label'         => esc_html__( 'Social media profiles' ),
			'type'          => 'social-profiles',
			'required'      => false,
			'priority'      => 7,
			'default_field' => true,
		],
		'listing_categories'            => [
			'label'         => esc_html__( 'Listing category' ),
			'type'          => 'listing-category',
			'taxonomy'      => 'listings-categories',
			'required'      => true,
			'priority'      => 8,
			'search'        => true,
			'default_field' => true,
		],
		'listing_tags'                  => [
			'label'         => esc_html__( 'Listing tags' ),
			'type'          => 'listing-tags',
			'required'      => true,
			'priority'      => 9,
			'default_field' => true,
		],
		'listing_regions'               => [
			'label'         => esc_html__( 'Listing regions' ),
			'type'          => 'term-select',
			'taxonomy'      => 'listings-locations',
			'placeholder'   => esc_html__( 'Select a region' ),
			'required'      => true,
			'priority'      => 10,
			'default_field' => true,
		],
		'listing_opening_hours'         => [
			'label'         => esc_html__( 'Hours of operation' ),
			'type'          => 'listing-opening-hours',
			'required'      => false,
			'priority'      => 11,
			'default_field' => true,
		],
		'listing_featured_image'        => [
			'label'              => esc_html__( 'Featured image' ),
			'type'               => 'file',
			'required'           => true,
			'priority'           => 12,
			'default_field'      => true,
			'dropzone_max_files' => 1,
		],
		'listing_gallery'               => [
			'label'         => esc_html__( 'Gallery images' ),
			'type'          => 'file',
			'multiple'      => true,
			'required'      => true,
			'priority'      => 13,
			'default_field' => true,
		],
		'listing_zipcode'               => [
			'label'         => esc_html__( 'Zipcode' ),
			'type'          => 'text',
			'required'      => false,
			'priority'      => 14,
			'default_field' => true,
		],
		'listing_location'              => [
			'label'         => esc_html__( 'Location' ),
			'type'          => 'listing-location',
			'required'      => false,
			'priority'      => 15,
			'default_field' => true,
		],
	];

	$counter = 0;

	// Make sure priority is always correct.
	foreach ( $fields as $key => $field ) {
		$counter ++;
		$fields[ $key ]['priority'] = $counter;
	}

	// Load fields from the database and merge it with the default settings.
	$fields_query = new PNO\Database\Queries\Listing_Fields( [ 'number' => 100 ] );

	if ( isset( $fields_query->items ) && is_array( $fields_query->items ) ) {

		foreach ( $fields_query->items as $listing_field ) {

			$field = $listing_field;

			if ( $field instanceof PNO\Field\Listing && ! empty( $field->get_object_meta_key() ) ) {

				// Determine if the field is a default one so we can just merge it
				// to the existing default array.
				if ( pno_is_default_field( $field->get_object_meta_key() ) ) {

					if ( $field->is_admin_only() === true && ! in_array( $field->get_object_meta_key(), [ 'listing_title' ] ) ) {
						unset( $fields[ $field->get_object_meta_key() ] );
						continue;
					}

					$fields[ $field->get_object_meta_key() ]['label']       = $field->get_label();
					$fields[ $field->get_object_meta_key() ]['description'] = $field->get_description();
					$fields[ $field->get_object_meta_key() ]['placeholder'] = $field->get_placeholder();
					$fields[ $field->get_object_meta_key() ]['readonly']    = $field->is_readonly();

					if ( $field->get_object_meta_key() !== 'listing_title' ) {
						$fields[ $field->get_object_meta_key() ]['required'] = $field->is_required();
					}
				} else {

					if ( $field->is_admin_only() === true ) {
						continue;
					}

					// The field does not exist so we now add it to the list of fields.
					$fields[ $field->get_object_meta_key() ] = [
						'label'       => $field->get_label(),
						'type'        => $field->get_type(),
						'description' => $field->get_description(),
						'placeholder' => $field->get_placeholder(),
						'readonly'    => $field->is_readonly(),
						'required'    => $field->is_required(),
						'priority'    => $field->get_priority(),
					];

					if ( in_array( $field->get_type(), pno_get_multi_options_field_types() ) ) {
						$fields[ $field->get_object_meta_key() ]['options'] = $field->get_options();
					}

					if ( in_array( $field->get_type(), [ 'term-select', 'term-multiselect', 'term-checklist' ] ) && ! empty( $field->get_taxonomy() ) ) {
						$fields[ $field->get_object_meta_key() ]['taxonomy'] = $field->get_taxonomy();
					}
				}

				if ( $field->get_priority() ) {
					$fields[ $field->get_object_meta_key() ]['priority'] = $field->get_priority();
				}

				if ( $field->get_type() === 'file' && ! empty( $field->get_allowed_mime_types() ) ) {
					$fields[ $field->get_object_meta_key() ]['allowed_mime_types'] = $field->get_allowed_mime_types();
				}

				if ( $field->get_type() == 'file' && ! empty( $field->get_maxsize() ) ) {
					$fields[ $field->get_object_meta_key() ]['max_size'] = $field->get_maxsize();
				}

				if ( $field->get_type() === 'file' && $field->is_multiple() ) {
					$fields[ $field->get_object_meta_key() ]['multiple'] = true;
				}

			}
		}
	}

	// Load listings related values within the fields.
	if ( $listing_id ) {
		$listing = get_post( $listing_id );
		if ( $listing instanceof WP_Post && $listing->post_type === 'listings' ) {
			foreach ( $fields as $key => $field ) {
				$value = false;
				if ( in_array( $key, pno_get_registered_default_meta_keys() ) ) {
					switch ( $key ) {
						case 'listing_title':
							$value = $listing->post_title;
							break;
						case 'listing_description':
							$value = $listing->post_content;
							break;
						case 'listing_email_address':
							$value = carbon_get_post_meta( $listing_id, 'listing_email' );
							break;
						case 'listing_phone_number':
							$value = carbon_get_post_meta( $listing_id, 'listing_phone_number' );
							break;
						case 'listing_website':
							$value = carbon_get_post_meta( $listing_id, 'listing_website' );
							break;
						case 'listing_video':
							$value = carbon_get_post_meta( $listing_id, 'listing_media_embed' );
							break;
						case 'listing_social_media_profiles':
							$value = wp_json_encode( carbon_get_post_meta( $listing_id, 'listing_social_profiles' ) );
							break;
						case 'listing_categories':
							$value = pno_serialize_stored_listing_categories( $listing_id );
							break;
						case 'listing_tags':
							$value = pno_serialize_stored_listing_tags( $listing_id );
							break;
						case 'listing_regions':
							$regions = wp_get_post_terms( $listing_id, 'listings-locations' );
							if ( is_array( $regions ) && ! empty( $regions ) ) {
								if ( pno_get_option( 'submission_region_sublevel' ) ) {
									$selected_regions = wp_filter_object_list( $regions, array( 'parent' => 0 ), 'not' );
								} else {
									$selected_regions = $regions;
								}
								$selected_regions = current( $selected_regions );
								$value            = isset( $selected_regions->term_id ) ? $selected_regions->term_id : false;
							} else {
								$value = false;
							}
							break;
						case 'listing_opening_hours':
							$value = wp_json_encode( get_post_meta( $listing_id, '_listing_opening_hours', true ) );
							break;
						case 'listing_featured_image':
							$featured_image_id = get_post_thumbnail_id( $listing_id );
							$value             = $featured_image_id;
							break;
						case 'listing_gallery':
							$gallery_images = get_post_meta( $listing_id, '_listing_gallery_images', true );
							$attachments    = [];
							if ( ! empty( $gallery_images ) && is_array( $gallery_images ) ) {
								foreach ( $gallery_images as $image_id ) {
									$image_id      = $image_id['value'];
									$attachments[] = $image_id;
								}
							}
							$value = $attachments;
							break;
						case 'listing_zipcode':
							$value = carbon_get_post_meta( $listing_id, 'listing_zipcode' );
							break;
						case 'listing_location':
							$value = wp_json_encode( carbon_get_post_meta( $listing_id, 'listing_location' ) );
							break;
					}
				} else {
					$value = carbon_get_post_meta( $listing_id, $key );
				}
				if ( ! empty( $value ) ) {
					$fields[ $key ]['value'] = $value;
				}
			}
		}
	}

	/**
	 * Allow developers to customize the listings submission form fields.
	 *
	 * @param array $fields the list of fields.
	 * @return array $fields
	 */
	$fields = apply_filters( 'pno_listing_submission_fields', $fields, $listing_id );

	uasort( $fields, 'pno_sort_array_by_priority' );

	return $fields;

}

/**
 * Retrieve categories assigned to a listing and prepare the "value" parameter
 * for fields within the listing's editing form.
 *
 * @param string $listing_id the id of the listing.
 * @return string
 */
function pno_serialize_stored_listing_categories( $listing_id ) {

	if ( ! $listing_id ) {
		return;
	}

	$value = '';

	$cats          = wp_get_post_terms( $listing_id, 'listings-categories' );
	$top           = wp_list_filter( $cats, [ 'parent' => 0 ] );
	$sub           = wp_list_filter( $cats, [ 'parent' => 0 ], 'NOT' );
	$parent        = [];
	$subcategories = [];

	if ( is_array( $top ) ) {
		foreach ( $top as $parent_category ) {
			$parent[] = $parent_category->term_id;
		}
	}

	if ( is_array( $sub ) ) {
		foreach ( $sub as $subcategory ) {
			$subcategories[] = $subcategory->term_id;
		}
	}

	$value = [
		'parent' => $parent,
		'sub'    => $subcategories,
	];

	return wp_json_encode( $value );

}

/**
 * Retrieve tags assigned to the listing and prepare the "value" parameter
 * for the tags selector field within the listing's editing form.
 *
 * @param string $listing_id the id of the listing.
 * @return string
 */
function pno_serialize_stored_listing_tags( $listing_id ) {

	if ( ! $listing_id ) {
		return;
	}

	$value = [];

	$stored_tags = wp_get_post_terms( $listing_id, 'listings-tags' );

	if ( ! empty( $stored_tags ) && is_array( $stored_tags ) ) {
		foreach ( $stored_tags as $tag ) {
			$value[] = $tag->term_id;
		}
	}

	return wp_json_encode( $value );

}

/**
 * Displays category select dropdown.
 *
 * Based on wp_dropdown_categories, with the exception of supporting multiple selected categories.
 *
 * @param string|array|object $args arguments for the function.
 * @return string
 */
function pno_dropdown_categories( $args = '' ) {

	$defaults = array(
		'orderby'         => 'id',
		'order'           => 'ASC',
		'show_count'      => 0,
		'hide_empty'      => 1,
		'parent'          => '',
		'child_of'        => 0,
		'exclude'         => '',
		'echo'            => 1,
		'selected'        => 0,
		'hierarchical'    => 0,
		'name'            => 'cat',
		'id'              => '',
		'class'           => 'pno-category-dropdown ' . ( is_rtl() ? 'chosen-rtl' : '' ),
		'depth'           => 0,
		'taxonomy'        => 'listings-categories',
		'value'           => 'id',
		'multiple'        => true,
		'show_option_all' => false,
		'placeholder'     => __( 'Choose a category&hellip;' ),
		'no_results_text' => __( 'No results match' ),
		'multiple_text'   => __( 'Select Some Options' ),
	);

	$r = wp_parse_args( $args, $defaults );

	if ( ! isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] ) {
		$r['pad_counts'] = true;
	}

	// Store in a transient to help sites with many cats.
	$categories_hash = 'pno_cats_' . md5( wp_json_encode( $r ) . PNO\Cache\Helper::get_transient_version( 'pno_get_' . $r['taxonomy'] ) );
	$categories      = get_transient( $categories_hash );

	if ( empty( $categories ) ) {
		$categories = get_terms(
			array(
				'taxonomy'     => $r['taxonomy'],
				'orderby'      => $r['orderby'],
				'order'        => $r['order'],
				'hide_empty'   => $r['hide_empty'],
				'parent'       => $r['parent'],
				'child_of'     => $r['child_of'],
				'exclude'      => $r['exclude'],
				'hierarchical' => $r['hierarchical'],
			)
		);
		set_transient( $categories_hash, $categories, DAY_IN_SECONDS * 7 );
	}

	$id = $r['id'] ? $r['id'] : $r['name'];

	$output = "<select name='" . esc_attr( $r['name'] ) . "[]' id='" . esc_attr( $id ) . "' class='" . esc_attr( $r['class'] ) . "' " . ( $r['multiple'] ? "multiple='multiple'" : '' ) . " data-placeholder='" . esc_attr( $r['placeholder'] ) . "' data-no_results_text='" . esc_attr( $r['no_results_text'] ) . "' data-multiple_text='" . esc_attr( $r['multiple_text'] ) . "'>\n";

	if ( $r['show_option_all'] ) {
		$output .= '<option value="">' . esc_html( $r['show_option_all'] ) . '</option>';
	}

	if ( ! empty( $categories ) ) {

		include_once PNO_PLUGIN_DIR . '/includes/walkers/class-pno-category-walker.php';

		$walker = new PNO_Category_Walker();

		if ( $r['hierarchical'] ) {
			$depth = $r['depth'];  // Walk the full depth.
		} else {
			$depth = -1; // Flat.
		}

		$output .= $walker->walk( $categories, $depth, $r );
	}

	$output .= "</select>\n";

	if ( $r['echo'] ) {
		echo $output; // WPCS: XSS ok.
	}

	return $output;

}
