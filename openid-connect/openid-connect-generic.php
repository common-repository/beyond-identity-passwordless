<?php

/**
 * BYNDID_OpenID_Connect_Generic class.
 * 
 * Wrapper for OpenID Connect Generic Client, details below.
 * 
 * This plugin provides the ability to authenticate users with Identity
 * Providers using the OpenID Connect OAuth2 API with Authorization Code Flow.
 *
 * package   BYNDID_OpenID_Connect_Generic
 * category  General
 * author    Jonathan Daggerhart <jonathan@daggerhart.com>
 * copyright 2015-2020 daggerhart
 * license   http://www.gnu.org/licenses/gpl-2.0.txt GPL-2.0+
 * link      https://github.com/daggerhart
 *
 * Plugin Name:       OpenID Connect Generic
 * Plugin URI:        https://github.com/daggerhart/openid-connect-generic
 * Description:       Connect to an OpenID Connect generic client using Authorization Code Flow.
 * Version:           3.9.1
 * Requires at least: 4.9
 * Requires PHP:      7.2
 * Author:            daggerhart
 * Author URI:        http://www.daggerhart.com
 * Text Domain:       daggerhart-openid-connect-generic
 * Domain Path:       /languages
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/daggerhart/openid-connect-generic
 */

defined('ABSPATH') or die('Not allowed.');

require_once 'includes/openid-connect-generic-option-settings.php';
require_once 'includes/openid-connect-generic-client-wrapper.php';
require_once 'includes/openid-connect-generic-client.php';
require_once 'includes/openid-connect-generic-login-form.php';

class BYNDID_OpenID_Connect_Generic
{

	/**
	 * Singleton instance of self
	 *
	 * @var BYNDID_OpenID_Connect_Generic
	 */
	protected static $_instance = null;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '3.9.1';

	/**
	 * Plugin settings.
	 *
	 * @var BYNDID_OpenID_Connect_Generic_Option_Settings
	 */
	private $settings;

	/**
	 * Openid Connect Generic client
	 *
	 * @var BYNDID_OpenID_Connect_Generic_Client
	 */
	private $client;

	/**
	 * Client wrapper.
	 *
	 * @var BYNDID_OpenID_Connect_Generic_Client_Wrapper
	 */
	public $client_wrapper;

	/**
	 * Setup the plugin
	 *
	 * @param BYNDID_OpenID_Connect_Generic_Option_Settings $settings The settings object.
	 *
	 * @return void
	 */
	public function __construct(BYNDID_OpenID_Connect_Generic_Option_Settings $settings)
	{
		$this->settings = $settings;
		self::$_instance = $this;
	}

	/**
	 * WordPress Hook 'init'.
	 *
	 * @return void
	 */
	public function init()
	{

		$redirect_uri = admin_url('admin-ajax.php?action=openid-connect-authorize');

		if ($this->settings->alternate_redirect_uri) {
			$redirect_uri = site_url('/openid-connect-authorize');
		}

		$state_time_limit = 180;
		if ($this->settings->state_time_limit) {
			$state_time_limit = intval($this->settings->state_time_limit);
		}

		$this->client = new BYNDID_OpenID_Connect_Generic_Client(
			$this->settings->client_id,
			$this->settings->client_secret,
			$this->settings->scope,
			$this->settings->endpoint_login,
			$this->settings->endpoint_userinfo,
			$this->settings->endpoint_token,
			$redirect_uri,
			$this->settings->acr_values,
			$state_time_limit,
		);

		$this->client_wrapper = BYNDID_OpenID_Connect_Generic_Client_Wrapper::register($this->client, $this->settings);
		if (defined('WP_CLI') && WP_CLI) {
			return;
		}

		BYNDID_OpenID_Connect_Generic_Login_Form::register($this->client_wrapper);

		// Add a shortcode to get the auth URL.
		add_shortcode('beyond_identity_auth_url', array($this->client_wrapper, 'get_authentication_url'));
	}

	/**
	 * Check if privacy enforcement is enabled, and redirect users that aren't
	 * logged in.
	 *
	 * @return void
	 */
	public function enforce_privacy_redirect()
	{
		if ($this->settings->enforce_privacy && !is_user_logged_in()) {
			// The client endpoint relies on the wp-admin ajax endpoint.
			if (!defined('DOING_AJAX') || !constant('DOING_AJAX') || !isset($_GET['action']) || 'openid-connect-authorize' != $_GET['action']) {
				auth_redirect();
			}
		}
	}

