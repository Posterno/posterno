<?php
/**
 * All fields related functionalities of Posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
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
		'text'                  => esc_html__( 'Single text line', 'posterno' ),
		'textarea'              => esc_html__( 'Textarea', 'posterno' ),
		'checkbox'              => esc_html__( 'Checkbox', 'posterno' ),
		'email'                 => esc_html__( 'Email address', 'posterno' ),
		'password'              => esc_html__( 'Password', 'posterno' ),
		'url'                   => esc_html__( 'Website', 'posterno' ),
		'select'                => esc_html__( 'Dropdown', 'posterno' ),
		'radio'                 => esc_html__( 'Radio', 'posterno' ),
		'number'                => esc_html__( 'Number', 'posterno' ),
		'multiselect'           => esc_html__( 'Multiselect', 'posterno' ),
		'multicheckbox'         => esc_html__( 'Multiple checkboxes', 'posterno' ),
		'file'                  => esc_html__( 'File', 'posterno' ),
		'editor'                => esc_html__( 'Text editor', 'posterno' ),
		'social-profiles'       => esc_html__( 'Social profiles selector', 'posterno' ),
		'listing-category'      => esc_html__( 'Listing category selector', 'posterno' ),
		'listing-tags'          => esc_html__( 'Listing tags selector', 'posterno' ),
		'term-select'           => esc_html__( 'Taxonomy dropdown', 'posterno' ),
		'term-multiselect'      => esc_html__( 'Taxonomy multiselect', 'posterno' ),
		'term-checklist'        => esc_html__( 'Taxonomy check list', 'posterno' ),
		'term-chain-dropdown'   => esc_html__( 'Taxonomy chain dropdown', 'posterno' ),
		'listing-opening-hours' => esc_html__( 'Opening hours', 'posterno' ),
		'listing-location'      => esc_html__( 'Map', 'posterno' ),
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

	$fields = [
		'username' => [
			'type'       => 'text',
			'label'      => esc_html__( 'Username', 'posterno' ),
			'required'   => true,
			'priority'   => 1,
			'attributes' => [
				'class' => 'form-control',
			],
		],
		'email'    => [
			'type'       => 'email',
			'label'      => esc_html__( 'Email address', 'posterno' ),
			'required'   => true,
			'priority'   => 2,
			'attributes' => [
				'class' => 'form-control',
			],
		],
		'password' => [
			'type'       => 'password',
			'label'      => esc_html__( 'Password', 'posterno' ),
			'required'   => true,
			'priority'   => 3,
			'attributes' => [
				'class' => 'form-control',
			],
		],
	];

	if ( pno_get_option( 'enable_role_selection' ) && count( pno_get_allowed_user_roles() ) >= 2 ) {
		$fields['role'] = array(
			'label'      => esc_html__( 'Register as:', 'posterno' ),
			'type'       => 'select',
			'required'   => true,
			'values'     => pno_get_allowed_user_roles(),
			'validators' => new PNO\Validator\KeyContained( pno_get_allowed_user_roles() ),
			'priority'   => 99,
			'value'      => get_option( 'default_role' ),
			'attributes' => [
				'class' => 'custom-select',
			],
		);
	}

	// Add a terms field is enabled.
	if ( pno_get_option( 'enable_terms' ) ) {
		$terms_page = pno_get_option( 'terms_page' );
		$terms_page = is_array( $terms_page ) && isset( $terms_page[0] ) ? $terms_page[0] : false;
		if ( $terms_page ) {
			$fields['terms'] = array(
				'label'      => apply_filters( 'pno_terms_text', sprintf( __( 'By registering to this website you agree to the <a href="%s" target="_blank">terms &amp; conditions</a>.', 'posterno' ), get_permalink( $terms_page ) ) ),
				'type'       => 'checkbox',
				'required'   => true,
				'priority'   => 800,
				'attributes' => [
					'class' => 'custom-control-input',
				],
			);
		}
	}

	// Add privacy checkbox if privacy page is enabled.
	if ( get_option( 'wp_page_for_privacy_policy' ) ) {
		$fields['privacy'] = array(
			'label'      => apply_filters( 'pno_privacy_text', sprintf( __( 'I have read and accept the <a href="%1$s" target="_blank">privacy policy</a> and allow "%2$s" to collect and store the data I submit through this form.', 'posterno' ), get_permalink( get_option( 'wp_page_for_privacy_policy' ) ), get_bloginfo( 'name' ) ) ),
			'type'       => 'checkbox',
			'required'   => true,
			'priority'   => 801,
			'attributes' => [
				'class' => 'custom-control-input',
			],
		);
	}

	// Add a password confirmation field.
	if ( pno_get_option( 'verify_password' ) && ! pno_get_option( 'disable_password' ) && isset( $fields['password'] ) ) {
		$fields['password_confirm'] = array(
			'label'      => esc_html__( 'Confirm password', 'posterno' ),
			'type'       => 'password',
			'required'   => true,
			'priority'   => $fields['password']['priority'] + 1,
			'attributes' => [
				'class' => 'form-control',
			],
		);
	}

	// Add strong password validators.
	if ( pno_get_option( 'strong_passwords' ) ) {
		$error_message    = esc_html__( 'Password must be at least 8 characters long and contain at least 1 number, 1 uppercase letter and 1 special character.', 'posterno' );
		$contains_letter  = new \PNO\Validator\RegEx( '/[A-Z]/', $error_message );
		$contains_digit   = new \PNO\Validator\RegEx( '/\d/', $error_message );
		$contains_special = new \PNO\Validator\RegEx( '/[^a-zA-Z\d]/', $error_message );
		$lenght           = new \PNO\Validator\LengthGreaterThanEqual( 8, $error_message );

		if ( isset( $fields['password'] ) ) {
			$fields['password']['validators'] = [
				$contains_letter,
				$contains_digit,
				$contains_special,
				$lenght,
			];
		}
	}

	// Now inject fields data from the database and add new fields if any.
	$fields_query = new PNO\Database\Queries\Registration_Fields( [ 'number' => 100 ] );

	if ( isset( $fields_query->items ) && is_array( $fields_query->items ) ) {

		foreach ( $fields_query->items as $registration_field ) {

			$field = $registration_field;

			if ( $field instanceof \PNO\Entities\Field\Registration && $field->getPostID() > 0 ) {

				if ( pno_is_default_field( $field->getObjectMetaKey() ) && isset( $fields[ $field->getObjectMetaKey() ] ) ) {
					$fields[ $field->getObjectMetaKey() ]['label'] = $field->getTitle();
					$fields[ $field->getObjectMetaKey() ]['hint']  = $field->getDescription();
					if ( $field->getPlaceholder() ) {
						$fields[ $field->getObjectMetaKey() ]['attributes']['placeholder'] = $field->getPlaceholder();
					}
					if ( $field->getPriority() ) {
						$fields[ $field->getObjectMetaKey() ]['priority'] = $field->getPriority();
					}
				} else {

					// The field does not exist so we now add it to the list of fields.
					$fields[ $field->getObjectMetaKey() ] = [
						'label'    => $field->getTitle(),
						'type'     => $field->getType(),
						'hint'     => $field->getDescription(),
						'required' => $field->isRequired(),
						'priority' => $field->getPriority(),
					];
					if ( $field->getPlaceholder() ) {
						$fields[ $field->getObjectMetaKey() ]['attributes']['placeholder'] = $field->getPlaceholder();
					}
					if ( in_array( $field->getType(), pno_get_multi_options_field_types() ) ) {
						$fields[ $field->getObjectMetaKey() ]['values'] = $field->getOptions();
					}

					$fields[ $field->getObjectMetaKey() ]['attributes']['class'] = 'form-control';

				}
			}

			// The field does not exist so we now add it to the list of fields.
			$attributes = [
				'class'       => 'form-control',
				'placeholder' => ! empty( $field->getPlaceholder() ) ? esc_attr( $field->getPlaceholder() ) : false,
			];

			if ( $field->getType() === 'multicheckbox' || $field->getType() === 'radio' ) {
				unset( $attributes['class'] );
			}
			if ( $field->getType() === 'checkbox' ) {
				$attributes['class'] = 'custom-control-input';
			}
			if ( $field->getType() === 'select' ) {
				$attributes['class'] = 'custom-select';
			}
			if ( $field->getType() === 'file' ) {
				$attributes['class'] = 'custom-file-input';
			}
			if ( $field->getType() === 'multiselect' ) {
				$attributes['data-placeholder'] = ! empty( $field->getPlaceholder() ) ? esc_attr( $field->getPlaceholder() ) : false;
			}
			if ( $field->getType() === 'textarea' ) {
				$attributes['rows'] = 3;
			}

			$fields[ $field->getObjectMetaKey() ]['attributes'] = $attributes;

			if ( ! empty( $field->getCssClasses() ) ) {
				$fields[ $field->getObjectMetaKey() ]['classes'] = $field->getCssClasses();
			}
		}
	}

	// Remove username field if the option is enabled.
	if ( pno_get_option( 'disable_username' ) && isset( $fields['username'] ) ) {
		unset( $fields['username'] );
	}

	// Remove the password field if option enabled.
	if ( pno_get_option( 'disable_password' ) && isset( $fields['password'] ) ) {
		unset( $fields['password'] );
	}

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
function pno_get_account_fields( $user_id = false, $bypass = false ) {

	$fields = [
		'avatar'      => [
			'label'              => esc_html__( 'Profile picture', 'posterno' ),
			'type'               => 'file',
			'required'           => false,
			'priority'           => 1,
			'allowed_mime_types' => [
				'jpg'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'gif'  => 'image/gif',
				'png'  => 'image/png',
			],
			'default_field'      => true,
			'attributes'         => [
				'class' => 'form-control',
			],
		],
		'first_name'  => [
			'label'         => esc_html__( 'First name', 'posterno' ),
			'type'          => 'text',
			'required'      => true,
			'priority'      => 2,
			'default_field' => true,
			'attributes'    => [
				'class' => 'form-control',
			],
		],
		'last_name'   => [
			'label'         => esc_html__( 'Last name', 'posterno' ),
			'type'          => 'text',
			'required'      => true,
			'priority'      => 3,
			'default_field' => true,
			'attributes'    => [
				'class' => 'form-control',
			],
		],
		'email'       => [
			'label'         => esc_html__( 'Email address', 'posterno' ),
			'type'          => 'email',
			'required'      => true,
			'priority'      => 4,
			'default_field' => true,
			'attributes'    => [
				'class' => 'form-control',
			],
		],
		'website'     => [
			'label'         => esc_html__( 'Website', 'posterno' ),
			'type'          => 'url',
			'required'      => false,
			'priority'      => 5,
			'default_field' => true,
			'attributes'    => [
				'class' => 'form-control',
			],
		],
		'description' => [
			'label'         => esc_html__( 'About me', 'posterno' ),
			'type'          => 'editor',
			'required'      => false,
			'priority'      => 6,
			'default_field' => true,
			'attributes'    => [
				'class' => 'form-control',
			],
		],
	];

	// Load fields from the database and merge it with the default settings.
	$fields_query = new \PNO\Database\Queries\Profile_Fields( [ 'number' => 100 ] );

	if ( isset( $fields_query->items ) && is_array( $fields_query->items ) ) {
		foreach ( $fields_query->items as $account_field ) {
			$field = $account_field;
			if ( $field instanceof \PNO\Entities\Field\Profile && ! empty( $field->getObjectMetaKey() ) ) {

				// Determine if the field is a default one so we can just merge it
				// to the existing default array.
				if ( isset( $fields[ $field->getObjectMetaKey() ] ) ) {
					if ( $field->isAdminOnly() === true ) {
						unset( $fields[ $field->getObjectMetaKey() ] );
						continue;
					}
					if ( $field->getObjectMetaKey() !== 'email' ) {
						$fields[ $field->getObjectMetaKey() ]['required'] = $field->isRequired();
					}
				} else {
					if ( $field->isAdminOnly() === true ) {
						continue;
					}
				}

				// The field does not exist so we now add it to the list of fields.
				$attributes = [
					'class'       => 'form-control',
					'placeholder' => ! empty( $field->getPlaceholder() ) ? esc_attr( $field->getPlaceholder() ) : false,
				];

				if ( $field->getType() === 'multicheckbox' || $field->getType() === 'radio' ) {
					unset( $attributes['class'] );
				}
				if ( $field->getType() === 'checkbox' ) {
					$attributes['class'] = 'custom-control-input';
				}
				if ( $field->getType() === 'select' ) {
					$attributes['class'] = 'custom-select';
				}
				if ( $field->getType() === 'multiselect' ) {
					$attributes['data-placeholder'] = ! empty( $field->getPlaceholder() ) ? esc_attr( $field->getPlaceholder() ) : false;
				}
				if ( $field->getType() === 'textarea' ) {
					$attributes['rows'] = 3;
				}
				if ( $field->getType() === 'file' ) {
					$attributes['class'] = 'custom-file-input';
				}

				$fields[ $field->getObjectMetaKey() ] = [
					'label'      => $field->getTitle(),
					'type'       => $field->getType(),
					'hint'       => $field->getDescription(),
					'readonly'   => $field->isReadOnly(),
					'required'   => $field->isRequired(),
					'priority'   => $field->getPriority(),
					'attributes' => $attributes,
				];

				if ( in_array( $field->getType(), pno_get_multi_options_field_types() ) ) {
					$fields[ $field->getObjectMetaKey() ]['values'] = $field->getOptions();
				}

				if ( $field->getPriority() ) {
					$fields[ $field->getObjectMetaKey() ]['priority'] = $field->getPriority();
				}

				if ( $field->getType() == 'file' && ! empty( $field->getMaxSize() ) ) {
					$fields[ $field->getObjectMetaKey() ]['max_size'] = $field->getMaxSize();
				}

				if ( $field->getType() === 'file' && ! empty( $field->getAllowedMimeTypes() ) ) {
					$fields[ $field->getObjectMetaKey() ]['allowed_mime_types'] = $field->getAllowedMimeTypes();
				}

				if ( $field->getType() === 'file' && $field->isMultiple() ) {
					$fields[ $field->getObjectMetaKey() ]['multiple'] = true;
				}

				if ( ! empty( $field->getCssClasses() ) ) {
					$fields[ $field->getObjectMetaKey() ]['classes'] = $field->getCssClasses();
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
	if ( ! pno_get_option( 'allow_avatars' ) && isset( $fields['avatar'] ) && ! $bypass ) {
		unset( $fields['avatar'] );
	}

	uasort( $fields, 'pno_sort_array_by_priority' );

	return $fields;

}

/**
 * Retrieve the classes for a given form field as an array.
 *
 * @param PNO\Form\Element $field field object.
 * @param string           $class optional classes.
 * @return array
 */
