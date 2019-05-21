<?php
/**
 * Handles registration of tests for the WordPress health manager tool.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Admin;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Helper class to check outdated template files.
 */
class TemplatesCheck {

	/**
	 * Retrieve metadata from a file. Based on WP Core's get_file_data function.
	 *
	 * @param  string $file Path to the file.
	 * @return string
	 */
	public static function get_file_version( $file ) {
		// Avoid notices if file does not exist.
		if ( ! file_exists( $file ) ) {
			return '';
		}
		// We don't need to write to the file, so just open for reading.
		$fp = fopen( $file, 'r' ); // @codingStandardsIgnoreLine.
		// Pull only the first 8kiB of the file in.
		$file_data = fread( $fp, 8192 ); // @codingStandardsIgnoreLine.
		// PHP will close file handle, but we are good citizens.
		fclose( $fp ); // @codingStandardsIgnoreLine.
		// Make sure we catch CR-only line endings.
		$file_data = str_replace( "\r", "\n", $file_data );
		$version   = '';
		if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( '@version', '/' ) . '(.*)$/mi', $file_data, $match ) && $match[1] ) {
			$version = _cleanup_header_comment( $match[1] );
		}
		return $version;
	}

	/**
	 * Scan the template files.
	 *
	 * @param  string $template_path Path to the template directory.
	 * @return array
	 */
	public static function scan_template_files( $template_path ) {
		$files  = @scandir( $template_path ); // @codingStandardsIgnoreLine.
		$result = array();
		if ( ! empty( $files ) ) {
			foreach ( $files as $key => $value ) {
				if ( ! in_array( $value, array( '.', '..' ), true ) ) {
					if ( is_dir( $template_path . DIRECTORY_SEPARATOR . $value ) ) {
						$sub_files = self::scan_template_files( $template_path . DIRECTORY_SEPARATOR . $value );
						foreach ( $sub_files as $sub_file ) {
							$result[] = $value . DIRECTORY_SEPARATOR . $sub_file;
						}
					} else {
						$result[] = $value;
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Determine if there are outdated templates.
	 *
	 * @return boolean
	 */
	public static function theme_has_outdated_templates() {
		$core_templates = self::scan_template_files( PNO_PLUGIN_DIR . '/templates' );
		$outdated       = false;

		foreach ( $core_templates as $file ) {
			$theme_file = false;
			if ( file_exists( get_stylesheet_directory() . '/' . $file ) ) {
				$theme_file = get_stylesheet_directory() . '/' . $file;
			} elseif ( file_exists( get_stylesheet_directory() . '/' . 'posterno/' . $file ) ) {
				$theme_file = get_stylesheet_directory() . '/' . 'posterno/' . $file;
			} elseif ( file_exists( get_template_directory() . '/' . $file ) ) {
				$theme_file = get_template_directory() . '/' . $file;
			} elseif ( file_exists( get_template_directory() . '/' . 'posterno/' . $file ) ) {
				$theme_file = get_template_directory() . '/' . 'posterno/' . $file;
			}
			if ( false !== $theme_file ) {
				$core_version  = self::get_file_version( PNO_PLUGIN_DIR . '/templates/' . $file );
				$theme_version = self::get_file_version( $theme_file );
				if ( $core_version && $theme_version && version_compare( $theme_version, $core_version, '<' ) ) {
					$outdated = true;
					break;
				}
			}
		}

		return $outdated;

	}

}
