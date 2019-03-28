<?php
/**
 * List of functions used within template files.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Parses a form action string to create an ID for the form tag of a form.
 *
 * @param string $form form name.
 * @return string
 */
function pno_get_form_id( $form ) {
	$id = 'pno-form-' . $form;
	return esc_attr( $id );
}

/**
 * Retrieve the correct label for the login form.
 *
 * @return string
 */
function pno_get_login_label() {

	$label = esc_html__( 'Username', 'posterno' );

	$login_method = pno_get_option( 'login_method' );

	if ( $login_method === 'email' ) {
		$label = esc_html__( 'Email', 'posterno' );
	} elseif ( $login_method === 'username_email' ) {
		$label = esc_html__( 'Username or email', 'posterno' );
	}

	return $label;

}

/**
 * Retrieve the url where to redirect the user after login.
 *
 * @return string
 */
function pno_get_login_redirect() {

	$url = home_url();

	$custom_page = pno_get_option( 'login_redirect' );

	if ( is_array( $custom_page ) && isset( $custom_page[0] ) ) {
		$url = get_permalink( $custom_page[0] );
	}

	if ( isset( $_GET['redirect_to'] ) && ! empty( $_GET['redirect_to'] ) ) {
		$url = esc_url_raw( $_GET['redirect_to'] );
	}

	/**
	 * Filter the login redirect url. This is the url where users
	 * are redirect after they log into the website through the
	 * posterno's login form.
	 *
	 * @param string $url the url.
	 */
	return apply_filters( 'pno_login_redirect', $url );

}

/**
 * Retrieve the url where to redirect users after they register.
 *
 * @return string
 */
function pno_get_registration_redirect() {

	$url = false;

	$registration_redirect_page = pno_get_option( 'registration_redirect' );

	if ( is_array( $registration_redirect_page ) && isset( $registration_redirect_page[0] ) ) {
		$url = get_permalink( $registration_redirect_page[0] );
	}

	if ( isset( $_GET['redirect_to'] ) && ! empty( $_GET['redirect_to'] ) ) {
		$url = esc_url_raw( $_GET['redirect_to'] );
	}

	/**
	 * Filter the registration redirect url. This is the url where users
	 * are redirect after they register into the website through the
	 * posterno's registration form.
	 *
	 * @param string $url the url.
	 */
	return apply_filters( 'pno_registration_redirect', $url );

}

/**
 * Programmatically log a user in given an email address or user id.
 * This function should usually be followed by a redirect.
 *
 * @param mixed $email_or_id either the email address or the id of the user.
 * @return void
 */
function pno_log_user_in( $email_or_id ) {

	$get_by = 'id';

	if ( is_email( $email_or_id ) ) {
		$get_by = 'email';
	}

	$user     = get_user_by( $get_by, $email_or_id );
	$user_id  = $user->ID;
	$username = $user->user_login;

	wp_set_current_user( $user_id, $username );
	wp_set_auth_cookie( $user_id );
	do_action( 'wp_login', $username, $user );

}

/**
 * Retrieve a list of allowed users role on the registration page
 *
 * @since 1.0.0
 * @return array $roles An array of the roles
 */
function pno_get_allowed_user_roles() {
	global $wp_roles;
	if ( ! isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles(); // phpcs:ignore
	}
	$user_roles         = array();
	$selected_roles     = pno_get_option( 'allowed_roles' );
	$allowed_user_roles = is_array( $selected_roles ) ? $selected_roles : array( $selected_roles );
	foreach ( $allowed_user_roles as $role ) {
		$user_roles[ $role ] = $wp_roles->roles[ $role ]['name'];
	}
	return $user_roles;
}

/**
 * Replace during email parsing characters.
 *
 * @param string $str the string to manipulate.
 * @return string
 */
function pno_starmid( $str ) {
	switch ( strlen( $str ) ) {
		case 0:
			return false;
		case 1:
			return $str;
		case 2:
			return $str[0] . '*';
		default:
			return $str[0] . str_repeat( '*', strlen( $str ) - 2 ) . substr( $str, -1 );
	}
}

/**
 * Mask an email address.
 *
 * @param string $email_address email address to mask.
 * @return string
 */
function pno_mask_email_address( $email_address ) {
	if ( ! filter_var( $email_address, FILTER_VALIDATE_EMAIL ) ) {
		return false;
	}
	list( $u, $d ) = explode( '@', $email_address );
	$d             = explode( '.', $d );
	$tld           = array_pop( $d );
	$d             = implode( '.', $d );
	return pno_starmid( $u ) . '@' . pno_starmid( $d ) . ".$tld";
}