	/**
	 * Enforce privacy settings for rss feeds.
	 *
	 * @param string $content The content.
	 *
	 * @return mixed
	 */
	public function enforce_privacy_feeds($content)
	{
		if ($this->settings->enforce_privacy && !is_user_logged_in()) {
			$content = __('Private site', 'beyond-identity-passwordless');
		}
		return $content;
	}

	/**
	 * Simple autoloader.
	 *
	 * @param string $class The class name.
	 *
	 * @return void
	 */
	public static function autoload($class)
	{
		$prefix = 'BYNDID_OpenID_Connect_Generic_';

		if (stripos($class, $prefix) !== 0) {
			return;
		}

		$filename = $class . '.php';

		// Internal files are all lowercase and use dashes in filenames.
		if (false === strpos($filename, '\\')) {
			$filename = strtolower(str_replace('_', '-', $filename));
		} else {
			$filename  = str_replace('\\', DIRECTORY_SEPARATOR, $filename);
		}

		$filepath = dirname(__FILE__) . '/includes/' . $filename;

		if (file_exists($filepath)) {
			require_once $filepath;
		}
	}

	/**
	 * Instantiate the plugin and hook into WordPress.
	 *
	 * @return void
	 */
	public static function bootstrap()
	{
		/**
		 * This is a documented valid call for spl_autoload_register.
		 *
		 * @link https://www.php.net/manual/en/function.spl-autoload-register.php#71155
		 */
		spl_autoload_register(array('BYNDID_OpenID_Connect_Generic', 'autoload'));

		require_once plugin_dir_path(__FILE__) . '../src/settings.php';

		$tenantId = get_option(BYNDID_BeyondIdentitySettings\BYNDID_TENANT_ID);
		$realmId = get_option(BYNDID_BeyondIdentitySettings\BYNDID_REALM_ID);
		$applicationId = get_option(BYNDID_BeyondIdentitySettings\BYNDID_APPLICATION_ID);
		$applicationClientId = get_option(BYNDID_BeyondIdentitySettings\BYNDID_APPLICATION_CLIENT_ID);
		$applicationClientSecret = get_option(BYNDID_BeyondIdentitySettings\BYNDID_APPLICATION_CLIENT_SECRET);
		$region = get_option(BYNDID_BeyondIdentitySettings\BYNDID_REGION);

		$settings = new BYNDID_OpenID_Connect_Generic_Option_Settings(
			// Default settings values.
			array(
				// OAuth client settings.
				'login_type'           => 'button',
				'client_id'            => $applicationClientId,
				'client_secret'        => $applicationClientSecret,
				'scope'                => 'email openid',
				'endpoint_login'       => "https://auth-$region.beyondidentity.com/v1/tenants/$tenantId/realms/$realmId/applications/$applicationId/authorize",
				'endpoint_userinfo'    => "https://auth-$region.beyondidentity.com/v1/tenants/$tenantId/realms/$realmId/applications/$applicationId/userinfo",
				'endpoint_token'       => "https://auth-$region.beyondidentity.com/v1/tenants/$tenantId/realms/$realmId/applications/$applicationId/token",
				'endpoint_end_session' => '',
				'acr_values'           => '',

				// Non-standard settings.
				'no_sslverify'    => 0,
				'http_request_timeout' => 5,
				'identity_key'    => 'sub',
				'nickname_key'    => 'sub',
				'email_format'       => '{email}',
				'displayname_format' => '',
				'identify_with_username' => false,

				// Plugin settings.
				'enforce_privacy' => 0,
				'alternate_redirect_uri' => 0,
				'token_refresh_enable' => 1,
				'link_existing_users' => 1,
				'create_if_does_not_exist' => 1,
				'redirect_user_back' => 0,
				'redirect_on_logout' => 1,
				'enable_logging'  => 0,
				'log_limit'       => 1000,
			)
		);

		$plugin = new self($settings);

		add_action('init', array($plugin, 'init'));

		// Privacy hooks.
		add_action('template_redirect', array($plugin, 'enforce_privacy_redirect'), 0);
		add_filter('the_content_feed', array($plugin, 'enforce_privacy_feeds'), 999);
		add_filter('the_excerpt_rss', array($plugin, 'enforce_privacy_feeds'), 999);
		add_filter('comment_text_rss', array($plugin, 'enforce_privacy_feeds'), 999);
	}

	/**
	 * Create (if needed) and return a singleton of self.
	 *
	 * @return BYNDID_OpenID_Connect_Generic
	 */
	public static function instance()
	{
		if (null === self::$_instance) {
			self::bootstrap();
		}
		return self::$_instance;
	}
}

BYNDID_OpenID_Connect_Generic::instance();
