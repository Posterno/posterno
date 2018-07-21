<?php
/**
 * List of functions used all around within the plugin.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Retrieve the ID number of the selected login page.
 *
 * @return mixed
 */
function pno_get_login_page_id() {

	$login_page  = false;
	$page_option = pno_get_option( 'login_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$login_page = absint( $page_option['value'] );
	}

	return $login_page;

}

/**
 * Retrieve the ID number of the selected registration page.
 *
 * @return mixed
 */
function pno_get_registration_page_id() {

	$registration_page = false;
	$page_option       = pno_get_option( 'registration_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$registration_page = absint( $page_option['value'] );
	}

	return $registration_page;

}

/**
 * Retrieve the ID number of the selected password recovery page.
 *
 * @return mixed
 */
function pno_get_password_recovery_page_id() {

	$password_page = false;
	$page_option   = pno_get_option( 'password_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$password_page = absint( $page_option['value'] );
	}

	return $password_page;

}

/**
 * Retrieve the ID number of the selected password recovery page.
 *
 * @return mixed
 */
function pno_get_dashboard_page_id() {

	$dashboard_page = false;
	$page_option    = pno_get_option( 'dashboard_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$dashboard_page = absint( $page_option['value'] );
	}

	return $dashboard_page;

}

/**
 * Retrieve the ID number of the selected password recovery page.
 *
 * @return mixed
 */
function pno_get_profile_page_id() {

	$profile_page = false;
	$page_option  = pno_get_option( 'profile_page' );

	if ( is_array( $page_option ) && isset( $page_option['value'] ) ) {
		$profile_page = absint( $page_option['value'] );
	}

	return $profile_page;

}

/**
 * Retrieve the list of registration form fields.
 *
 * @return void
 */