/**
 * Sort an array by the priority key value.
 *
 * @param array $a first set.
 * @param array $b second set.
 * @return mixed
 */
function pno_sort_array_by_priority( $a, $b ) {
	if ( $a['priority'] == $b['priority'] ) {
		return 0;
	}
	return ( $a['priority'] < $b['priority'] ) ? -1 : 1;
}

/**
 * Retrieve the url of a given dashboard navigation item.
 *
 * @param string $key item key.
 * @param array  $item item definition.
 * @return string
 */
function pno_get_dashboard_navigation_item_url( $key, $item = [] ) {

	$base_url = rtrim( get_permalink( pno_get_dashboard_page_id() ), '/' );

	// We use information stored in the CSS class to determine what kind of
	// menu item this is, and how it should be treated.
	if ( isset( $item->classes ) && ! empty( $item->classes ) ) {
		$menu_classes = $item->classes;
		if ( is_array( $menu_classes ) ) {
			$menu_classes = implode( ' ', $item->classes );
		}

		preg_match( '/\spno-(.*)-nav/', $menu_classes, $matches );

		if ( ! empty( $matches[1] ) && $matches[1] === 'dashboard' ) {
			return $base_url;
		}
	}

	$base_url = $base_url . '/' . $key;

	return apply_filters( 'pno_dashboard_navigation_item_url', $base_url, $key, $item );

}

/**
 * Retrieve the classes for a given dashboard navigation item as an array.
 *
 * @param string $key item key.
 * @param array  $item item definition.
 * @param string $class optional additional class.
 * @return array
 */
function pno_get_dashboard_navigation_item_class( $key, $item, $class = '' ) {

	$classes   = [ 'pno-dashboard-item' ];
	$classes[] = 'list-group-item';
	$classes[] = 'list-group-item-action';

	if ( $key ) {
		$classes[] = 'item-' . $key;
	}

	// Determine the currently active tab.
	if ( pno_is_dashboard_navigation_item_active( $item->pno_identifier ) ) {
		$classes[] = 'active';
	} elseif ( empty( get_query_var( 'dashboard_navigation_item' ) ) && isset( $item->pno_identifier ) && $item->pno_identifier === 'dashboard' ) {
		$classes[] = 'active';
	}

	$classes = array_map( 'esc_attr', $classes );

	/**
	 * Filters the list of CSS classes for the current navigation item.
	 *
	 * @param array $classes list of classes.
	 * @param string $key item key.
	 * @param array $item item definition.
	 * @param string $class optional class.
	 */
	$classes = apply_filters( 'pno_dashboard_navigation_item_classes', $classes, $key, $item, $class );

	return array_unique( $classes );

}

/**
 * Display the classes for a given dashboard navigation item.
 *
 * @param string $key item key.
 * @param object $item item definition.
 * @param string $class optional class.
 * @return void
 */
function pno_dashboard_navigation_item_class( $key, $item, $class = '' ) {
	// Separates classes with a single space, collates classes for body element.
	// phpcs:ignore
	echo 'class="' . join( ' ', pno_get_dashboard_navigation_item_class( $key, $item, $class ) ) . '"';
}

/**
 * Determine if a given navigation item is currently active.
 *
 * @param string $current key of the item to check.
 * @return boolean
 */
function pno_is_dashboard_navigation_item_active( $current ) {

	$active = ! empty( get_query_var( 'dashboard_navigation_item' ) ) && get_query_var( 'dashboard_navigation_item' ) == $current ? true : false;

	return $active;

}

/**
 * Retrieve the full hierarchy of a given page or post.
 *
 * @param int $page_id page id number.
 * @return mixed
 */
function pno_get_full_page_hierarchy( $page_id ) {
	$page = get_post( $page_id );
	if ( empty( $page ) || is_wp_error( $page ) ) {
		return [];
	}
	$return         = [];
	$page_obj       = [];
	$page_obj['id'] = $page_id;
	$return[]       = $page_obj;
	if ( $page->post_parent > 0 ) {
		$return = array_merge( $return, pno_get_full_page_hierarchy( $page->post_parent ) );
	}
	return $return;
}

/**
 * Get nav menu items by location.
 *
 * @param string $location the menu location to check.
 * @param array  $args optional settings.
 * @return mixed
 */
function pno_get_nav_menu_items_by_location( $location, $args = [] ) {

	$locations  = get_nav_menu_locations();
	$object     = wp_get_nav_menu_object( $locations[ $location ] );
	$menu_items = wp_get_nav_menu_items( $object->name, $args );

	return $menu_items;
}

