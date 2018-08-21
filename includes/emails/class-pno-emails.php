<?php
/**
 * Handles all the email templates the PNO sends.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2018, Alessandro Tesoro
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The class that handles sending templated emails.
 */
class PNO_Emails {

	/**
	 * Email address from which the email should come from.
	 *
	 * @var string
	 */
	private $from_address;

	/**
	 * The name from which the email should come from.
	 *
	 * @var string
	 */
	private $from_name;

	/**
	 * Content type encoding of the email.
	 *
	 * @var string
	 */
	private $content_type;

	/**
	 * Headers string of the email.
	 *
	 * @var string
	 */
	private $headers;

	/**
	 * Wether the email is being sent as html or not.
	 *
	 * @var boolean
	 */
	private $html = true;

	/**
	 * Selected template for the email.
	 *
	 * @var string
	 */
	private $template;

	/**
	 * Heading title of the email.
	 *
	 * @var string
	 */
	private $heading = '';

	/**
	 * All available dynamic tags of the email.
	 *
	 * @var array
	 */
	private $tags;

	/**
	 * The dynamic user id on which some tags are based.
	 *
	 * @var string
	 */
	private $user_id;

	/**
	 * The dynamic key for password reset.
	 *
	 * @var string
	 */
	private $password_reset_key;

	/**
	 * The password sent to the user upon signup.
	 *
	 * @var string
	 */
	private $plain_text_password;

	/**
	 * Get things started.
	 */
	public function __construct() {

		if ( 'none' === $this->get_template() ) {
			$this->html = false;
		}

		add_action( 'pno_email_send_before', array( $this, 'send_before' ) );
		add_action( 'pno_email_send_after', array( $this, 'send_after' ) );

	}

	/**
	 * Set properties of the class.
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set( $key, $value ) {
		$this->$key = $value;
	}

	/**
	 * Retrieve the "From name" setting for the email.
	 *
	 * @return string
	 */
	public function get_from_name() {
		if ( ! $this->from_name ) {
			$this->from_name = pno_get_option( 'from_name', get_bloginfo( 'name' ) );
		}

		/**
		 * Allows filtering of the "from name" option used within emails sent by PNO.
		 *
		 * @param string $from_name the name set in the options panel or the site's name.
		 */
		return apply_filters( 'pno_email_from_name', wp_specialchars_decode( $this->from_name ), $this );
	}

	/**
	 * Retrieve the "from address" email setting.
	 *
	 * @return string
	 */
	public function get_from_address() {
		if ( ! $this->from_address ) {
			$this->from_address = pno_get_option( 'from_email', get_option( 'admin_email' ) );
		}

		/**
		 * Allows filtering of the "from address" email used to send emails via PNO.
		 *
		 * @param string $from_address the email address specified into the options panel or the site's admin email.
		 */
		return apply_filters( 'pno_email_from_address', $this->from_address, $this );
	}

	/**
	 * Get the content type encoding of the email.
	 *
	 * @return string
	 */
	public function get_content_type() {
		if ( ! $this->content_type && $this->html ) {
			$this->content_type = apply_filters( 'pno_email_default_content_type', 'text/html', $this );
		} elseif ( ! $this->html ) {
			$this->content_type = 'text/plain';
		}
		return apply_filters( 'pno_email_content_type', $this->content_type, $this );
	}

	/**
	 * Retrieve the headers of the email.
	 *
	 * @return string
	 */
	public function get_headers() {
		if ( ! $this->headers ) {
			$this->headers  = "From: {$this->get_from_name()} <{$this->get_from_address()}>\r\n";
			$this->headers .= "Reply-To: {$this->get_from_address()}\r\n";
			$this->headers .= "Content-Type: {$this->get_content_type()}; charset=utf-8\r\n";
		}

		/**
		 * Allows filtering of the email's headers. The headers are for emails sent through PNO.
		 *
		 * @param string $headers
		 */
		return apply_filters( 'pno_email_headers', $this->headers, $this );
	}

	/**
	 * Retrieve a list of available templates for the emails.
	 *
	 * @return array
	 */
	public function get_templates() {
		$templates = array(
			'default' => esc_html__( 'Default Template' ),
			'none'    => __( 'No template, plain text only' ),
		);
		return apply_filters( 'pno_email_templates', $templates );
	}

	/**
	 * Retrieve the selected template from the options panel.
	 *
	 * @return string
	 */
	public function get_template() {
		if ( ! $this->template ) {
			$this->template = pno_get_option( 'email_template', 'default' );
		}
		return apply_filters( 'pno_email_template', $this->template );
	}

	/**
	 * Retrieve the heading title set for the email.
	 *
	 * @return void
	 */
	public function get_heading() {
		return apply_filters( 'pno_email_heading', $this->heading );
	}

	/**
	 * Prepare the email to be sent.
	 *
	 * @param string $message
	 * @return void
	 */
	public function build_email( $message ) {

		if ( false === $this->html ) {
			return apply_filters( 'pno_email_message', wp_strip_all_tags( preg_replace( '/<br(\s+)?\/?>/i', "\n", $message ) ), $this );
		}

		$message = $this->text_to_html( $message );

		ob_start();

		$data = [
			'heading' => $this->heading,
		];

		posterno()->templates
			->set_template_data( $data )
			->get_template_part( 'emails/header', $this->get_template() );

		do_action( 'pno_email_header', $this );

		posterno()->templates
			->set_template_data( $data )
			->get_template_part( 'emails/body', $this->get_template() );

		do_action( 'pno_email_body', $this );

		posterno()->templates
			->set_template_data( $data )
			->get_template_part( 'emails/footer', $this->get_template() );

		do_action( 'pno_email_footer', $this );

		$body    = ob_get_clean();
		$message = str_replace( '{email}', $message, $body );

		return apply_filters( 'pno_email_message', $message, $this );
	}

