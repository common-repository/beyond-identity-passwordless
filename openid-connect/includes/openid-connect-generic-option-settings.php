<?php
/**
 * BYNDID_OpenID_Connect_Generic_Option_Settings class.
 *
 * OAuth Client Settings:
 *
 * @property string $login_type           How the client (login form) should provide login options.
 * @property string $client_id            The ID the client will be recognized as when connecting the to Identity provider server.
 * @property string $client_secret        The secret key the IDP server expects from the client.
 * @property string $scope                The list of scopes this client should access.
 * @property string $endpoint_login       The IDP authorization endpoint URL.
 * @property string $endpoint_userinfo    The IDP User information endpoint URL.
 * @property string $endpoint_token       The IDP token validation endpoint URL.
 * @property string $endpoint_end_session The IDP logout endpoint URL.
 * @property string $acr_values           The Authentication contract as defined on the IDP.
 *
 * Non-standard Settings:
 *
 * @property bool   $no_sslverify           The flag to enable/disable SSL verification during authorization.
 * @property int    $http_request_timeout   The timeout for requests made to the IDP. Default value is 5.
 * @property string $identity_key           The key in the user claim array to find the user's identification data.
 * @property string $nickname_key           The key in the user claim array to find the user's nickname.
 * @property string $email_format           The key(s) in the user claim array to formulate the user's email address.
 * @property string $displayname_format     The key(s) in the user claim array to formulate the user's display name.
 * @property bool   $identify_with_username The flag which indicates how the user's identity will be determined.
 * @property int    $state_time_limit       The valid time limit of the state, in seconds. Defaults to 180 seconds.
 *
 * Plugin Settings:
 *
 * @property bool $enforce_privacy          The flag to indicates whether a user us required to be authenticated to access the site.
 * @property bool $alternate_redirect_uri   The flag to indicate whether to use the alternative redirect URI.
 * @property bool $token_refresh_enable     The flag whether to support refresh tokens by IDPs.
 * @property bool $link_existing_users      The flag to indicate whether to link to existing WordPress-only accounts or greturn an error.
 * @property bool $create_if_does_not_exist The flag to indicate whether to create new users or not.
 * @property bool $redirect_user_back       The flag to indicate whether to redirect the user back to the page on which they started.
 * @property bool $redirect_on_logout       The flag to indicate whether to redirect to the login screen on session expiration.
 * @property bool $enable_logging           The flag to enable/disable logging.
 * @property int  $log_limit                The maximum number of log entries to keep.
 */

defined('ABSPATH') or die('Not allowed.');

class BYNDID_OpenID_Connect_Generic_Option_Settings
{

	/**
	 * Stored option values array.
	 *
	 * @var array<mixed>
	 */
	private $values;

	/**
	 * The class constructor.
	 *
	 * @param array<mixed> $default_settings  The default plugin settings values.
	 */
	public function __construct($default_settings = array())
	{
		$this->values = $default_settings;
	}

	/**
	 * Magic getter for settings.
	 *
	 * @param string $key The array key/option name.
	 *
	 * @return mixed
	 */
	public function __get($key)
	{
		if (isset($this->values[$key])) {
			return $this->values[$key];
		}
	}

	/**
	 * Magic setter for settings.
	 *
	 * @param string $key   The array key/option name.
	 * @param mixed  $value The option value.
	 *
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->values[$key] = $value;
	}

	/**
	 * Magic method to check is an attribute isset.
	 *
	 * @param string $key The array key/option name.
	 *
	 * @return bool
	 */
	public function __isset($key)
	{
		return isset($this->values[$key]);
	}

	/**
	 * Magic method to clear an attribute.
	 *
	 * @param string $key The array key/option name.
	 *
	 * @return void
	 */
	public function __unset($key)
	{
		unset($this->values[$key]);
	}

	/**
	 * Get the plugin settings array.
	 *
	 * @return array
	 */
	public function get_values()
	{
		return $this->values;
	}
}
