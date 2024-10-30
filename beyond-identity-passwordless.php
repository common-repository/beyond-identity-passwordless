<?php

/**
 * Plugin Name:       Beyond Identity Passwordless
 * Description:       Passwordless Authentication for Wordpress.
 * Version:           1.0.0
 * Author:            Beyond Identity
 * Author URI:        http://www.beyondidentity.com
 * Text Domain:       beyond-identity-passwordless
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * 
 * @package   BeyondIdentityPasswordless
 * @category  Authentication
 * @license   http://www.gnu.org/licenses/gpl-2.0.txt GPL-2.0+
 */

defined('ABSPATH') or die('Not allowed.');

class BeyondIdentityPasswordless
{
    // form_id is used for inline CSS
    private $form_id = 'beyond_identity_passkey_form';
    private $oidc;

    function __construct()
    {
        require_once plugin_dir_path(__FILE__) . 'openid-connect/openid-connect-generic.php';
        $this->oidc = BYNDID_OpenID_Connect_Generic::instance();

        // Add endpoint on passkey completion to kick off authentication immediately
        add_action('rest_api_init', function () {
            register_rest_route('beyondidentity/v1', '/passkeySuccess', array(
                'methods' => 'GET',
                'callback' => array($this, 'getPasskeySuccess'),
            ));
        });
    }

    function getPasskeySuccess(WP_REST_Request $request)
    {
        $redirectTo = $request->get_param('redirectTo');
        $redirect_url = $this->get_authorization_url(array(
            'redirect_to' => $redirectTo,
        ));

        // Redirect the user
        return new WP_REST_Response(null, 302, array('Location' => $redirect_url));
    }

    function register()
    {
        if (is_admin()) {
            require_once plugin_dir_path(__FILE__) . 'src/settings.php';
            require_once plugin_dir_path(__FILE__) . 'src/users-page.php';
            $settings = new BYNDID_BeyondIdentitySettings\SettingsPage();
            // must match id used to add_user_meta in openid-connect-generic-client.php
            $user_page = new BYNDID_BeyondIdentityUsersPage('beyond_identity_user_id');
        }

        require_once plugin_dir_path(__FILE__) . 'src/login-form.php';

        $login_form = new BYNDID_BeyondIdentityWPLoginForm($this->form_id, array($this, 'get_authorization_url'));
    }

    // return single use authentication url - fall back to home_url
    // send as callback of get_authorization_url to get new single use auth urls and client_wrapper isn't ready yet.
    function get_authorization_url($args = array())
    {
        $client_wrapper = $this->oidc->client_wrapper;
        if (isset($client_wrapper)) {
            return $client_wrapper->get_authentication_url($args);
        }

        return home_url();
    }

    function activate()
    {
    }

    function deactivate()
    {
    }
}

if (class_exists('BeyondIdentityPasswordless')) {
    $beyondIdentity = new BeyondIdentityPasswordless();
    $beyondIdentity->register();
}

register_activation_hook(__FILE__, array($beyondIdentity, 'activate'));
register_deactivation_hook(__FILE__, array($beyondIdentity, 'deactivate'));
