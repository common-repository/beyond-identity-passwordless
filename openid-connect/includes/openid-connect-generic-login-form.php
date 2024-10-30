<?php

defined('ABSPATH') or die('Not allowed.');

class BYNDID_OpenID_Connect_Generic_Login_Form
{

	/**
	 * Plugin client wrapper instance.
	 *
	 * @var BYNDID_OpenID_Connect_Generic_Client_Wrapper
	 */
	private $client_wrapper;

	/**
	 * The class constructor.
	 *
	 * @param BYNDID_OpenID_Connect_Generic_Client_Wrapper  $client_wrapper A plugin client wrapper object instance.
	 */
	public function __construct($client_wrapper)
	{
		$this->client_wrapper = $client_wrapper;
	}

	/**
	 * Create an instance of the BYNDID_OpenID_Connect_Generic_Login_Form class.
	 *
	 * @param BYNDID_OpenID_Connect_Generic_Client_Wrapper  $client_wrapper A plugin client wrapper object instance.
	 *
	 * @return void
	 */
	public static function register($client_wrapper)
	{
		$login_form = new self($client_wrapper);

		// Add a shortcode for the login button.
		add_shortcode('beyond_identity_login_button', array($login_form, 'make_login_button'));

		$login_form->handle_redirect_login_type_auto();
	}

	/**
	 * Auto Login redirect.
	 *
	 * @return void
	 */
	public function handle_redirect_login_type_auto()
	{

		if (
			'wp-login.php' == $GLOBALS['pagenow']
			&& (!empty($_GET['force_redirect']))
			// Don't send users to the IDP on logout or post password protected authentication.
			&& (!isset($_GET['action']) || !in_array($_GET['action'], array('logout', 'postpass')))
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WP Login Form doesn't have a nonce.
			&& !isset($_POST['wp-submit'])
		) {
			if (!isset($_GET['login-error'])) {
				wp_redirect($this->client_wrapper->get_authentication_url());
				exit;
			}
		}
	}

	/**
	 * Display an error message to the user.
	 *
	 * @param string $error_code    The error code.
	 * @param string $error_message The error message test.
	 *
	 * @return string
	 */
	public function make_error_output($error_code, $error_message)
	{

		ob_start();
?>
		<div id="login_error"><?php // translators: %1$s is the error code from the IDP. 
								?>
			<strong><?php printf(esc_html__('ERROR (%1$s)', 'beyond-identity-passwordless'), esc_html($error_code)); ?>: </strong>
			<?php print esc_html($error_message); ?>
		</div>
<?php
		return wp_kses_post(ob_get_clean());
	}

	/**
	 * Create a login button (link).
	 *
	 * @param array $atts Array of optional attributes to override login buton
	 * functionality when used by shortcode.
	 *
	 * @return string
	 */
	public function make_login_button($atts = array())
	{

		$atts = shortcode_atts(
			array(
				'button_text' => __('Continue with passwordless', 'beyond-identity-passwordless'),
				'button_color' => '#4673D3',
				'button_text_color' => '#FFFFFF',
				'button_border_color' => '#4673D3',
				'redirect_to' => home_url(),
			),
			$atts,
			'beyond_identity_login_button'
		);

		$text = esc_html($atts['button_text']);
		$button_color = esc_html($atts['button_color']);
		$button_text_color = esc_html($atts['button_text_color']);
		$button_border_color = esc_html($atts['button_border_color']);
		$redirect_to = esc_html($atts['redirect_to']);

		$href = $this->client_wrapper->get_authentication_url(array(
			'redirect_to' => $redirect_to,
		));
		$href = esc_url_raw($href);

		$login_button = '<div class="openid-connect-login-button" style="margin: 1em 0; text-align: center;">'
			. '<a class="button button-large" style="text-decoration: none; background: ' . $button_color . '; color: ' . $button_text_color . '; font-size: 1.1em; padding: 0.2em 1em; border-color: ' . $button_border_color . '; border-width: 2px; border-radius: 0.2em;" href="' . $href . '">' . $text . '</a>'
			. '</div>';

		return $login_button;
	}
}