function pno_get_form_field_class( $field, $class = '' ) {

	$classes = [ 'pno-field' ];

	$classes[] = 'pno-field-' . sanitize_title( strtolower( $field->getName() ) );

	$classes[] = 'pno-field-' . $field->getType();
	$classes[] = 'form-group';

	if ( $field->getType() === 'checkbox' ) {
		$classes[] = 'custom-control custom-checkbox';
	}

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
 * Retrieve the classes for a given form field wrapper as an array.
 *
 * @param PNO\Form\Element $field field object.
 * @param string           $class optional classes.
 * @return array
 */
function pno_get_form_field_wrapper_class( $field, $class = '' ) {

	$classes = [ 'col-md-12' ];

	if ( ! empty( $field->getClasses() ) ) {
		$string  = explode( ' ', $field->getClasses() );
		$classes = $string;
	}

	$classes = array_map( 'esc_attr', $classes );

	/**
	 * Filters the list of CSS classes for the current form field wrapper.
	 *
	 * @param array $classes the list of classes.
	 * @param array $field the field being processed.
	 * @param string $class additional classes if any.
	 */
	$classes = apply_filters( 'pno_form_field_wrapper_classes', $classes, $field, $class );

	return array_unique( $classes );

}

/**
 * Display the classes for a given form field.
 *
 * @param PNO\Form\Field $field field object.
 * @param string         $class optional classes.
 * @return void
 */
function pno_form_field_wrapper_class( $field, $class = '' ) {
	echo 'class="' . join( ' ', pno_get_form_field_wrapper_class( $field, $class ) ) . '"'; //phpcs:ignore
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
 * Retrieve the list of fields for the listings submission form.
 *
 * @return array
 */
function pno_get_listing_submission_fields( $listing_id = false ) {

	$fields = [
		'listing_title'                 => [
			'label'         => esc_html__( 'Listing title', 'posterno' ),
			'type'          => 'text',
			'required'      => true,
			'priority'      => 1,
			'default_field' => true,
			'attributes'    => [
				'class' => 'form-control',
			],
		],
		'listing_description'           => [
			'label'         => esc_html__( 'Description', 'posterno' ),
			'type'          => 'editor',
			'required'      => true,
			'priority'      => 2,
			'default_field' => true,
			'attributes'    => [
				'class' => 'form-control',
			],
		],
		'listing_email_address'         => [
			'label'         => esc_html__( 'Email address', 'posterno' ),
			'type'          => 'email',
			'required'      => false,
			'priority'      => 3,
			'default_field' => true,
			'attributes'    => [
				'class' => 'form-control',
			],
		],
		'listing_phone_number'          => [
			'label'         => esc_html__( 'Phone number', 'posterno' ),
			'type'          => 'text',
			'required'      => false,
			'priority'      => 4,
			'default_field' => true,
			'attributes'    => [
				'class' => 'form-control',
			],
		],
		'listing_website'               => [
			'label'         => esc_html__( 'Website', 'posterno' ),
			'type'          => 'url',
			'required'      => false,
			'priority'      => 5,
			'default_field' => true,
			'attributes'    => [
				'class' => 'form-control',
			],
		],
		'listing_video'                 => [
			'label'         => esc_html__( 'Video', 'posterno' ),
			'type'          => 'url',
			'required'      => false,
			'priority'      => 6,
			'default_field' => true,
			'attributes'    => [
				'class' => 'form-control',
			],
		],
		'listing_social_media_profiles' => [
			'label'         => esc_html__( 'Social media profiles', 'posterno' ),
			'type'          => 'social-profiles',
			'required'      => false,
			'priority'      => 7,
			'default_field' => true,
		],
		'listing_categories'            => [
			'label'         => esc_html__( 'Listing category', 'posterno' ),
			'type'          => 'listing-category',
			'taxonomy'      => 'listings-categories',
			'required'      => true,
			'priority'      => 8,
			'search'        => true,
			'default_field' => true,
		],
		'listing_tags'                  => [
			'label'         => esc_html__( 'Listing tags', 'posterno' ),
			'type'          => 'listing-tags',
			'required'      => true,
			'priority'      => 9,
			'default_field' => true,
		],
		'listing_regions'               => [
			'label'         => esc_html__( 'Listing regions', 'posterno' ),
			'type'          => 'term-chain-dropdown',
			'taxonomy'      => 'listings-locations',
			'placeholder'   => esc_html__( 'Select a region', 'posterno' ),
			'required'      => true,
			'priority'      => 10,
			'default_field' => true,
		],
		'listing_opening_hours'         => [
			'label'         => esc_html__( 'Hours of operation', 'posterno' ),
			'type'          => 'listing-opening-hours',
			'required'      => false,
			'priority'      => 11,
			'default_field' => true,
		],
		'listing_featured_image'        => [
			'label'         => esc_html__( 'Featured image', 'posterno' ),
			'type'          => 'file',
			'required'      => true,
			'priority'      => 12,
			'default_field' => true,
			'attributes'    => [
				'class' => 'form-control custom-file-input',
			],
		],
		'listing_gallery'               => [
			'label'         => esc_html__( 'Gallery images', 'posterno' ),
			'type'          => 'file',
			'multiple'      => true,
			'required'      => true,
			'priority'      => 13,
			'default_field' => true,
			'attributes'    => [
				'class' => 'form-control custom-file-input',
			],
		],
		'listing_zipcode'               => [
			'label'         => esc_html__( 'Zipcode', 'posterno' ),
			'type'          => 'text',
			'required'      => false,
			'priority'      => 14,
			'default_field' => true,
			'attributes'    => [
				'class' => 'form-control',
			],
		],
		'listing_location'              => [
			'label'         => esc_html__( 'Location', 'posterno' ),
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
	$fields_query = new PNO\Database\Queries\Listing_Fields( [ 'number' => 300 ] );

	if ( isset( $fields_query->items ) && is_array( $fields_query->items ) ) {

		foreach ( $fields_query->items as $listing_field ) {

			$field = $listing_field;

			if ( $field instanceof \PNO\Entities\Field\Listing && ! empty( $field->getObjectMetaKey() ) ) {
				// Determine if the field is a default one so we can just merge it
				// to the existing default array.
				if ( pno_is_default_field( $field->getObjectMetaKey() ) ) {

					if ( $field->isAdminOnly() === true && ! in_array( $field->getObjectMetaKey(), [ 'listing_title' ] ) ) {
						unset( $fields[ $field->getObjectMetaKey() ] );
						continue;
					}

					$fields[ $field->getObjectMetaKey() ]['label']       = $field->getTitle();
					$fields[ $field->getObjectMetaKey() ]['description'] = $field->getDescription();
					$fields[ $field->getObjectMetaKey() ]['placeholder'] = $field->getPlaceholder();
					$fields[ $field->getObjectMetaKey() ]['readonly']    = $field->isReadOnly();

					if ( $field->getObjectMetaKey() !== 'listing_title' ) {
						$fields[ $field->getObjectMetaKey() ]['required'] = $field->isRequired();
					}
				} else {

					if ( $field->isAdminOnly() === true ) {
						continue;
					}

					$attributes = [
						'class'       => 'form-control',
						'placeholder' => ! empty( $field->getPlaceholder() ) ? esc_attr( $field->getPlaceholder() ) : false,
					];

					if ( $field->getType() === 'multicheckbox' || $field->getType() === 'radio' ) {
						unset( $attributes['class'] );
					}
					if ( $field->getType() === 'checkbox' || $field->getType() === 'term-checklist' ) {
						$attributes['class'] = 'custom-control-input';
					}
					if ( $field->getType() === 'select' ) {
						$attributes['class'] = 'custom-select';
					}
					if ( $field->getType() === 'multiselect' || $field->getType() === 'term-multiselect' ) {
						$attributes['data-placeholder'] = ! empty( $field->getPlaceholder() ) ? esc_attr( $field->getPlaceholder() ) : false;
					}
					if ( $field->getType() === 'textarea' ) {
						$attributes['rows'] = 3;
					}
					if ( $field->getType() === 'file' ) {
						$attributes['class'] = 'custom-file-input';
					}

					// The field does not exist so we now add it to the list of fields.
					$fields[ $field->getObjectMetaKey() ] = [
						'label'       => $field->getTitle(),
						'type'        => $field->getType(),
						'description' => $field->getDescription(),
						'placeholder' => $field->getPlaceholder(),
						'readonly'    => $field->isReadOnly(),
						'required'    => $field->isRequired(),
						'priority'    => $field->getPriority(),
						'attributes'  => $attributes,
					];

					if ( in_array( $field->getType(), pno_get_multi_options_field_types() ) ) {
						$fields[ $field->getObjectMetaKey() ]['values'] = $field->getOptions();
					}

					if ( in_array( $field->getType(), [ 'term-select', 'term-multiselect', 'term-checklist', 'term-chain-dropdown' ] ) && ! empty( $field->getTaxonomy() ) ) {
						$fields[ $field->getObjectMetaKey() ]['taxonomy'] = $field->getTaxonomy();
					}
				}

				if ( $field->getPriority() ) {
					$fields[ $field->getObjectMetaKey() ]['priority'] = $field->getPriority();
				}

				if ( $field->getType() === 'file' && ! empty( $field->getAllowedMimeTypes() ) ) {
					$fields[ $field->getObjectMetaKey() ]['allowed_mime_types'] = $field->getAllowedMimeTypes();
				}

				if ( $field->getType() == 'file' && ! empty( $field->getMaxSize() ) ) {
					$fields[ $field->getObjectMetaKey() ]['max_size'] = $field->getMaxSize();
				}

				$fields[ $field->getObjectMetaKey() ]['multiple'] = $field->isMultiple();

				if ( in_array( $field->getType(), [ 'term-multiselect', 'term-checklist', 'term-chain-dropdown' ] ) && ! empty( $field->getTaxonomy() ) ) {
					$fields[ $field->getObjectMetaKey() ]['taxonomy'] = $field->getTaxonomy();
					$fields[ $field->getObjectMetaKey() ]['multiple'] = true;
				}

				if ( $field->getType() === 'term-chain-dropdown' ) {
					$fields[ $field->getObjectMetaKey() ]['disable_branch_nodes'] = $field->isBranchNodesDisabled();
				}

				if ( ! empty( $field->getCssClasses() ) ) {
					$fields[ $field->getObjectMetaKey() ]['classes'] = $field->getCssClasses();
				}
			}
		}
	}

	/**
	 * Filter: allows developers to hook at the time when the fields query has been performed.
	 *
	 * @return array $fields
	 */
	$fields = apply_filters( 'pno_get_listing_submission_fields_query', $fields, $fields_query );

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
							$value = pno_serialize_stored_listing_terms( $listing_id );
							break;
						case 'listing_tags':
							$value = pno_serialize_stored_listing_terms( $listing_id, 'listings-tags' );
							break;
						case 'listing_regions':
							$value = pno_serialize_stored_listing_terms( $listing_id, 'listings-locations' );
							break;
						case 'listing_opening_hours':
							$value = wp_json_encode( get_post_meta( $listing_id, '_listing_opening_hours', true ) );
							break;
						case 'listing_featured_image':
							$featured_image_id = get_post_thumbnail_id( $listing_id );
							$value             = $featured_image_id;
							break;
						case 'listing_gallery':
							$gallery_images = get_post_meta( $listing_id, '_listing_gallery_images' );
							$attachments    = [];
							if ( isset( $gallery_images[0] ) && ! empty( $gallery_images[0] ) && is_array( $gallery_images[0] ) ) {
								foreach ( $gallery_images[0] as $image ) {
									$url           = isset( $image['url'][0]['value'] ) ? $image['url'][0]['value'] : $image['url'];
									$attachments[] = esc_url( $url );
								}
							}
							$value = wp_json_encode( $attachments );
							break;
						case 'listing_zipcode':
							$value = carbon_get_post_meta( $listing_id, 'listing_zipcode' );
							break;
						case 'listing_location':
							$value = wp_json_encode( carbon_get_post_meta( $listing_id, 'listing_location' ) );
							break;
					}
				} else {
					if ( isset( $field['taxonomy'] ) && ! empty( $field['taxonomy'] ) ) {

						$terms = wp_get_post_terms( $listing_id, $field['taxonomy'] );
						$value = [];

						if ( ! empty( $terms ) && is_array( $terms ) ) {
							foreach ( $terms as $found_term ) {
								$value[] = absint( $found_term->term_id );
							}
						}

						if ( ! isset( $field['multiple'] ) || isset( $field['multiple'] ) && $field['multiple'] === false && ( is_array( $value ) ) ) {
							$value = isset( $value[0] ) ? $value[0] : false;
						}
					} else {
						$value = carbon_get_post_meta( $listing_id, $key );
					}
				}
				if ( ! empty( $value ) ) {
					$fields[ $key ]['value'] = $value;
				}
			}
		}
	}

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
function pno_serialize_stored_listing_terms( $listing_id, $taxonomy = 'listings-categories' ) {

	if ( ! $listing_id ) {
		return;
	}

	$values = [];
	$cats   = wp_get_post_terms( $listing_id, $taxonomy );

	if ( ! empty( $cats ) && is_array( $cats ) ) {
		foreach ( $cats as $term ) {

			if ( isset( $term->term_id ) ) {
				$values[] = absint( $term->term_id );
			}
		}
	}

	return wp_json_encode( $values );

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
		'placeholder'     => __( 'Choose a category&hellip;', 'posterno' ),
		'no_results_text' => __( 'No results match', 'posterno' ),
		'multiple_text'   => __( 'Select Some Options', 'posterno' ),
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

/**
 * Helper function to retrieve all listings fields available within the database.
 *
 * @param array $exclude list of fields to exclude by meta key.
 * @return array
 */
function pno_get_listings_fields( $exclude = [] ) {

	$fields = remember_transient(
		'pno_get_listings_fields',
		function () {

			$found_fields = [];

			$args = [
				'number' => 300,
			];

			$listing_fields = new PNO\Database\Queries\Listing_Fields( $args );

			if ( ! empty( $listing_fields ) && isset( $listing_fields->items ) && is_array( $listing_fields->items ) ) {
				foreach ( $listing_fields->items as $field ) {
					$found_fields[] = [
						'name'    => $field->getTitle(),
						'type'    => $field->getType(),
						'meta'    => $field->getObjectMetaKey(),
						'options' => $field->getOptions(),
					];
				}
			}

			return $found_fields;

		}
	);

	if ( ! empty( $exclude ) && is_array( $exclude ) ) {
		foreach ( $fields as $key => $field ) {
			if ( in_array( $field['meta'], $exclude ) ) {
				unset( $fields[ $key ] );
			}
		}
	}

	return $fields;

}

/**
 * Delete custom fields and regenerate default ones.
 *
 * @param boolean $type the type of fields to regenerate.
 * @param integer $offset query offset.
 * @param integer $limit query number.
 * @return void
 */
function pno_reset_custom_fields_batch( $type = false, $offset = 0, $limit = 0 ) {

	if ( ! $type ) {
		return;
	}

	if ( $type === 'profile' ) {

		$fields = new \PNO\Database\Queries\Profile_Fields(
			[
				'number' => $limit,
				'offset' => $offset,
			]
		);

		if ( isset( $fields->items ) && ! empty( $fields->items ) ) {

			foreach ( $fields->items as $found_field ) {
				$found_field::delete( $found_field->getPostID(), true );
			}

			posterno()->queue->schedule_single(
				time(),
				'pno_reset_custom_fields_batch',
				[
					'type'   => $type,
					'offset' => $offset + $limit,
					'limit'  => $limit,
				],
				'pno_reset_custom_fields_batch'
			);

		}

		if ( ! isset( $fields->items ) || isset( $fields->items ) && empty( $fields->items ) ) {
			delete_option( 'pno_profile_fields_installed' );
			delete_option( 'pno_background_custom_fields_generation' );
			pno_install_profile_fields( true );
		}
	} elseif ( $type === 'registration' ) {

		$fields = new \PNO\Database\Queries\Registration_Fields(
			[
				'number' => $limit,
				'offset' => $offset,
			]
		);

		if ( isset( $fields->items ) && ! empty( $fields->items ) ) {

			foreach ( $fields->items as $found_field ) {
				$found_field::delete( $found_field->getPostID(), true );
			}

			posterno()->queue->schedule_single(
				time(),
				'pno_reset_custom_fields_batch',
				[
					'type'   => $type,
					'offset' => $offset + $limit,
					'limit'  => $limit,
				],
				'pno_reset_custom_fields_batch'
			);

		}

		if ( ! isset( $fields->items ) || isset( $fields->items ) && empty( $fields->items ) ) {
			delete_option( 'pno_registration_fields_installed' );
			delete_option( 'pno_background_custom_fields_generation' );
			pno_install_registration_fields();
		}
	} elseif ( $type === 'listings' ) {

		$fields = new \PNO\Database\Queries\Listing_Fields(
			[
				'number' => $limit,
				'offset' => $offset,
			]
		);

		if ( isset( $fields->items ) && ! empty( $fields->items ) ) {

			foreach ( $fields->items as $found_field ) {
				$found_field::delete( $found_field->getPostID(), true );
			}

			posterno()->queue->schedule_single(
				time(),
				'pno_reset_custom_fields_batch',
				[
					'type'   => $type,
					'offset' => $offset + $limit,
					'limit'  => $limit,
				],
				'pno_reset_custom_fields_batch'
			);

		}

		if ( ! isset( $fields->items ) || isset( $fields->items ) && empty( $fields->items ) ) {
			delete_option( 'pno_listings_fields_installed' );
			delete_option( 'pno_background_custom_fields_generation' );
			pno_install_listings_fields();
		}
	}

}
add_action( 'pno_reset_custom_fields_batch', 'pno_reset_custom_fields_batch', 10 );
