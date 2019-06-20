<?php
/**
 * All the functions that are only used within the admin panel.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Retrieve pages from the database and cache them as transient.
 *
 * @param boolean $force whether to force the loading.
 * @return array
 */
function pno_get_pages( $force = false ) {

	$pages = [];

	if ( ( ! isset( $_GET['page'] ) || strpos( $_GET['page'], 'posterno-options' ) !== 0 ) && ! $force ) {
		return $pages;
	}

	$available_pages = get_pages();

	if ( ! empty( $available_pages ) ) {
		foreach ( $available_pages as $page ) {
			$pages[ $page->ID ] = $page->post_title;
		}
	}

	return $pages;
}

/**
 * Load tinymce plugin
 *
 * @access public
 * @since  0.1.0
 * @return $plugin_array
 */
function pno_shortcodes_add_tinymce_plugin( $plugin_array ) {

	$plugin_array['pno_shortcodes_mce_button'] = apply_filters( 'pno_shortcodes_tinymce_js_file_url', PNO_PLUGIN_URL . 'assets/js/frontend/tinymce.min.js' );

	return $plugin_array;

}

/**
 * Load tinymce button
 *
 * @access public
 * @since  1.0.0
 * @return $buttons
 */
function pno_shortcodes_register_mce_button( $buttons ) {

	array_push( $buttons, 'pno_shortcodes_mce_button' );

	return $buttons;

}

/**
 * Retrieve the options for the available login methods.
 *
 * @return array
 */
function pno_get_login_methods() {
	return apply_filters(
		'pno_get_login_methods',
		array(
			'username'       => __( 'Username only', 'posterno' ),
			'email'          => __( 'Email only', 'posterno' ),
			'username_email' => __( 'Username or Email', 'posterno' ),
		)
	);
}

/**
 * Retrieve a list of all user roles and cache them into a transient.
 *
 * @param boolean $force set to true if loading outside the pno settings.
 * @param boolean $admin set to true to load the admin role too.
 * @return array
 */
function pno_get_roles( $force = false, $admin = false ) {

	global $wp_roles;

	$roles = [];

	if ( ( ! isset( $_GET['page'] ) || strpos( $_GET['page'], 'posterno-options' ) !== 0 ) && ! $force ) {
		return $roles;
	}

	$available_roles = $wp_roles->get_names();

	foreach ( $available_roles as $role_id => $role ) {
		if ( $role_id == 'administrator' && ! $admin ) {
			continue;
		}
		$roles[ esc_attr( $role_id ) ] = esc_html( $role );
	}

	return $roles;
}

/**
 * Install the profile fields within the database.
 * This function is usually used within the plugin's activation.
 *
 * @return void
 */
function pno_install_profile_fields( $bypass = false ) {

	// Bail if this was already done.
	if ( get_option( 'pno_profile_fields_installed' ) ) {
		return;
	}

	$registered_fields = wp_list_filter( pno_get_account_fields( false, $bypass ), [ 'default_field' => true ] );

	if ( ! is_array( $registered_fields ) ) {
		return;
	}

	if ( empty( $registered_fields ) ) {
		return;
	}

	foreach ( $registered_fields as $field_key => $field ) {

		if ( pno_is_default_field( $field_key ) ) {

			$new_field = [
				'post_type'   => 'pno_users_fields',
				'post_title'  => $field['label'],
				'post_status' => 'publish',
			];

			$field_id = wp_insert_post( $new_field );

			if ( is_wp_error( $field_id ) ) {
				return $field_id;
			} else {

				$dbfield = new \PNO\Database\Queries\Profile_Fields();

				$db_settings = [
					'_profile_field_meta_key' => $field_key,
				];

				// Setup the field's type.
				$registered_field_types = pno_get_registered_field_types();

				if ( isset( $field['type'] ) && isset( $registered_field_types[ $field['type'] ] ) ) {
					$db_settings['_profile_field_type'] = $field['type'];
				}

				// Assign a description if one is given.
				if ( isset( $field['description'] ) && ! empty( $field['description'] ) ) {
					$db_settings['_profile_field_description'] = $field['description'];
				}

				// Assign a placeholder if one is given.
				if ( isset( $field['placeholder'] ) && ! empty( $field['placeholder'] ) ) {
					$db_settings['_profile_field_placeholder'] = esc_html( $field['placeholder'] );
				}

				// Make field required if defined.
				if ( isset( $field['required'] ) && $field['required'] === true ) {
					$db_settings['_profile_field_is_required'] = true;
				}

				// Set priority.
				if ( isset( $field['priority'] ) && ! empty( $field['priority'] ) ) {
					$db_settings['_profile_field_priority'] = absint( $field['priority'] );
				}

				// Mark the field as a default one.
				if ( pno_is_default_field( $field_key ) ) {
					$db_settings['_profile_is_default_field'] = true;
				}

				if ( $field_key === 'avatar' ) {
					$db_settings['_profile_field_file_extensions'] = [
						'image/jpeg',
						'image/png',
					];
				}

				$dbfield->add_item(
					[
						'post_id'       => $field_id,
						'settings'      => maybe_serialize( $db_settings ),
						'user_meta_key' => $field_key,
					]
				);

			}

			wp_reset_postdata();

		}
	}

	update_option( 'pno_profile_fields_installed', true );

}

