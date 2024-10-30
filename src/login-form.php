<?php

defined('ABSPATH') or die('Not allowed.');

// Adds Passkey Creationg to WP Login Form
class BYNDID_BeyondIdentityWPLoginForm
{
    private $form_id;
    private $get_authorization_url_callback;

    function __construct($form_id, $get_authorization_url_callback)
    {
        $this->form_id = $form_id;
        $this->get_authorization_url_callback = $get_authorization_url_callback;

        // Add to wp-login form
        add_filter('login_message', array($this, 'add_plugin_to_login_form'));
    }

    function get_authorization_url()
    {
        return call_user_func($this->get_authorization_url_callback);
    }

    // Separate HTML from shortcode to use similar structure and classes as wp-login for customization
    function add_plugin_to_login_form()
    {
        $card_title = __('Continue with passwordless', 'beyond-identity-passwordless');
        $button_title = __('Continue with passwordless', 'beyond-identity-passwordless');
        $description = "";

        if ($GLOBALS['pagenow'] === 'wp-login.php' && !empty($_REQUEST['action']) && $_REQUEST['action'] === 'lostpassword') {
            $card_title = __('Forgot password', 'beyond-identity-passwordless');
            $description = __('Tired of forgetting your password? Use passwordless login instead.', 'beyond-identity-passwordless');
        }
?>
        <div>
            <form class="login_form" action="#" name="login_form" id="<?php echo esc_attr($this->form_id); ?>" method="post">
                <div>
                    <h2>
                        <?php echo esc_html($card_title); ?>
                    </h2>
                    <br />
                    <p><?php echo esc_html($description); ?></p>
                    <br />
                </div>
                <p class="submit">
                    <a style="float:none; width: 100%; text-align: center" class="button button-primary button-large" href="<?php echo esc_url($this->get_authorization_url()); ?>"><?php echo esc_html($button_title); ?></a>
                </p>
            </form>
        </div>
<?php
    }
}
