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

	uasort( $fields, 'pno_sort_array_by_priority' );

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
 * @param string  $user_id user id.
 * @param boolean $admin_request flag to determine if the function is an admin request or not.
 * @return array
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

	// Remove the avatar field when option disabled.
	if ( ! pno_get_option( 'allow_avatars' ) && isset( $fields['avatar'] ) ) {
		unset( $fields['avatar'] );
	}

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
 * @param string  $field_key field key.
 * @param object  $field field object.
 * @param boolean $form form name.
 * @param string  $class optional classes.
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
 * @param string  $field_key field key.
 * @param object  $field field object.
 * @param boolean $form form name.
 * @param string  $class optional classes.
 * @return void
 */
function pno_form_field_class( $field_key, $field, $form = false, $class = '' ) {
	// Separates classes with a single space, collates classes for body element.
	echo 'class="' . join( ' ', pno_get_form_field_class( $field_key, $field, $form, $class ) ) . '"';
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
 * Displays category select dropdown.
 * Based on wp_dropdown_categories, with the exception of supporting multiple selected categories.
 *
 * @see  wp_dropdown_categories
 * @param string|array|object $args args.
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
		'class'           => 'form-control pno-category-dropdown ' . ( is_rtl() ? 'chosen-rtl' : '' ),
		'depth'           => 0,
		'taxonomy'        => 'listings-categories',
		'value'           => 'id',
		'multiple'        => true,
		'show_option_all' => false,
		'placeholder'     => __( 'Choose a category&hellip;' ),
		'no_results_text' => __( 'No results match' ),
		'multiple_text'   => __( 'Select some options' ),
	);
	$r        = wp_parse_args( $args, $defaults );
	if ( ! isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] ) {
		$r['pad_counts'] = true;
	}

	// Store in a transient to help sites with many cats.
	$categories_hash = 'pno_cats_' . md5( wp_json_encode( $r ) . \PNO\Cache\Helper::get_transient_version( 'pno_get_' . $r['taxonomy'] ) );
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
	$id     = $r['id'] ? $r['id'] : $r['name'];
	$output = "<select name='" . esc_attr( $r['name'] ) . "[]' id='" . esc_attr( $id ) . "' class='" . esc_attr( $r['class'] ) . "' " . ( $r['multiple'] ? "multiple='multiple'" : '' ) . " data-placeholder='" . esc_attr( $r['placeholder'] ) . "' data-no_results_text='" . esc_attr( $r['no_results_text'] ) . "' data-multiple_text='" . esc_attr( $r['multiple_text'] ) . "'>\n";
	if ( $r['show_option_all'] ) {
		$output .= '<option value="">' . esc_html( $r['show_option_all'] ) . '</option>';
	}
	if ( ! empty( $categories ) ) {
		include_once PNO_PLUGIN_DIR . '/includes/class-pno-category-walker.php';
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
 * Get js settings for the listings submission form.
 *
 * @return array
 */
function pno_get_listings_submission_form_js_vars() {

	$js_settings = [
		'selected_listing_type' => isset( $_POST['pno_listing_type_id'] ) && ! empty( sanitize_text_field( $_POST['pno_listing_type_id'] ) ) ? absint( $_POST['pno_listing_type_id'] ) : false, //phpcs:ignore
		'max_multiselect'       => absint( pno_get_option( 'submission_categories_amount' ) ),
	];

	return apply_filters( 'pno_listings_submission_form_js_vars', $js_settings );

}

/**
 * Retrieve the list of fields for the listings submission form.
 *
 * @return array
 */
function pno_get_listing_submission_fields() {

	$fields = [];

	$fields['submit'] = [
		'listing_title'                 => [
			'label'    => esc_html__( 'Listing title' ),
			'type'     => 'text',
			'required' => true,
			'priority' => 1,
		],
		'listing_description'           => [
			'label'    => esc_html__( 'Description' ),
			'type'     => 'editor',
			'required' => true,
			'priority' => 2,
		],
		'listing_email_address'         => [
			'label'    => esc_html__( 'Email address' ),
			'type'     => 'email',
			'required' => false,
			'priority' => 3,
		],
		'listing_phone_number'          => [
			'label'    => esc_html__( 'Phone number' ),
			'type'     => 'text',
			'required' => false,
			'priority' => 4,
		],
		'listing_website'               => [
			'label'    => esc_html__( 'Website' ),
			'type'     => 'url',
			'required' => false,
			'priority' => 5,
		],
		'listing_video'                 => [
			'label'    => esc_html__( 'Video' ),
			'type'     => 'url',
			'required' => false,
			'priority' => 6,
		],
		'listing_social_media_profiles' => [
			'label'    => esc_html__( 'Social media profiles' ),
			'type'     => 'social-profiles',
			'required' => false,
			'priority' => 7,
		],
		'listing_categories'            => [
			'label'    => esc_html__( 'Listing category' ),
			'type'     => 'listing-category',
			'required' => true,
			'priority' => 8,
			'search'   => true,
		],
	];

	/**
	 * Allow developers to customize the listings submission form fields.
	 *
	 * @param array $fields the list of fields.
	 * @return array $fields
	 */
	return apply_filters( 'pno_listing_submission_fields', $fields );

}
