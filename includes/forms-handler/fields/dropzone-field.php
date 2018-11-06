<?php
/**
 * Representation of a file dropzone field.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Form\Field;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The class responsible of handling file fields within a PNO\Form.
 */
class DropzoneField extends FileField {

	/**
	 * Initialize the field.
	 *
	 * @return $this the current object.
	 */
	public function init() {
		$this->set_type( 'dropzone' );
		return $this->set_value( $this->get_option( 'value', [] ) );
	}

	/**
	 * Bind the value of the field.
	 *
	 * @param string $value the value of the field.
	 * @return $this the current object.
	 */
	public function bind( $value ) {
		if ( $value ) {
			$value           = json_decode( wp_unslash( $value ) );
			$redefined_value = [];
			if ( is_array( $value ) && ! empty( $value ) ) {
				$max_files = absint( $this->get_option( 'dropzone_max_files' ) );
				if ( $max_files > 1 ) {
					foreach ( $value as $uploaded_image ) {
						if ( isset( $uploaded_image->path ) && file_exists( $uploaded_image->path ) ) {
							$redefined_value[] = [
								'image_url'  => $uploaded_image->url,
								'image_path' => $uploaded_image->path,
								'image_name' => wp_basename( $uploaded_image->path ),
								'image_size' => filesize( $uploaded_image->path ),
							];
						}
					}
				} else {
					if ( isset( $value[0]->path ) && isset( $value[0]->url ) && file_exists( $value[0]->path ) ) {
						$redefined_value = [
							'image_url'  => $value[0]->url,
							'image_path' => $value[0]->path,
							'image_name' => wp_basename( $value[0]->path ),
							'image_size' => filesize( $value[0]->path ),
						];
					}
				}
			}
			$value = $redefined_value;
			return $this->set_value( wp_json_encode( $value ) );
		}
		return $this->set_value( $value );
	}

}
