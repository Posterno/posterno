<?php
/**
 * Custom templates files loader for posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Dynamic templates loader for PNO.
 */
class PNO_Templates extends Gamajo_Template_Loader {

	/**
	 * Prefix for filter names.
	 *
	 * @var string
	 */
	protected $filter_prefix = 'pno';

	/**
	 * Directory name where templates should be found into the theme.
	 *
	 * @var string
	 */
	protected $theme_template_directory = 'posterno';

	/**
	 * Current plugin's root directory.
	 *
	 * @var string
	 */
	protected $plugin_directory = PNO_PLUGIN_DIR;

	/**
	 * Directory name of where the templates are stored into the plugin.
	 *
	 * @var string
	 */
	protected $plugin_template_directory = 'templates';

	/**
	 * Retrieve a template part.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Template slug.
	 * @param string $name Optional. Template variation name. Default null.
	 * @param bool   $load Optional. Whether to load template. Default true.
	 * @return string
	 */
	public function get_template_part( $slug, $name = null, $load = true ) {

		$supported = ! pno_get_option( 'bootstrap_style' ) || current_theme_supports( 'posterno' );

		// Load the wrapper only when bootstrap is disabled or when the theme does not declares custom support.
		if ( ! $supported ) {
			echo '<div class="posterno-template">';
		}

		do_action( 'get_template_part_' . $slug, $slug, $name );
		do_action( $this->filter_prefix . '_get_template_part_' . $slug, $slug, $name );
		$templates = $this->get_template_file_names( $slug, $name );
		$output    = $this->locate_template( $templates, $load, false );

		if ( ! $supported ) {
			echo '</div>';
		}

		return $output;
	}

}
