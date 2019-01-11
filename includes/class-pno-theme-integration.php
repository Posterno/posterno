<?php
/**
 * Integrate Posterno and load custom template files for
 * listings archive, taxonomies and single template.
 *
 * Posterno overrides theme's files only when the theme
 * does not declare support for Posterno.
 *
 * Adapted from WooCommerce.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Integrate custom template files within themes.
 */
class PNO_Theme_Integration {

	/**
	 * Store the login page id.
	 * We use this page to get information about
	 * the administrator that created the page in order
	 * to create a dummy post for our integration.
	 *
	 * @var integer
	 */
	private static $login_page_id = 0;

	/**
	 * Is Posterno support defined?
	 *
	 * @var boolean
	 */
	private static $theme_support = false;

	/**
	 * Store whether we're processing a listing inside the_content filter.
	 *
	 * @var boolean
	 */
	private static $in_content_filter = false;

	/**
	 * Get things started.
	 *
	 * @return void
	 */
	public static function init() {

		self::$theme_support = current_theme_supports( 'posterno' );
		self::$login_page_id = pno_get_login_page_id();

		if ( ! self::$theme_support ) {
			add_action( 'template_redirect', array( __class__, 'unsupported_theme_init' ) );
		}
	}

	/**
	 * Hook in methods to enhance the unsupported theme experience on pages.
	 *
	 * @return void
	 */
	public static function unsupported_theme_init() {
		if ( 0 < self::$login_page_id ) {
			if ( pno_is_listing_taxonomy() ) {
				self::unsupported_theme_tax_archive_init();
			} elseif ( is_singular( 'listings' ) ) {
				self::unsupported_single_listing_page_init();
			}
		}
	}

	/**
	 * Enhance the unsupported theme experience on listings taxonomies pages by loading
	 * the theme's post single template and replacing the content with out own. To do this
	 * we make a dummy post and replace global queries variables.
	 *
	 * @return void
	 */
	private static function unsupported_theme_tax_archive_init() {

		global $wp_query, $post;

		$queried_object = get_queried_object();

		$login_page = get_post( self::$login_page_id );

		$dummy_post_properties = array(
			'ID'                    => 0,
			'post_status'           => 'publish',
			'post_author'           => isset( $login_page->post_author ) ? $login_page->post_author : false,
			'post_parent'           => 0,
			'post_type'             => 'page',
			'post_date'             => isset( $login_page->post_date ) ? $login_page->post_date : '',
			'post_date_gmt'         => isset( $login_page->post_date_gmt ) ? $login_page->post_date_gmt : '',
			'post_modified'         => isset( $login_page->post_modified ) ? $login_page->post_modified : '',
			'post_modified_gmt'     => isset( $login_page->post_modified_gmt ) ? $login_page->post_modified_gmt : '',
			'post_content'          => self::get_taxonomy_template(),
			'post_title'            => esc_html( $queried_object->name ),
			'post_excerpt'          => '',
			'post_content_filtered' => '',
			'post_mime_type'        => '',
			'post_password'         => '',
			'post_name'             => $queried_object->slug,
			'guid'                  => '',
			'menu_order'            => 0,
			'pinged'                => '',
			'to_ping'               => '',
			'ping_status'           => '',
			'comment_status'        => 'closed',
			'comment_count'         => 0,
			'filter'                => 'raw',
		);

		$post = new WP_Post( (object) $dummy_post_properties ); // @codingStandardsIgnoreLine.

		// Copy the new post global into the main $wp_query.
		$wp_query->post  = $post;
		$wp_query->posts = array( $post );

		// Prevent comments form from appearing.
		$wp_query->post_count    = 1;
		$wp_query->is_404        = false;
		$wp_query->is_page       = true;
		$wp_query->is_single     = true;
		$wp_query->is_archive    = false;
		$wp_query->is_tax        = true;
		$wp_query->max_num_pages = 0;

		// Prepare everything for rendering.
		setup_postdata( $post );
		remove_all_filters( 'the_content' );
		remove_all_filters( 'the_excerpt' );

		add_filter( 'template_include', array( __CLASS__, 'force_single_template_filter' ) );

	}

	/**
	 * Force the loading of one of the single templates instead of whatever template was about to be loaded.
	 *
	 * @param string $template path to the template.
	 * @return string
	 */
	public static function force_single_template_filter( $template ) {

		$possible_templates = array(
			'page',
			'single',
			'singular',
			'index',
		);

		foreach ( $possible_templates as $possible_template ) {
			$path = get_query_template( $possible_template );
			if ( $path ) {
				return $path;
			}
		}

		return $template;
	}

	/**
	 * Load the taxonomy template.
	 *
	 * @return string
	 */
	public static function get_taxonomy_template() {

		ob_start();

		posterno()->templates->get_template_part( 'taxonomy' );

		return ob_get_clean();

	}

	/**
	 * Enhance the unsupported theme experience on the single listing template.
	 *
	 * @return void
	 */
	private static function unsupported_single_listing_page_init() {
		add_filter( 'the_content', array( __CLASS__, 'unsupported_theme_listing_content_filter' ), 10 );
		add_filter( 'post_thumbnail_html', array( __CLASS__, 'unsupported_theme_single_featured_image_filter' ) );
	}

	/**
	 * Filter the content and insert Posterno content on the single listing page for non supported themes.
	 *
	 * @param string $content Existing post content.
	 * @return string
	 */
	public static function unsupported_theme_listing_content_filter( $content ) {
		global $wp_query;

		if ( self::$theme_support || ! is_main_query() || ! in_the_loop() ) {
			return $content;
		}

		self::$in_content_filter = true;

		// Remove the filter we're in to avoid nested calls.
		remove_filter( 'the_content', array( __CLASS__, 'unsupported_theme_listing_content_filter' ) );

		if ( is_singular( 'listings' ) ) {
			$content = self::get_single_template();
		}

		self::$in_content_filter = false;

		return $content;
	}

	/**
	 * Prevent the main featured image on listing singular pages because there will be another featured image
	 * in the gallery.
	 *
	 * @param string $html Img element HTML.
	 * @return string
	 */
	public static function unsupported_theme_single_featured_image_filter( $html ) {
		if ( self::in_content_filter() || ! is_singular( 'listings' ) || ! is_main_query() ) {
			return $html;
		}
		return '';
	}

	/**
	 * Are we filtering content for unsupported themes?
	 *
	 * @return bool
	 */
	public static function in_content_filter() {
		return (bool) self::$in_content_filter;
	}

	/**
	 * Retrieve the single's listing template file.
	 *
	 * @return string
	 */
	public static function get_single_template() {
		ob_start();

		posterno()->templates->get_template_part( 'single' );

		return ob_get_clean();
	}

}
add_action( 'init', array( 'PNO_Theme_Integration', 'init' ) );