/**
 * Install the registration fields within the database.
 * This function is usually used within the plugin's activation.
 *
 * @return void
 */
function pno_install_registration_fields() {

	// Bail if this was already done.
	if ( get_option( 'pno_registration_fields_installed' ) ) {
		return;
	}

	$registered_fields = [
		'username' => [
			'label'    => esc_html__( 'Username', 'posterno' ),
			'type'     => 'text',
			'required' => true,
			'priority' => 1,
		],
		'email'    => [
			'label'    => esc_html__( 'Email address', 'posterno' ),
			'type'     => 'email',
			'required' => true,
			'priority' => 2,
		],
		'password' => [
			'label'    => esc_html__( 'Password', 'posterno' ),
			'type'     => 'password',
			'required' => true,
			'priority' => 3,
		],
	];

	foreach ( $registered_fields as $key => $field ) {

		$new_field = [
			'post_type'   => 'pno_signup_fields',
			'post_title'  => $field['label'],
			'post_status' => 'publish',
		];

		$field_id = wp_insert_post( $new_field );

		if ( ! is_wp_error( $field_id ) ) {

			$dbfield = new \PNO\Database\Queries\Registration_Fields();

			$db_settings = [
				'_registration_field_is_default' => $key,
			];

			// Mark fields as required.
			if ( isset( $field['required'] ) && $field['required'] === true ) {
				$db_settings['_registration_field_is_required'] = true;
			}

			// Setup the priority of this field.
			if ( isset( $field['priority'] ) ) {
				$db_settings['_registration_field_priority'] = $field['priority'];
			}

			$dbfield->add_item(
				[
					'post_id'  => $field_id,
					'settings' => maybe_serialize( $db_settings ),
				]
			);

		}
	}

	update_option( 'pno_registration_fields_installed', true );

}

/**
 * Install the default listings submission fields.
 *
 * @return void
 */
function pno_install_listings_fields() {

	// Bail if this was already done.
	if ( get_option( 'pno_listings_fields_installed' ) ) {
		return;
	}

	$registered_fields = wp_list_filter( pno_get_listing_submission_fields(), [ 'default_field' => true ] );

	foreach ( $registered_fields as $key => $field ) {

		$new_field = [
			'post_type'   => 'pno_listings_fields',
			'post_title'  => $field['label'],
			'post_status' => 'publish',
		];

		$field_id = wp_insert_post( $new_field );

		if ( ! is_wp_error( $field_id ) ) {

			$dbfield = new \PNO\Database\Queries\Listing_Fields();

			// Setup the field's type.
			$registered_field_types = pno_get_registered_field_types();

			$db_settings = [
				'_listing_field_is_default' => true,
				'_listing_field_meta_key'   => $key,
				'_listing_field_type'       => isset( $field['type'] ) && isset( $registered_field_types[ $field['type'] ] ) ? esc_attr( $field['type'] ) : 'text',
			];

			// Mark fields as required.
			if ( isset( $field['required'] ) && $field['required'] === true ) {
				$db_settings['_listing_field_is_required'] = true;
			}

			// Setup the priority of this field.
			if ( isset( $field['priority'] ) ) {
				$db_settings['_listing_field_priority'] = absint( $field['priority'] );
			}

			// Assign a description if one is given.
			if ( isset( $field['description'] ) && ! empty( $field['description'] ) ) {
				$db_settings['_listing_field_description'] = esc_html( $field['description'] );
			}

			// Assign a placeholder if one is given.
			if ( isset( $field['placeholder'] ) && ! empty( $field['placeholder'] ) ) {
				$db_settings['_listing_field_placeholder'] = esc_html( $field['placeholder'] );
			}

			// Assign taxonomies to field if specified - doesn't matter what field type it is.
			if ( isset( $field['taxonomy'] ) && ! empty( $field['taxonomy'] ) ) {
				$db_settings['_listing_field_taxonomy'] = $field['taxonomy'];
			}

			// Disable branch node on some tree-select fields.
			if ( isset( $field['taxonomy'] ) && $field['taxonomy'] === 'listings-regions' ) {
				$db_settings['_listing_field_disable_branch_nodes'] = true;
			}

			// Media gallery field must be set as multiple by default.
			if ( $key === 'listing_gallery' ) {
				$db_settings['_listing_field_file_is_multiple'] = true;
				$db_settings['_listing_field_file_extensions']  = [
					'image/jpeg',
					'image/png',
				];
			}

			if ( $key === 'listing_featured_image' ) {
				$db_settings['_listing_field_file_extensions'] = [
					'image/jpeg',
					'image/png',
				];
			}

			$dbfield->add_item(
				[
					'post_id'          => $field_id,
					'listing_meta_key' => $key,
					'settings'         => maybe_serialize( $db_settings ),
				]
			);

		}
	}

	update_option( 'pno_listings_fields_installed', true );

}