/**
 * Display the formatted value of a field.
 *
 * @param string $type the type of the field.
 * @param string $value the content of the field.
 * @param array  $field all the details about the field.
 * @return void
 */
function pno_display_field_value( $type, $value, $field = false ) {

	if ( ! $type || ! $value ) {
		return;
	}

	$output = false;
	$type   = strtolower( str_replace( '-', '_', $type ) );

	$function_name = apply_filters( 'pno_display_field_value_func_name', "pno_display_field_{$type}_value", $type, $value, $field );

	if ( function_exists( $function_name ) ) {
		$output = call_user_func( $function_name, $value, $field );
	}

	if ( $output ) {
		echo $output; //phpcs:ignore
	}

}

/**
 * Display the formatted content for a text field on the frontend.
 *
 * If the string contains an url, we display an anchor tag.
 *
 * @param string $value the value to display.
 * @return string
 */
function pno_display_field_text_value( $value ) {
	return wp_kses_post( $value );
}

/**
 * Display the formatted content for the url field on the frontend.
 *
 * @param string $value the value to display.
 * @return string
 */
function pno_display_field_url_value( $value ) {
	return '<a href="' . esc_url( $value ) . '" rel="nofollow" class="pno-user-field-link">' . esc_url( $value ) . '</a>';
}

/**
 * Display the formatted content for a textarea field on the frontend.
 *
 * @param string $value the value to display.
 * @return string
 */
function pno_display_field_textarea_value( $value ) {
	return wp_kses_post( $value );
}

/**
 * Display the formatted content for a textarea field on the frontend.
 *
 * @param string $value the value to display.
 * @return string
 */
function pno_display_field_editor_value( $value ) {
	return wp_kses_post( $value );
}

/**
 * Display the formatted content for the email field on the frontend.
 *
 * @param string $value the value to display.
 * @return string
 */
function pno_display_field_email_value( $value ) {
	return '<a href="mailto:' . $value . '">' . antispambot( $value ) . '</a>';
}

/**
 * Display the formatted content for the checkbox field on the frontend.
 *
 * @param string $value the value to display.
 * @return string
 */
function pno_display_field_checkbox_value( $value ) {
	return $value === true ? esc_html__( 'Yes', 'posterno' ) : esc_html__( 'No', 'posterno' );
}

/**
 * Display the formatted content for the dropdown field on the frontend.
 *
 * @param string $value the value to display.
 * @param array  $field all the details about the field.
 * @return string
 */
function pno_display_field_select_value( $value, $field ) {

	$options = isset( $field['options'] ) ? array_map( 'esc_attr', $field['options'] ) : [];

	if ( array_key_exists( $value, $options ) ) {
		$value = $options[ $value ];
	}

	return $value;
}

/**
 * Display the formatted content for the radio field on the frontend.
 *
 * @param string $value the value to display.
 * @param array  $field all the details about the field.
 * @return string
 */
function pno_display_field_radio_value( $value, $field ) {
	return pno_display_field_select_value( $value, $field );
}

/**
 * Display the formatted content for the multiple checkboxes field on the frontend.
 *
 * @param string $value the value to display.
 * @param array  $field all the details about the field.
 * @return string
 */
function pno_display_field_multicheckbox_value( $value, $field ) {

	$output = [];

	$options = isset( $field['options'] ) ? array_map( 'esc_attr', $field['options'] ) : [];
	$value   = is_array( $value ) ? array_map( 'esc_attr', $value ) : [];

	foreach ( $value as $selected_option ) {
		if ( array_key_exists( $selected_option, $options ) ) {
			$output[] = esc_html( $options[ $selected_option ] );
		}
	}

	return implode( ', ', $output );

}

/**
 * Display the formatted content for the multiple selects field on the frontend.
 *
 * @param string $value the value to display.
 * @param array  $field all the details about the field.
 * @return string
 */
function pno_display_field_multiselect_value( $value, $field ) {
	return pno_display_field_multicheckbox_value( $value, $field );
}

/**
 * Display the formatted content for the number field on the frontend.
 *
 * @param string $value the value to display.
 * @return string
 */
function pno_display_field_number_value( $value ) {
	return pno_display_field_text_value( $value );
}

/**
 * Display the formatted content for the file field on the frontend.
 *
 * @param string $value the value to display.
 * @param array  $field all the details about the field.
 * @return string
 */
