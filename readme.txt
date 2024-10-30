=== Beyond Identity Passwordless ===
Contributors: annagarcia
Tags: passwordless, login, authentication, passkeys, security, oauth2, openidconnect, apps
Requires at least: 4.9
Tested up to: 6.3
Stable tag: 1.0.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A passwordless solution that allows users and admins to log into a WordPress website using passkeys with Beyond Identity.

== Description ==

Are you or your customers tired of remembering passwords? 

This plugin provides a secure and convenient solution to log into your WordPress website. With Beyond Identity, you can say goodbye to password fatigue and improve your website's security.

Once activated, you will see:

- Passwordless UI that integrates seamlessly on with the WordPress login page.

- Beyond Identity Settings page for Wordpress admins to configure their Beyond Identity account. 

- Beyond Identity filter on the Wordpress Dashboard's Users page to view which users use passkeys.

**Before you begin**

You will need a Beyond Identity account to configure this plugin.
Beyond Identity currently uses "Universal Passkeys," which are specific to Beyond Identity and have two benefits over your average FIDO2 passkeys.
1. Universal Passkeys never leave the device on which they are created. This makes them much more secure.
2. Universal Passkeys work everywhere. Some browsers (Firefox) do not support passkeys. Universal Passkeys work everywhere, even on Firefox.

**Coming soon:** Vanilla WebAuthn FIDO2 passkeys. These passkeys allow syncing between devices and work with passkey managers. 

As a Beyond Identity admin, you will have several configuration options including selecting passkey flavors and customizing the login page.

== Admin Set Up == 

First, sign up for a free developer account by visiting: https://www.beyondidentity.com/developers

Once you have a developer account you will need to set several values for the OIDC server. Follow the steps below to configure a Beyond Identity application. Most defaults are fine. However make sure the following are set:

1. In your Beyond Identity Console, navigate to the **Apps** tab under Authentication
2. Tap **Add an application**
3. Set **Protocol** to **OIDC**
4. Set **Client Type** to **Confidential**
5. Set **PKCE** to **Disabled**
6. Set **Redirect URIs** to include `https://${your-website-domain.com}/wp-admin/admin-ajax.php?action=openid-connect-authorize`
7. Set **Token Configuration** > **Subject** to **id**
8. At the top of the page, navigate to your application's **Authenticator Config** tab
9. Set **Configuration Type** to **Hosted Web**
10. The recommended defaults for **Authentication Profile** are fine but feel free to modify
11. Tap the **Submit** button to save your changes

Finally, go to your Wordpress dashboard and find the Beyond Identity Settings page. You will need three generated values from your newly created application. You can find the **Issuer URL**, **Client ID**, and **Client Secret** in the Beyond Identity Console's application that you just created. 

For more information on how Beyond Identity works, visit the [developer documentation](http://developer.beyondidentity.com). 

For help, reach out on [Slack](https://join.slack.com/t/byndid/shared_invite/zt-1anns8n83-NQX4JvW7coi9dksADxgeBQ).

== Installation == 

To install the Beyond Identity Passwordless Authentication plugin, follow these steps:

1. Upload to the `/wp-content/plugins/` directory
1. Activate the plugin
1. Visit Settings > Beyond Identity and configure with the tenant you created on sign up.

Once you have configured your account and activated the plugin you will see passwordless UI added to your site. 

== Shortcodes == 

This plugin also provides shortcodes that can be used on any page or post. These include:

`[beyond_identity_login_button]`  
Generates a button to log in with a Beyond Identity Universal Passkey.

`[beyond_identity_auth_url]`  
Generates the authorize URL to log in with a Beyond Identity Universal Passkey.

For information on shortcode customization attributes, please refer to the documentation available in the Settings > Beyond Identity dashboard page after activating the plugin.

== Frequently Asked Questions ==

= How does it work? =

User identification is based on passkeys, device-stored public-private key pairs. A user may opt into passwordless login by providing an email. 

= Does this plugin require any coding knowledge? =

No. This is a no-code solution.

= Will it work with my theme? =

Yes. Buttons and colors should match you current theme automatically. The plugin has been tested on several themes. If you have any problems with your theme, get in touch with us and we will be happy to help! 

= What is the client's Redirect URI in the Beyond Identity Console? =

Your Beyond Identity Application requires a set of allowed redirect URIs for security purposes. In the Beyond Identity application you created for this website, add a redirect URI to the list that follows the format:

`https://${your-website-domain.com}/wp-admin/admin-ajax.php?action=openid-connect-authorize`

= Where can I learn more about Beyond Identity? = 

Visit the [developer documentation](http://developer.beyondidentity.com).

= Where can I get help or provide feedback? = 

Feel free to reach out on [Slack](https://join.slack.com/t/byndid/shared_invite/zt-1anns8n83-NQX4JvW7coi9dksADxgeBQ) for any questions or feedback. We'd love to hear from you!

== Screenshots ==

1. Passwordless login to your WordPress website.
2. Passwordless registration to your WordPress website.
3. Passwordless recovery to your WordPress website.
4. Plugin Settings.
5. Plugin User.

== Changelog ==

Please view CHANGELOG.md

== Upgrade Notice ==