/**
 * Install email types into the database.
 *
 * @return void
 */
function pno_install_email_types() {
	$types = pno_email_get_type_schema();

	foreach ( $types as $type_id => $type ) {
		if ( ! term_exists( $type_id, 'pno-email-type' ) ) {
			wp_insert_term(
				$type_id,
				'pno-email-type',
				array(
					'description' => $type['description'],
					'slug'        => $type_id,
				)
			);
		}
	}

}

/**
 * Retrieve a list of listings types when within the listings categories panel.
 * This function is used only within the admin panel.
 *
 * @return array
 */
function pno_get_listings_types_for_association() {
	$types = [];

	$terms = remember_transient(
		'pno_get_listings_types_for_association',
		function() {
			return get_terms(
				'listings-types',
				array(
					'hide_empty' => false,
					'number'     => 999,
					'orderby'    => 'name',
					'order'      => 'ASC',
				)
			);
		}
	);

	if ( ! empty( $terms ) && is_array( $terms ) ) {
		foreach ( $terms as $listing_type ) {
			$types[ absint( $listing_type->term_id ) ] = esc_html( $listing_type->name );
		}
	}

	return $types;

}

/**
 * Retrieve a list of listings categories when within the listings tags panel.
 * This function is used only within the admin panel.
 *
 * @return array
 */
function pno_get_listings_categories_for_association() {
	$categories = [];

	$terms = get_terms(
		'listings-categories',
		array(
			'hide_empty' => false,
			'number'     => 999,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'parent'     => 0,
		)
	);

	if ( ! empty( $terms ) ) {
		foreach ( $terms as $listing_cat ) {
			$categories[ absint( $listing_cat->term_id ) ] = esc_html( $listing_cat->name );
		}
	}

	return $categories;

}

/**
 * Retrieve a list of listings tags.
 * This function is used only within the admin panel.
 *
 * @return array
 */