	/**
	 * Finally send the email now.
	 *
	 * @param string $to
	 * @param string $subject
	 * @param string $message
	 * @param string $attachments
	 * @return void
	 */
	public function send( $to, $subject, $message, $attachments = '' ) {

		if ( ! did_action( 'init' ) && ! did_action( 'admin_init' ) ) {
			_doing_it_wrong( __FUNCTION__, __( 'You cannot send emails with PNO_Emails until init/admin_init has been reached' ), null );
			return false;
		}

		$this->setup_email_tags();

		do_action( 'pno_email_send_before', $this );

		$subject     = $this->parse_tags( $subject );
		$message     = $this->build_email( $message );
		$message     = $this->parse_tags( $message );
		$attachments = apply_filters( 'pno_email_attachments', $attachments, $this );
		$sent        = wp_mail( $to, $subject, $message, $this->get_headers(), $attachments );

		do_action( 'pno_email_send_after', $this );

		return $sent;

	}

	/**
	 * Modify core WP's filter to inject our own settings.
	 *
	 * @return void
	 */
	public function send_before() {
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
	}

	/**
	 * Remove our customized filters after the email is sent.
	 *
	 * @return void
	 */
	public function send_after() {
		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
		$this->heading             = '';
		$this->user_id             = '';
		$this->password_reset_key  = '';
		$this->plain_text_password = '';
	}

	/**
	 * Convert content of the message.
	 *
	 * @param string $message
	 * @return void
	 */
	public function text_to_html( $message ) {
		if ( 'text/html' == $this->content_type || true === $this->html ) {
			$message = apply_filters( 'pno_email_template_wpautop', true ) ? wpautop( $message ) : $message;
			$message = apply_filters( 'pno_email_template_make_clickable', true ) ? make_clickable( $message ) : $message;
			$message = str_replace( '&#038;', '&amp;', $message );
		}
		return $message;
	}

	/**
	 * Parse email tags with the appropriate callback.
	 *
	 * @param string $content
	 * @return string
	 */
	private function parse_tags( $content ) {
		// Make sure there's at least one tag
		if ( empty( $this->tags ) || ! is_array( $this->tags ) ) {
			return $content;
		}
		$new_content = preg_replace_callback( '/{([A-z0-9\-\_]+)}/s', array( $this, 'do_tag' ), $content );
		return $new_content;
	}

	/**
	 * Load all email tags into the class.
	 *
	 * @return void
	 */
	private function setup_email_tags() {
		$tags = $this->get_tags();
		foreach ( $tags as $tag ) {
			if ( isset( $tag['function'] ) && is_callable( $tag['function'] ) ) {
				$this->tags[ $tag['tag'] ] = $tag;
			}
		}
	}

	/**
	 * List of available dynamic email tags.
	 *
	 * @return array
	 */
	public function get_tags() {

		$email_tags = array(
			array(
				'name'        => esc_html__( 'Website name' ),
				'description' => esc_html__( 'Display the name of the website.' ),
				'tag'         => 'sitename',
				'function'    => 'pno_email_tag_sitename',
			),
			array(
				'name'        => esc_html__( 'Website URL' ),
				'description' => esc_html__( 'The website url.' ),
				'tag'         => 'website',
				'function'    => 'pno_email_tag_website',
			),
			array(
				'name'        => esc_html__( 'Username' ),
				'description' => esc_html__( 'Display the user\'s username.' ),
				'tag'         => 'username',
				'function'    => 'pno_email_tag_username',
			),
			array(
				'name'        => esc_html__( 'User email' ),
				'description' => esc_html__( 'Display the user\'s email.' ),
				'tag'         => 'email',
				'function'    => 'pno_email_tag_email',
			),
			array(
				'name'        => esc_html__( 'Plain text password' ),
				'description' => esc_html__( 'Display the password randomly generated at signup or the password chosen by the user.' ),
				'tag'         => 'password',
				'function'    => 'pno_email_tag_password',
			),
			array(
				'name'        => esc_html__( 'Login page url' ),
				'description' => esc_html__( 'Display the login page url.' ),
				'tag'         => 'login_page_url',
				'function'    => 'pno_email_tag_login_page_url',
			),
			array(
				'name'        => esc_html__( 'Password recovery url' ),
				'description' => esc_html__( 'Display the password recovery url.' ),
				'tag'         => 'recovery_url',
				'function'    => 'pno_email_tag_password_recovery_url',
			),
		);

		/**
		 * Allows developers to register or deregister custom email dynamic tags.
		 *
		 * Each new tag takes the following parameter:
		 *
		 * name: the title that will be displayed within the settings panel,
		 * description: explanation of what the tag is,
		 * tag: the actual tag to use into the email's content,
		 * function: php function that parses the content of the tag.
		 *
		 * @param array $email_tags all currently registered tags.
		 */
		return apply_filters( 'pno_email_tags', $email_tags, $this );

	}

	/**
	 * Parse a specific tag with it's own callback.
	 *
	 * @param string $m
	 * @return void
	 */
	private function do_tag( $m ) {
		// Get tag.
		$tag = $m[1];
		// Return tag if not set.
		if ( ! $this->email_tag_exists( $tag ) ) {
			return $m[0];
		}
		return call_user_func( $this->tags[ $tag ]['function'], $this->user_id, $this->password_reset_key, $this->plain_text_password, $tag );
	}

	/**
	 * Check if a tag exists.
	 *
	 * @param string $tag
	 * @return void
	 */
	public function email_tag_exists( $tag ) {
		return array_key_exists( $tag, $this->tags );
	}

}
