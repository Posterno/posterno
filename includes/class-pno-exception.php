<?php
/**
 * Handles custom php exceptions, supports a custom error code that we'll use together with WP_Error.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO;

/**
 * Handles custom exceptions thrown by Posterno.
 */
class Exception extends \Exception {

	/**
	 * The error code of the exception.
	 *
	 * @var string
	 */
	private $_error_code = '';

	/**
	 * Instantiate the exception object.
	 *
	 * @param string $message the message of the exception.
	 * @param mixed  $error_code option error code.
	 */
	public function __construct( $message, $error_code = false ) {

		if ( $error_code ) {
			$this->_error_code = $error_code;
		}

		parent::__construct( $message );

	}

	/**
	 * Get the error code assigned to the exception.
	 *
	 * @return string
	 */
	public function getErrorCode() {
		return $this->_error_code;
	}

}