function pno_get_registration_fields() {

	$fields = array(
		'registration' => array(
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
		),
	);

	if ( pno_get_option( 'enable_role_selection' ) ) {
		$fields['registration']['role'] = array(
			'label'    => __( 'Register as:' ),
			'type'     => 'select',
			'required' => true,
			'options'  => pno_get_allowed_user_roles(),
			'priority' => 99,
			'value'    => get_option( 'default_role' ),
		);
	}

	$fields['registration']['robo'] = [
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
			$fields['registration']['terms'] = array(
				'label'    => apply_filters( 'pno_terms_text', sprintf( __( 'By registering to this website you agree to the <a href="%s" target="_blank">terms &amp; conditions</a>.' ), get_permalink( $terms_page ) ) ),
				'type'     => 'checkbox',
				'required' => true,
				'priority' => 101,
			);
		}
	}

	if ( get_option( 'wp_page_for_privacy_policy' ) ) {
		$fields['registration']['privacy'] = array(
			'label'    => apply_filters( 'wpum_privacy_text', sprintf( __( 'I have read and accept the <a href="%1$s" target="_blank">privacy policy</a> and allow "%2$s" to collect and store the data I submit through this form.' ), get_permalink( get_option( 'wp_page_for_privacy_policy' ) ), get_bloginfo( 'name' ) ) ),
			'type'     => 'checkbox',
			'required' => true,
			'priority' => 102,
		);
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
 * @return void
 */
function pno_get_account_fields( $user_id = false ) {

	$fields = [
		'avatar'      => [
			'label'              => esc_html__( 'Profile picture' ),
			'type'               => 'file',
			'required'           => false,
			'placeholder'        => '',
			'priority'           => 0,
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
			'priority'    => 1,
		],
		'last_name'   => [
			'label'       => esc_html__( 'Last name' ),
			'type'        => 'text',
			'required'    => true,
			'placeholder' => '',
			'priority'    => 2,
		],
		'email'       => [
			'label'       => esc_html__( 'Email address' ),
			'type'        => 'email',
			'required'    => true,
			'placeholder' => '',
			'priority'    => 3,
		],
		'website'     => [
			'label'       => esc_html__( 'Website' ),
			'type'        => 'text',
			'required'    => false,
			'placeholder' => '',
			'priority'    => 4,
		],
		'description' => [
			'label'       => esc_html__( 'About me' ),
			'type'        => 'textarea',
			'required'    => false,
			'placeholder' => '',
			'priority'    => 5,
		],
	];

	// Load fields from the database and merge it with the default settings.
	$fields_query_args = [
		'post_type'              => 'pno_users_fields',
		'posts_per_page'         => 100,
		'nopaging'               => true,
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
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
					$fields[ $field->get_meta() ]['label']       = $field->get_label();
					$fields[ $field->get_meta() ]['description'] = $field->get_description();
					$fields[ $field->get_meta() ]['placeholder'] = $field->get_placeholder();
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
 * Defines a list of navigation items for the dashboard page.
 *
 * @return array
 */
function pno_get_dashboard_navigation_items() {

	$items = [
		'dashboard'    => [
			'name'     => esc_html__( 'Dashboard' ),
			'priority' => 0,
		],
		'edit-account' => [
			'name'     => esc_html__( 'Account details' ),
			'priority' => 1,
		],
		'password'     => [
			'name'     => esc_html__( 'Change password' ),
			'priority' => 2,
		],
		'privacy'      => [
			'name'     => esc_html__( 'Privacy settings' ),
			'priority' => 3,
		],
		'logout'       => [
			'name'     => esc_html__( 'Logout' ),
			'priority' => 13,
		],
	];

	/**
	 * Allows developers to register or deregister navigation items
	 * for the dashboard menu.
	 *
	 * @param array $items
	 */
	$items = apply_filters( 'pno_dashboard_navigation_items', $items );

	uasort( $items, 'pno_sort_array_by_priority' );

	$first                       = key( $items );
	$items[ $first ]['is_first'] = true;

	return $items;

}

/**
 * Prepares files for upload by standardizing them into an array. This adds support for multiple file upload fields.
 *
 * @since 0.1.0
 * @param  array $file_data
 * @return array
 */
function pno_prepare_uploaded_files( $file_data ) {
	$files_to_upload = array();
	if ( is_array( $file_data['name'] ) ) {
		foreach ( $file_data['name'] as $file_data_key => $file_data_value ) {
			if ( $file_data['name'][ $file_data_key ] ) {
				$type              = wp_check_filetype( $file_data['name'][ $file_data_key ] ); // Map mime type to one WordPress recognises.
				$files_to_upload[] = array(
					'name'     => $file_data['name'][ $file_data_key ],
					'type'     => $type['type'],
					'tmp_name' => $file_data['tmp_name'][ $file_data_key ],
					'error'    => $file_data['error'][ $file_data_key ],
					'size'     => $file_data['size'][ $file_data_key ],
				);
			}
		}
	} else {
		$type              = wp_check_filetype( $file_data['name'] ); // Map mime type to one WordPress recognises.
		$file_data['type'] = $type['type'];
		$files_to_upload[] = $file_data;
	}
	return apply_filters( 'pno_prepare_uploaded_files', $files_to_upload );
}

/**
 * Uploads a file using WordPress file API.
 *
 * @since  0.1.0
 * @param  array|WP_Error      $file Array of $_FILE data to upload.
 * @param  string|array|object $args Optional arguments.
 * @return stdClass|WP_Error Object containing file information, or error.
 */
function pno_upload_file( $file, $args = array() ) {

	global $pno_upload, $pno_uploading_file;

	include_once ABSPATH . 'wp-admin/includes/file.php';
	include_once ABSPATH . 'wp-admin/includes/media.php';

	$args               = wp_parse_args(
		$args,
		array(
			'file_key'           => '',
			'file_label'         => '',
			'allowed_mime_types' => '',
		)
	);
	$pno_upload         = true;
	$pno_uploading_file = $args['file_key'];
	$uploaded_file      = new stdClass();
	if ( '' === $args['allowed_mime_types'] ) {
		$allowed_mime_types = pno_get_allowed_mime_types( $pno_uploading_file );
	} else {
		$allowed_mime_types = $args['allowed_mime_types'];
	}
	/**
	 * Filter file configuration before upload
	 *
	 * This filter can be used to modify the file arguments before being uploaded, or return a WP_Error
	 * object to prevent the file from being uploaded, and return the error.
	 *
	 * @since 0.1.0
	 *
	 * @param array $file               Array of $_FILE data to upload.
	 * @param array $args               Optional file arguments.
	 * @param array $allowed_mime_types Array of allowed mime types from field config or defaults.
	 */
	$file = apply_filters( 'pno_upload_file_pre_upload', $file, $args, $allowed_mime_types );

	if ( is_wp_error( $file ) ) {
		return $file;
	}
	if ( ! in_array( $file['type'], $allowed_mime_types, true ) ) {
		if ( $args['file_label'] ) {
			// translators: %1$s is the file field label; %2$s is the file type; %3$s is the list of allowed file types.
			return new WP_Error( 'upload', sprintf( __( '"%1$s" (filetype %2$s) needs to be one of the following file types: %3$s' ), $args['file_label'], $file['type'], implode( ', ', array_keys( $allowed_mime_types ) ) ) );
		} else {
			// translators: %s is the list of allowed file types.
			return new WP_Error( 'upload', sprintf( __( 'Uploaded files need to be one of the following file types: %s' ), implode( ', ', array_keys( $allowed_mime_types ) ) ) );
		}
	} else {
		$upload = wp_handle_upload( $file, apply_filters( 'submit_pno_handle_upload_overrides', array( 'test_form' => false ) ) );
		if ( ! empty( $upload['error'] ) ) {
			return new WP_Error( 'upload', $upload['error'] );
		} else {
			$uploaded_file->url       = $upload['url'];
			$uploaded_file->file      = $upload['file'];
			$uploaded_file->name      = basename( $upload['file'] );
			$uploaded_file->type      = $upload['type'];
			$uploaded_file->size      = $file['size'];
			$uploaded_file->extension = substr( strrchr( $uploaded_file->name, '.' ), 1 );
		}
	}
	$pno_upload         = false;
	$pno_uploading_file = '';
	return $uploaded_file;
}

/**
 * Returns mime types specifically for PNO.
 *
 * @since   0.1.0
 * @param   string $field Field used.
 * @return  array  Array of allowed mime types
 */
function pno_get_allowed_mime_types( $field = '' ) {
	if ( 'avatar' === $field ) {
		$allowed_mime_types = array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'gif'          => 'image/gif',
			'png'          => 'image/png',
		);
	} else {
		$allowed_mime_types = array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'gif'          => 'image/gif',
			'png'          => 'image/png',
			'pdf'          => 'application/pdf',
			'doc'          => 'application/msword',
			'docx'         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		);
	}
	/**
	 * Mime types to accept in uploaded files.
	 *
	 * Default is image, pdf, and doc(x) files.
	 *
	 * @since 1.25.1
	 *
	 * @param array  {
	 *     Array of allowed file extensions and mime types.
	 *     Key is pipe-separated file extensions. Value is mime type.
	 * }
	 * @param string $field The field key for the upload.
	 */
	return apply_filters( 'pno_mime_types', $allowed_mime_types, $field );
}
