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
						$file_url  = $this->get_file_url( $uploaded_image );
						$file_path = $this->get_file_path( $uploaded_image );
						$file_name = $this->get_file_name( $uploaded_image );
						$file_size = $this->get_file_size( $uploaded_image );

						if ( $file_path && file_exists( $file_path ) ) {
							$redefined_value[] = [
								'image_url'  => $file_url,
								'image_path' => $file_path,
								'image_name' => $file_name,
								'image_size' => $file_size,
							];
						}
					}
				} else {
					if ( isset( $value[0] ) ) {
						$file_url  = $this->get_file_url( $value[0] );
						$file_path = $this->get_file_path( $value[0] );
						$file_name = $this->get_file_name( $value[0] );
						$file_size = $this->get_file_size( $value[0] );

						$redefined_value = [
							'image_url'  => $file_url,
							'image_path' => $file_path,
							'image_name' => $file_name,
							'image_size' => $file_size,
						];
					}
				}
			}
			$value = $redefined_value;
			return $this->set_value( wp_json_encode( $value ) );
		}
		return $this->set_value( $value );
	}

	/**
	 * Retrieve url of the uploaded file.
	 *
	 * @param object $file file object.
	 * @return string|boolean
	 */
	private function get_file_url( $file ) {

		$url = false;

		if ( isset( $file->image_url ) ) {
			$url = $file->image_url;
		} elseif ( $file->url ) {
			$url = $file->url;
		}

		return $url;

	}

	/**
	 * Retrieve path of the uploaded file.
	 *
	 * @param object $file file object.
	 * @return string|boolean
	 */
	private function get_file_path( $file ) {

		$path = false;

		if ( isset( $file->image_path ) ) {
			$path = $file->image_path;
		} elseif ( $file->path ) {
			$path = $file->path;
		}

		return $path;

	}

	/**
	 * Retrieve the name of the uploaded file.
	 *
	 * @param object $file file object.
	 * @return string|boolean
	 */
	private function get_file_name( $file ) {
		return wp_basename( $this->get_file_path( $file ) );
	}

	/**
	 * Retrieve the size of the uploaded file.
	 *
	 * @param object $file file object.
	 * @return string|boolean
	 */
	private function get_file_size( $file ) {
		return filesize( $this->get_file_path( $file ) );
	}

}