function pno_get_listings_tags_for_association() {
	$tags = [];

	$terms = get_terms(
		'listings-tags',
		array(
			'hide_empty' => false,
			'number'     => 999,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( ! empty( $terms ) ) {
		foreach ( $terms as $tag ) {
			$tags[ absint( $tag->term_id ) ] = esc_html( $tag->name );
		}
	}

	return $tags;

}

/**
 * Get email situations in an ordered list.
 *
 * @return array
 */
function pno_get_emails_situations() {
	$types = [];

	$terms = get_terms(
		'pno-email-type',
		array(
			'hide_empty' => false,
			'number'     => 999,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( ! empty( $terms ) ) {
		foreach ( $terms as $type ) {
			$types[ absint( $type->term_id ) ] = esc_html( $type->description );
		}
	}

	return $types;

}

/**
 * Retrieve ordering options available for listings.
 *
 * @return array
 */
function pno_get_listings_order_options() {
	return wp_list_pluck( pno_get_listings_results_order_filters(), 'label' );
}

/**
 * Retrive layout options available for listings.
 *
 * @return array
 */
function pno_get_listings_layout_available_options() {
	return wp_list_pluck( pno_get_listings_layout_options(), 'label' );
}

/**
 * Retrieve options for the "Move featured listings at the top of the list when sorting by" setting.
 * Removes the random option since it's not needed.
 *
 * @return array
 */
function pno_get_listings_featured_order_options() {

	$options = wp_list_pluck( pno_get_listings_results_order_filters(), 'label' );

	unset( $options['random'] );

	return $options;

}

/**
 * Retrieve a list of profile fields for widget's association.
 *
 * @return array
 */
function pno_get_profile_fields_for_widget_association() {

	$not_needed = [
		'avatar',
		'first_name',
		'last_name',
	];

	$fields = remember_transient(
		'pno_profile_fields_list_for_widget_association',
		function () use ( $not_needed ) {

			$found_fields = [];

			/**
			 * Filter: adjusts the query arguments for the profile fields.
			 *
			 * @param array $args
			 * @return array
			 */
			$args = apply_filters(
				'pno_profile_fields_widget_association_args',
				[
					'number'                => 100,
					'user_meta_key__not_in' => $not_needed,
				]
			);

			$profile_fields = new PNO\Database\Queries\Profile_Fields( $args );

			if ( ! empty( $profile_fields ) && isset( $profile_fields->items ) && is_array( $profile_fields->items ) ) {
				foreach ( $profile_fields->items as $field ) {
					$found_fields[ $field->getObjectMetaKey() ] = $field->getTitle();
				}
			}

			return $found_fields;

		}
	);

	asort( $fields );

	return $fields;

}

/**
 * Retrieve a list of listings fields for widget's association.
 *
 * @return array
 */
function pno_get_listings_fields_for_widget_association() {

	$not_needed = [
		'listing_title',
		'listing_description',
		'listing_opening_hours',
		'listing_featured_image',
		'listing_gallery',
		'listing_location',
		'listing_categories',
		'listing_tags',
		'listing_regions',
		'listing_video',
	];

	$fields = remember_transient(
		'pno_listings_fields_list_for_widget_association',
		function () use ( $not_needed ) {

			$found_fields = [];

			/**
			 * Filter: adjusts the query arguments for the listings fields.
			 *
			 * @param array $args
			 * @return array
			 */
			$args = apply_filters(
				'pno_listings_fields_widget_association_args',
				[
					'number'                   => 100,
					'listing_meta_key__not_in' => $not_needed,
				]
			);

			$listing_fields = new PNO\Database\Queries\Listing_Fields( $args );

			if ( ! empty( $listing_fields ) && isset( $listing_fields->items ) && is_array( $listing_fields->items ) ) {
				foreach ( $listing_fields->items as $field ) {

					if ( ! empty( $field->getTaxonomy() ) ) {
						continue;
					}

					$found_fields[ $field->getObjectMetaKey() ] = $field->getTitle();
				}
			}

			return $found_fields;

		}
	);

	asort( $fields );

	return $fields;

}

/**
 * Retrieve a list of registered taxonomies for the listings post type.
 *
 * @return array
 */
function pno_get_registered_listings_taxonomies() {

	$list = [];

	$taxonomies = get_object_taxonomies( 'listings', 'objects' );

	if ( ! empty( $taxonomies ) && is_array( $taxonomies ) ) {
		foreach ( $taxonomies as $taxonomy ) {
			$list[ $taxonomy->name ] = $taxonomy->label;
		}
	}

	return $list;

}

/**
 * Determine whether or not we should install and create required pages.
 *
 * @return void
 */
function pno_install_pages() {

	if ( ! pno_get_option( 'login_page' ) ) {
		$login = wp_insert_post(
			array(
				'post_title'     => esc_html__( 'Login', 'posterno' ),
				'post_content'   => '[pno_login_form]',
				'post_status'    => 'publish',
				'post_author'    => get_current_user_id(),
				'post_type'      => 'page',
				'comment_status' => 'closed',
			)
		);
		pno_update_option( 'login_page', [ $login ] );
	}

	if ( ! pno_get_option( 'password_page' ) ) {
		$psw = wp_insert_post(
			array(
				'post_title'     => esc_html__( 'Forgot password', 'posterno' ),
				'post_content'   => '[pno_password_recovery_form]',
				'post_status'    => 'publish',
				'post_author'    => get_current_user_id(),
				'post_type'      => 'page',
				'comment_status' => 'closed',
			)
		);
		pno_update_option( 'password_page', [ $psw ] );
	}

	if ( ! pno_get_option( 'registration_page' ) ) {
		$registration_page = wp_insert_post(
			array(
				'post_title'     => esc_html__( 'Register', 'posterno' ),
				'post_content'   => '[pno_registration_form]',
				'post_status'    => 'publish',
				'post_author'    => get_current_user_id(),
				'post_type'      => 'page',
				'comment_status' => 'closed',
			)
		);
		pno_update_option( 'registration_page', [ $registration_page ] );
	}

	if ( ! pno_get_option( 'dashboard_page' ) ) {
		$dashboard_page = wp_insert_post(
			array(
				'post_title'     => esc_html__( 'Dashboard', 'posterno' ),
				'post_content'   => '[pno_dashboard]',
				'post_status'    => 'publish',
				'post_author'    => get_current_user_id(),
				'post_type'      => 'page',
				'comment_status' => 'closed',
			)
		);
		pno_update_option( 'dashboard_page', [ $dashboard_page ] );
	}

	if ( ! pno_get_option( 'submission_page' ) ) {
		$submission_page = wp_insert_post(
			array(
				'post_title'     => esc_html__( 'Submit listing', 'posterno' ),
				'post_content'   => '[pno_listing_submission_form]',
				'post_status'    => 'publish',
				'post_author'    => get_current_user_id(),
				'post_type'      => 'page',
				'comment_status' => 'closed',
			)
		);
		pno_update_option( 'submission_page', [ $submission_page ] );
	}

	if ( ! pno_get_option( 'editing_page' ) ) {
		$editing_page = wp_insert_post(
			array(
				'post_title'     => esc_html__( 'Edit listing', 'posterno' ),
				'post_content'   => '[pno_listing_editing_form]',
				'post_status'    => 'publish',
				'post_author'    => get_current_user_id(),
				'post_type'      => 'page',
				'comment_status' => 'closed',
			)
		);
		pno_update_option( 'editing_page', [ $editing_page ] );
	}

	if ( ! pno_get_option( 'profile_page' ) ) {
		$profile_page = wp_insert_post(
			array(
				'post_title'     => esc_html__( 'Profile', 'posterno' ),
				'post_content'   => '[pno_profile]',
				'post_status'    => 'publish',
				'post_author'    => get_current_user_id(),
				'post_type'      => 'page',
				'comment_status' => 'closed',
			)
		);
		pno_update_option( 'profile_page', [ $profile_page ] );
	}

}

/**
 * Install default emails.
 *
 * @return void
 */
function pno_install_default_emails() {

	if ( get_option( 'posterno_emails_installed', false ) ) {
		return;
	}

	$registration_schema = get_term_by( 'slug', 'core_user_registration', 'pno-email-type' );

	if ( $registration_schema instanceof WP_Term ) {

		$registration_email = wp_insert_post(
			array(
				'post_title'     => 'Welcome to {sitename}',
				'post_content'   => '<p>Hello {username}, and welcome to {sitename}. We’re thrilled to have you on board. </p>
	<p>For reference, here\'s your login information:</p>
	<p>Username: {username}<br />Login page: {login_page_url}<br />Password: {password}</p>
	<p>Thanks,<br />{sitename}</p>',
				'post_status'    => 'publish',
				'post_author'    => get_current_user_id(),
				'post_type'      => 'pno_emails',
				'comment_status' => 'closed',
			)
		);

		wp_set_object_terms( $registration_email, $registration_schema->term_id, 'pno-email-type' );

	}

	$password_recovery_schema = get_term_by( 'slug', 'core_user_password_recovery', 'pno-email-type' );

	if ( $password_recovery_schema instanceof WP_Term ) {

		$password_recovery_email = wp_insert_post(
			array(
				'post_title'     => 'Reset your {sitename} password',
				'post_content'   => '<p>Hello {username},</p>
<p>You are receiving this message because you or somebody else has attempted to reset your password on {sitename}.</p>
<p>If this was a mistake, just ignore this email and nothing will happen.</p>
<p>To reset your password, visit the following address:</p>
<p>{recovery_url}</p>',
				'post_status'    => 'publish',
				'post_author'    => get_current_user_id(),
				'post_type'      => 'pno_emails',
				'comment_status' => 'closed',
			)
		);

		wp_set_object_terms( $password_recovery_email, $password_recovery_schema->term_id, 'pno-email-type' );

	}

	$listing_author_email_schema = get_term_by( 'slug', 'core_listing_author_email', 'pno-email-type' );

	if ( $listing_author_email_schema instanceof WP_Term ) {

		$listing_author_email = wp_insert_post(
			array(
				'post_title'     => '{sitename}: You received a message from {sender_name}',
				'post_content'   => '<p>Hello {username},</p>
<p>You received a message from {sender_name}, regarding your listing: {listing_title} on {sitename}.</p>
<p>Sender: {sender_name}</p>
<p>Sender email: {sender_email}</p>
<p>Message: {sender_message}</p>
<p>Thanks,<br />{sitename}</p>',
				'post_status'    => 'publish',
				'post_author'    => get_current_user_id(),
				'post_type'      => 'pno_emails',
				'comment_status' => 'closed',
			)
		);

		wp_set_object_terms( $listing_author_email, $listing_author_email_schema->term_id, 'pno-email-type' );

	}

	$listing_submitted_email_schema = get_term_by( 'slug', 'core_user_listing_submitted', 'pno-email-type' );

	if ( $listing_submitted_email_schema instanceof WP_Term ) {

		$listing_submitted_email = wp_insert_post(
			array(
				'post_title'     => 'Your listing on {sitename} has been published.',
				'post_content'   => '<p>Hello {username},</p>
<p>Your listing: {listing_title} on {sitename} has been successfully submitted.</p> <a href="{listing_url}">View your submission.</a>',
				'post_status'    => 'publish',
				'post_author'    => get_current_user_id(),
				'post_type'      => 'pno_emails',
				'comment_status' => 'closed',
			)
		);

		wp_set_object_terms( $listing_submitted_email, $listing_submitted_email_schema->term_id, 'pno-email-type' );

	}

	$listing_updated_email_schema = get_term_by( 'slug', 'core_user_listing_updated', 'pno-email-type' );

	if ( $listing_updated_email_schema instanceof WP_Term ) {

		$listing_updated_email = wp_insert_post(
			array(
				'post_title'     => 'Your listing on {sitename} has been updated.',
				'post_content'   => '<p>Hello {username},</p>
<p>Your listing: {listing_title} on {sitename} has been successfully updated.</p> <a href="{listing_url}">View your submission.</a>',
				'post_status'    => 'publish',
				'post_author'    => get_current_user_id(),
				'post_type'      => 'pno_emails',
				'comment_status' => 'closed',
			)
		);

		wp_set_object_terms( $listing_updated_email, $listing_updated_email_schema->term_id, 'pno-email-type' );

	}

	$listing_approved_email_schema = get_term_by( 'slug', 'core_user_listing_approved', 'pno-email-type' );

	if ( $listing_approved_email_schema instanceof WP_Term ) {

		$listing_approved_email = wp_insert_post(
			array(
				'post_title'     => 'Your listing on {sitename} has been approved.',
				'post_content'   => '<p>Hello {username},</p>
<p>Your listing: {listing_title} on {sitename} has been successfully approved.</p> <a href="{listing_url}">View your submission.</a>',
				'post_status'    => 'publish',
				'post_author'    => get_current_user_id(),
				'post_type'      => 'pno_emails',
				'comment_status' => 'closed',
			)
		);

		wp_set_object_terms( $listing_approved_email, $listing_approved_email_schema->term_id, 'pno-email-type' );

	}

	$listing_expiring_email_schema = get_term_by( 'slug', 'core_listing_expiring', 'pno-email-type' );

	if ( $listing_expiring_email_schema instanceof WP_Term ) {

		$listing_expiring_email = wp_insert_post(
			array(
				'post_title'     => 'Your listing is about to expire.',
				'post_content'   => '<p>Hello {username},</p>
<p>Your listing: {listing_title} on {sitename} expires on {listing_expiration_date}.</p>',
				'post_status'    => 'publish',
				'post_author'    => get_current_user_id(),
				'post_type'      => 'pno_emails',
				'comment_status' => 'closed',
			)
		);

		wp_set_object_terms( $listing_expiring_email, $listing_expiring_email_schema->term_id, 'pno-email-type' );

	}

	$listing_expired_email_schema = get_term_by( 'slug', 'core_listing_expired', 'pno-email-type' );

	if ( $listing_expired_email_schema instanceof WP_Term ) {

		$listing_expired_email = wp_insert_post(
			array(
				'post_title'     => 'Your listing is expired.',
				'post_content'   => '<p>Hello {username},</p>
<p>Your listing: {listing_title} on {sitename} is expired.</p>',
				'post_status'    => 'publish',
				'post_author'    => get_current_user_id(),
				'post_type'      => 'pno_emails',
				'comment_status' => 'closed',
			)
		);

		wp_set_object_terms( $listing_expired_email, $listing_expired_email_schema->term_id, 'pno-email-type' );

	}

	update_option( 'posterno_emails_installed', true );

}

/**
 * Installs default settings.
 *
 * @return void
 */
function pno_install_default_settings() {

	if ( get_option( 'posterno_settings_installed', false ) ) {
		return;
	}

	$default_settings_string = 'a:51:{s:12:"login_method";s:14:"username_email";s:28:"login_show_registration_link";b:1;s:24:"login_show_password_link";b:1;s:28:"registration_show_login_link";b:1;s:31:"registration_show_password_link";b:1;s:24:"recovery_show_login_link";b:1;s:31:"recovery_show_registration_link";b:1;s:20:"allow_account_delete";b:1;s:18:"allow_data_request";b:1;s:18:"allow_data_erasure";b:1;s:21:"profiles_allow_guests";b:1;s:22:"profiles_allow_members";b:1;s:13:"allow_avatars";b:1;s:17:"profile_permalink";s:8:"username";s:15:"bootstrap_style";b:1;s:16:"bootstrap_script";b:1;s:19:"listing_date_format";s:7:"default";s:17:"listings_per_page";s:2:"10";s:22:"listings_default_order";s:6:"newest";s:15:"listings_layout";s:4:"list";s:17:"listings_duration";s:2:"30";s:23:"listing_can_be_featured";b:1;s:28:"listings_featured_in_sorters";a:4:{i:0;s:6:"newest";i:1;s:6:"oldest";i:2;s:5:"title";i:3;s:8:"title_za";}s:29:"listing_external_open_new_tab";b:1;s:31:"listing_external_rel_attributes";b:1;s:25:"listings_disable_comments";b:1;s:24:"listings_social_profiles";a:4:{i:0;s:8:"facebook";i:1;s:11:"google-plus";i:2;s:9:"instagram";i:3;s:7:"twitter";}s:25:"listing_image_placeholder";b:1;s:32:"submission_categories_associated";b:1;s:30:"submission_categories_multiple";b:1;s:26:"submission_tags_associated";b:1;s:27:"listings_per_page_dashboard";s:2:"10";s:21:"listing_allow_editing";b:1;s:20:"listing_allow_delete";b:1;s:25:"submission_edit_moderated";s:3:"yes";s:12:"map_provider";s:10:"googlemaps";s:16:"map_starting_lat";s:10:"40.7484405";s:16:"map_starting_lng";s:11:"-73.9944191";s:8:"map_zoom";s:2:"12";s:10:"login_page";a:1:{i:0;i:36;}s:13:"password_page";a:1:{i:0;i:37;}s:17:"registration_page";a:1:{i:0;i:38;}s:14:"dashboard_page";a:1:{i:0;i:39;}s:15:"submission_page";a:1:{i:0;i:40;}s:12:"editing_page";a:1:{i:0;i:41;}s:12:"profile_page";a:1:{i:0;i:42;}s:9:"from_name";s:8:"posterno";s:10:"from_email";s:13:"test@demo.com";s:14:"email_template";s:7:"default";s:24:"listing_expiration_email";b:1;s:31:"listing_expiration_email_period";s:2:"10";}';

	$default_options = maybe_unserialize( $default_settings_string );

	if ( is_array( $default_options ) ) {

		if ( isset( $default_options['login_page'] ) ) {
			unset( $default_options['login_page'] );
		}
		if ( isset( $default_options['password_page'] ) ) {
			unset( $default_options['password_page'] );
		}
		if ( isset( $default_options['registration_page'] ) ) {
			unset( $default_options['registration_page'] );
		}
		if ( isset( $default_options['dashboard_page'] ) ) {
			unset( $default_options['dashboard_page'] );
		}
		if ( isset( $default_options['submission_page'] ) ) {
			unset( $default_options['submission_page'] );
		}
		if ( isset( $default_options['editing_page'] ) ) {
			unset( $default_options['editing_page'] );
		}
		if ( isset( $default_options['profile_page'] ) ) {
			unset( $default_options['profile_page'] );
		}

		update_option( 'pno_settings', $default_options );

	}

	pno_update_option( 'from_email', get_option( 'admin_email' ) );
	pno_update_option( 'from_name', get_option( 'blogname' ) );
	pno_update_option( 'submission_images_amount', 2 );

	update_option( 'posterno_settings_installed', true );

}

/**
 * Automatically create a dashboard menu.
 *
 * @return void
 */
function pno_install_dashboard_menu() {

	if ( get_option( 'posterno_dashboard_menu_installed', false ) ) {
		return;
	}

	$dashboard_menu = pno_get_nav_menu_items_by_location( 'pno-dashboard-menu' );

	if ( ! $dashboard_menu ) {

		$menu_id = wp_create_nav_menu( esc_html__( 'Dashboard Menu', 'posterno' ) );

		$dashboard_components = pno_get_dashboard_navigation_items();

		foreach ( $dashboard_components as $key => $nav_item ) {

			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'   => esc_html( $nav_item['name'] ),
					'menu-item-classes' => "pno-menu pno-{$key}-nav",
					'menu-item-url'     => pno_get_dashboard_navigation_item_url( $key, $nav_item ),
					'menu-item-status'  => 'publish',
				)
			);

		}

		if ( ! has_nav_menu( 'pno-dashboard-menu' ) ) {
			$locations                       = get_theme_mod( 'nav_menu_locations' );
			$locations['pno-dashboard-menu'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}

	update_option( 'posterno_dashboard_menu_installed', true );

}

/**
 * Automatically create a profile menu.
 *
 * @return void
 */
function pno_install_profile_menu() {

	if ( get_option( 'posterno_profile_menu_installed', false ) ) {
		return;
	}

	$profile_menu = pno_get_nav_menu_items_by_location( 'pno-profile-menu' );

	if ( ! $profile_menu ) {

		$menu_id = wp_create_nav_menu( esc_html__( 'Profile Menu', 'posterno' ) );

		$profile_components = pno_get_profile_components();

		foreach ( $profile_components as $key => $nav_item ) {

			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'   => esc_html( $nav_item ),
					'menu-item-classes' => "pno-menu pno-{$key}-nav",
					'menu-item-url'     => '#',
					'menu-item-status'  => 'publish',
				)
			);

		}

		if ( ! has_nav_menu( 'pno-profile-menu' ) ) {
			$locations                     = get_theme_mod( 'nav_menu_locations' );
			$locations['pno-profile-menu'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}

	update_option( 'posterno_profile_menu_installed', true );

}

/**
 * Retrieve the list of sidebars registered on the site.
 *
 * @return array
 */
function pno_get_registered_sidebars() {

	global $wp_registered_sidebars;

	$sidebars = [
		'' => '',
	];

	if ( empty( $wp_registered_sidebars ) ) {
		return $sidebars;
	}

	foreach ( $wp_registered_sidebars as $sidebar ) {
		$sidebars[ esc_attr( $sidebar['id'] ) ] = esc_html( $sidebar['name'] );
	}

	return $sidebars;

}

/**
 * Get the list of registered marker types.
 *
 * @return array
 */
function pno_get_registered_marker_types() {

	$types = [
		'default'  => esc_html__( 'Default', 'posterno' ),
		'category' => esc_html__( 'Category icon', 'posterno' ),
		'image'    => esc_html__( 'Image', 'posterno' ),
		'custom'   => esc_html__( 'Custom field (text)', 'posterno' ),
	];

	/**
	 * Filter: determines the options available to define the type of map marker used.
	 *
	 * @param array $types registered types.
	 * @return array $types
	 */
	return apply_filters( 'pno_marker_types', $types );

}

/**
 * Get image fields for association with the marker image setting field.
 *
 * @return array
 */
function pno_get_marker_image_fields() {

	$image_fields = [];

	$fields = wp_list_filter( pno_get_listings_fields(), [ 'type' => 'file' ] );

	foreach ( $fields as $field ) {
		$image_fields[ $field['meta'] ] = $field['name'];
	}

	return $image_fields;

}

/**
 * Get fields for association with the marker custom setting field.
 *
 * @return array
 */
function pno_get_marker_text_fields() {

	$fields = [];

	$text_fields     = wp_list_filter( pno_get_listings_fields(), [ 'type' => 'text' ] );
	$textarea_fields = wp_list_filter( pno_get_listings_fields(), [ 'type' => 'textarea' ] );
	$radio_fields    = wp_list_filter( pno_get_listings_fields(), [ 'type' => 'radio' ] );
	$select_fields   = wp_list_filter( pno_get_listings_fields(), [ 'type' => 'select' ] );
	$number_fields   = wp_list_filter( pno_get_listings_fields(), [ 'type' => 'number' ] );

	$all_fields = array_merge( $text_fields, $textarea_fields, $radio_fields, $select_fields, $number_fields );

	foreach ( $all_fields as $field ) {
		$fields[ $field['meta'] ] = $field['name'];
	}

	unset( $fields['listing_title'] );

	return $fields;

}