function pno_display_field_file_value( $value, $field ) {

	$files = $value;

	ob_start();

	if ( is_array( $files ) ) {
		posterno()->templates
			->set_template_data(
				[
					'files' => $files,
				]
			)
			->get_template_part( 'fields-output/file-field' );
	} else {
		posterno()->templates
			->set_template_data(
				[
					'file_url' => $files,
				]
			)
			->get_template_part( 'fields-output/file-field' );
	}

	return ob_get_clean();

}

/**
 * Display the formatted content for the social profiles field on the frontend.
 *
 * @param string $value the value to display.
 * @param array  $field all the details about the field.
 * @return string
 */
function pno_display_field_social_profiles_value( $value, $field ) {

	ob_start();

	posterno()->templates
		->set_template_data(
			[
				'networks' => $value,
			]
		)
		->get_template_part( 'fields-output/social-networks-field' );

	return ob_get_clean();

}

/**
 * Display the formatted content for the taxonomy field on the frontend.
 *
 * @param string $value the value to display.
 * @param array  $field all the details about the field.
 * @return string
 */
function pno_display_field_term_multiselect_value( $value, $field ) {

	$taxonomy = isset( $field['taxonomy'] ) && ! empty( $field['taxonomy'] ) ? esc_attr( $field['taxonomy'] ) : false;

	posterno()->templates
		->set_template_data(
			[
				'taxonomy' => $taxonomy,
				'terms'    => $value,
			]
		)
		->get_template_part( 'fields-output/taxonomy-field' );

	ob_start();

	return ob_get_clean();
}

/**
 * Display the formatted content for the taxonomy field on the frontend.
 *
 * @param string $value the value to display.
 * @param array  $field all the details about the field.
 * @return string
 */
function pno_display_field_term_select_value( $value, $field ) {
	return pno_display_field_term_multiselect_value( $value, $field );
}

/**
 * Display the formatted content for the taxonomy field on the frontend.
 *
 * @param string $value the value to display.
 * @param array  $field all the details about the field.
 * @return string
 */
function pno_display_field_term_checklist_value( $value, $field ) {
	return pno_display_field_term_multiselect_value( $value, $field );
}

/**
 * Display the formatted content for the taxonomy field on the frontend.
 *
 * @param string $value the value to display.
 * @param array  $field all the details about the field.
 * @return string
 */
function pno_display_field_term_chain_dropdown_value( $value, $field ) {
	return pno_display_field_term_multiselect_value( $value, $field );
}

/**
 * Displays the classes for the listing container element.
 *
 * @param string|array $class   One or more classes to add to the class list.
 * @param int|WP_Post  $post_id Optional. Post ID or post object. Defaults to the global `$post`.
 * @return void
 */
function pno_listing_class( $class = '', $post_id = null ) {
	// Separates classes with a single space, collates classes for post DIV.
	echo 'class="' . join( ' ', pno_get_listing_class( $class, $post_id ) ) . '"'; //phpcs:ignore
}

/**
 * Retrieves an array of the class names for the listing container element.
 *
 * @param string|string[] $class   Space-separated string or array of class names to add to the class list.
 * @param int|WP_Post     $post_id Optional. Post ID or post object.
 * @return string[] Array of class names.
 */
function pno_get_listing_class( $class = '', $post_id = null ) {

	$post = get_post( $post_id );

	$classes = array();

	if ( $class ) {
		if ( ! is_array( $class ) ) {
			$class = preg_split( '#\s+#', $class );
		}
		$classes = array_map( 'esc_attr', $class );
	} else {
		// Ensure that we always coerce class to being an array.
		$class = array();
	}

	if ( ! $post ) {
		return $classes;
	}

	$classes[] = 'listing-' . $post->ID;

	if ( pno_listing_is_featured( $post->ID ) ) {
		$classes[] = 'listing-is-featured';
	}

	$classes = array_map( 'esc_attr', $classes );

	/**
	 * Filters the list of CSS class names for the current listing.
	 *
	 * @param string[] $classes An array of listing class names.
	 * @param string[] $class   An array of additional class names added to the listing.
	 * @param int      $post_id The post ID.
	 */
	$classes = apply_filters( 'pno_listing_class', $classes, $class, $post->ID );

	return array_unique( $classes );

}

/**
 * Retrieve the error message displayed within widgets that are not meant to be used
 * outside the singular listings page.
 *
 * @return string
 */
function pno_get_widget_singular_restriction_message() {
	return esc_html__( 'This widget can only be used when within a sidebar for the sigle listing page.', 'posterno' );
}
