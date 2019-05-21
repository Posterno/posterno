<?php
/**
 * Handles the fields cache tool.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Admin\Tools;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Fields cache handler tool.
 */
class FieldsCache {

	/**
	 * Hook into WP.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'pno_tools_cache', [ $this, 'page' ] );
	}

	/**
	 * Displays content of the page.
	 *
	 * @return void
	 */
	public function page() {

		?>
		<div class="postbox">
			<h2 class="hndle ui-sortable-handle">
				<span><?php esc_html_e( 'Fields cache' ); ?></span>
			</h2>
			<div class="inside">

			</div>
		</div>
		<?php

	}

}

( new FieldsCache() )->init();
