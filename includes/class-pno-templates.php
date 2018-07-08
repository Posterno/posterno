<?php
/**
 * Custom templates files loader for posterno.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

// Exit if accessed directly
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
	protected $theme_template_directory = 'pno';

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

}
