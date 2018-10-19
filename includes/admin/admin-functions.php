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
 * @return array
 */
function pno_get_pages( $force = false ) {
	$pages = [];
	if ( ( ! isset( $_GET['page'] ) || 'posterno-settings' != $_GET['page'] ) && ! $force ) {
		return $pages;
	}
	$transient = get_transient( 'pno_get_pages' );
	if ( $transient ) {
		$pages = $transient;
	} else {
		$available_pages = get_pages();
		if ( ! empty( $available_pages ) ) {
			foreach ( $available_pages as $page ) {
				$pages[] = array(
					'value' => $page->ID,
					'label' => $page->post_title,
				);
			}
			set_transient( 'pno_get_pages', $pages, DAY_IN_SECONDS );
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

	$plugin_array['pno_shortcodes_mce_button'] = apply_filters( 'pno_shortcodes_tinymce_js_file_url', PNO_PLUGIN_URL . 'assets/js/pno-tinymce.min.js' );

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
		'pno_get_login_methods', array(
			'username'       => __( 'Username only' ),
			'email'          => __( 'Email only' ),
			'username_email' => __( 'Username or Email' ),
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
	$roles = [];
	if ( ( ! isset( $_GET['page'] ) || 'posterno-settings' != $_GET['page'] ) && ! $force ) {
		return $roles;
	}
	$transient = get_transient( 'pno_get_roles' );
	if ( $transient && ! $force ) {
		$roles = $transient;
	} else {
		global $wp_roles;
		$available_roles = $wp_roles->get_names();
		foreach ( $available_roles as $role_id => $role ) {
			if ( $role_id == 'administrator' && ! $admin ) {
				continue;
			}
			$roles[] = array(
				'value' => esc_attr( $role_id ),
				'label' => esc_html( $role ),
			);
		}
		//set_transient( 'pno_get_roles', $roles, DAY_IN_SECONDS );
	}
	return $roles;
}

/**
 * Install the profile fields within the database.
 * This function is usually used within the plugin's activation.
 *
 * @return void
 */
function pno_install_profile_fields() {

	// Bail if this was already done.
	if ( get_option( 'pno_profile_fields_installed' ) ) {
		return;
	}

	$registered_fields = [
		'avatar'      => [
			'label'       => esc_html__( 'Profile picture' ),
			'type'        => 'file',
			'required'    => false,
			'placeholder' => '',
			'priority'    => 1,
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

	if ( ! is_array( $registered_fields ) ) {
		return;
	}

	if ( empty( $registered_fields ) ) {
		return;
	}

	foreach ( $registered_fields as $field_key => $field ) {

		if ( pno_is_default_profile_field( $field_key ) ) {

			$new_field = [
				'post_type'   => 'pno_users_fields',
				'post_title'  => $field['label'],
				'post_status' => 'publish',
			];

			$field_id = wp_insert_post( $new_field );

			if ( is_wp_error( $field_id ) ) {
				return new WP_REST_Response( $field_id->get_error_message(), 422 );
			} else {

				// Setup the field's meta key.
				carbon_set_post_meta( $field_id, 'profile_field_meta_key', $field_key );

				// Setup the field's type.
				$registered_field_types = pno_get_registered_field_types();

				if ( isset( $field['type'] ) && isset( $registered_field_types[ $field['type'] ] ) ) {
					carbon_set_post_meta( $field_id, 'profile_field_type', esc_attr( $field['type'] ) );
				}

				// Assign a description if one is given.
				if ( isset( $field['description'] ) && ! empty( $field['description'] ) ) {
					carbon_set_post_meta( $field_id, 'profile_field_description', esc_html( $field['description'] ) );
				}

				// Assign a placeholder if one is given.
				if ( isset( $field['placeholder'] ) && ! empty( $field['placeholder'] ) ) {
					carbon_set_post_meta( $field_id, 'profile_field_placeholder', esc_html( $field['placeholder'] ) );
				}

				// Make field required if defined.
				if ( isset( $field['required'] ) && $field['required'] === true ) {
					carbon_set_post_meta( $field_id, 'profile_field_is_required', true );
				}

				// Set priority.
				if ( isset( $field['priority'] ) && ! empty( $field['priority'] ) ) {
					carbon_set_post_meta( $field_id, 'profile_field_priority', absint( $field['priority'] ) );
				}

				// Mark the field as a default one.
				if ( pno_is_default_profile_field( $field_key ) ) {
					carbon_set_post_meta( $field_id, 'profile_is_default_field', true );
				}
			}

			wp_reset_postdata();

		}
	}

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
			'label'    => esc_html__( 'Username' ),
			'type'     => 'text',
			'required' => true,
			'priority' => 1,
		],
		'email'    => [
			'label'    => esc_html__( 'Email address' ),
			'type'     => 'email',
			'required' => true,
			'priority' => 2,
		],
		'password' => [
			'label'    => esc_html__( 'Password' ),
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

			// Mark the registration field as a default field.
			if ( pno_is_default_profile_field( $key ) ) {
				carbon_set_post_meta( $field_id, 'registration_field_is_default', $key );
			}

			// Mark fields as required.
			if ( isset( $field['required'] ) && $field['required'] === true ) {
				carbon_set_post_meta( $field_id, 'registration_field_is_required', true );
			}

			// Setup the priority of this field.
			if ( isset( $field['priority'] ) ) {
				carbon_set_post_meta( $field_id, 'registration_field_priority', $field['priority'] );
			}
		}
	}

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

			// Mark the registration field as a default field.
			carbon_set_post_meta( $field_id, 'listing_field_is_default', $key );

			// Mark fields as required.
			if ( isset( $field['required'] ) && $field['required'] === true ) {
				carbon_set_post_meta( $field_id, 'listing_field_is_required', true );
			}

			// Setup the priority of this field.
			if ( isset( $field['priority'] ) ) {
				carbon_set_post_meta( $field_id, 'listing_field_priority', $field['priority'] );
			}

			// Setup the field's meta key.
			carbon_set_post_meta( $field_id, 'listing_field_meta_key', $key );

			// Assign a description if one is given.
			if ( isset( $field['description'] ) && ! empty( $field['description'] ) ) {
				carbon_set_post_meta( $field_id, 'profile_field_description', esc_html( $field['description'] ) );
			}

			// Assign a placeholder if one is given.
			if ( isset( $field['placeholder'] ) && ! empty( $field['placeholder'] ) ) {
				carbon_set_post_meta( $field_id, 'profile_field_placeholder', esc_html( $field['placeholder'] ) );
			}

		}
	}

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
 * Generate a list of tabs for the listings list table and taxonomies associated.
 * The tabs are then displayed at the top of the admin page.
 *
 * @return void
 */
function pno_display_post_type_tabs() {

	$tabs = array(
		'listings' => array(
			'name' => 'Listings',
			'url'  => admin_url( 'edit.php?post_type=listings' ),
		),
	);

	$taxonomies = get_object_taxonomies( 'listings', 'objects' );
	foreach ( $taxonomies as $tax => $details ) {
		$tabs[ $tax ] = array(
			'name' => $details->labels->menu_name,
			'url'  => add_query_arg(
				array(
					'taxonomy'  => $tax,
					'post_type' => 'listings',
				), admin_url( 'edit-tags.php' )
			),
		);
	}

	$tabs['custom_fields'] = [
		'name' => esc_html__( 'Custom fields' ),
		'url'  => admin_url( 'edit.php?post_type=listings&page=posterno-custom-fields#' ),
	];

	/**
	 * Allows developers to extend the tabs within the admin panel.
	 *
	 * @param array $tabs the list of registered tabs.
	 */
	$tabs = apply_filters( 'pno_add_ons_tabs', $tabs );

	// phpcs:ignore
	if ( isset( $_GET['taxonomy'] ) && in_array( $_GET['taxonomy'], array_keys( $taxonomies ), true ) ) {
		$active_tab = $_GET['taxonomy']; // phpcs:ignore
	} else {
		$active_tab = 'listings';
	}

	ob_start() ?>

	<div class="clear"></div>
	<h2 class="nav-tab-wrapper pno-nav-tab-wrapper">
		<?php

		foreach ( $tabs as $tab_id => $tab ) {
			$active = ( $active_tab === $tab_id )
				? ' nav-tab-active'
				: '';

			echo '<a href="' . esc_url( $tab['url'] ) . '" class="nav-tab' . esc_attr( $active ) . '">';
			echo esc_html( $tab['name'] );
			echo '</a>';
		}
		?>

		<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=listings' ) ); ?>" class="page-title-action pno-title-action">
			<?php esc_html_e( 'Add new listing' ); ?>
		</a>
	</h2>
	<br />

	<?php
	echo ob_get_clean(); // phpcs:ignore
}

/**
 * Retrieve a list of listings types when within the listings categories panel.
 * This function is used only within the admin panel.
 *
 * @return array
 */
function pno_get_listings_types_for_association() {

	$types = [];

	$terms = get_terms(
		'listings-types', array(
			'hide_empty' => false,
			'number'     => 999,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( ! empty( $terms ) ) {
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
		'listings-categories', array(
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
		'listings-tags', array(
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
		'pno-email-type', array(
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






















function testme() {

	if ( isset( $_GET['testme'] ) ) {

		wp_die();
	}

}
add_action( 'admin_init', 'testme' );
