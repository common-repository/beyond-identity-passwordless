<?php

namespace BYNDID_BeyondIdentitySettings;

defined('ABSPATH') or die('Not allowed.');

const BYNDID_TENANT_ID = 'beyond_identity_tenant_id';
const BYNDID_REGION = 'beyond_identity_region';
const BYNDID_REALM_ID = 'beyond_identity_realm_id';
const BYNDID_APPLICATION_ID = 'beyond_identity_application_id';

const BYNDID_APPLICATION_ISSUER_URL = 'beyond_identity_application_issuer';
const BYNDID_APPLICATION_CLIENT_ID = 'beyond_identity_application_client_id';
const BYNDID_APPLICATION_CLIENT_SECRET = 'beyond_identity_application_client_secret';

// Handle Displaying Beyond Identity Settings for Admins.
class SettingsPage
{
    private $plugin = 'beyond-identity-passwordless';
    private $settings_page_name = 'beyond_identity_settings';
    private $settings_tenant_section_id = 'beyond_identity_tenant';

    function __construct()
    {
        // Add links to Plugin Page
        add_filter("plugin_action_links_$this->plugin", array($this, 'add_settings_link'));
        add_filter("plugin_action_links_$this->plugin", array($this, 'add_documentation_link'));

        // Add Settings Page to Settings Section and Own Settings Section on Dashboard
        add_action('admin_menu', array($this, 'add_settings_page'));

        // Configure the Settings Page and Fields
        add_action('admin_init', array($this, 'configure_settings'));

        // Parse Issuer URL on update or inital add of application issuer URL option
        add_action('add_option_' . BYNDID_APPLICATION_ISSUER_URL, array($this, 'parse_issuer_url'), 100, 2);
        add_action('update_option_' . BYNDID_APPLICATION_ISSUER_URL, array($this, 'parse_issuer_url'), 100, 2);
    }

    function parse_issuer_url($old_value, $new_value)
    {
        preg_match('/https?:\/\/auth-([a-z]+)\.beyondidentity\.com\/v1\/tenants\/([0-9a-f]+)\/realms\/([0-9a-f]+)\/applications\/([0-9a-f-]+)/i', $new_value, $matches);

        if (count($matches) < 5) {
            update_option(BYNDID_REGION, '');
            update_option(BYNDID_TENANT_ID, '');
            update_option(BYNDID_REALM_ID, '');
            update_option(BYNDID_APPLICATION_ID, '');
        } else {
            update_option(BYNDID_REGION, $matches[1]);
            update_option(BYNDID_TENANT_ID, $matches[2]);
            update_option(BYNDID_REALM_ID, $matches[3]);
            update_option(BYNDID_APPLICATION_ID, $matches[4]);
        }
    }

    public function add_settings_link($links)
    {
        $href = esc_url(get_admin_url()) . "options-general.php?page=$this->settings_page_name";
        $settings_link = '<a href="' . $href . '">' . __('Settings', 'beyond-identity-passwordless') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public function add_documentation_link($links)
    {

        $links['documentation'] = sprintf(
            '<a href="%1$s" target="_blank" font-weight: bold;">%2$s</a>',
            'http://developer.beyondidentity.com',
            __('Documentation', 'beyond-identity-passwordless')
        );

        return $links;
    }

    public function add_settings_page()
    {
        // Add Own Settings Page in Admin Dashboard
        add_menu_page(
            'Beyond Identity Settings',
            'Beyond Identity',
            'manage_options',
            $this->settings_page_name,
            array($this, 'settings_page_index'),
            '', // could add a custom icon
            110
        );

        // Add to the Settings Section
        add_options_page(
            'Beyond Identity Settings',
            'Beyond Identity',
            'manage_options',
            $this->settings_page_name,
            array($this, 'settings_page_index')
        );
    }

    public function settings_page_index()
    {
        // require_once plugin_dir_path(__FILE__) . 'templates/settings.php';
?>
        <div class="wrap">
            <h2><?php print esc_html(get_admin_page_title()); ?></h2>
            <form method="post" action="options.php">
                <?php settings_errors($this->settings_page_name); ?>
                <?php
                // security field
                settings_fields($this->settings_page_name);
                // section
                do_settings_sections($this->settings_page_name);
                submit_button(__('Save Settings', 'beyond-identity-passwordless'));
                ?>
            </form>
            <h4><?php esc_html_e('Shortcodes', 'beyond-identity-passwordless'); ?></h4>
            <p><?php esc_html_e('Shortcodes can be pasted into a WordPress page or post and customized with attributes.', 'beyond-identity-passwordless'); ?></p>
            <p class="description">
                <strong><?php esc_html_e('Login Button Shortcode', 'beyond-identity-passwordless'); ?></strong>
                <code>[beyond_identity_login_button]</code>
            </p>
            <p class="description">
                <strong><?php esc_html_e('Authentication URL Shortcode', 'beyond-identity-passwordless'); ?></strong>
                <code>[beyond_identity_auth_url]</code>
            </p>
            <h4><?php esc_html_e('Configurable Shortcode Attributes', 'beyond-identity-passwordless'); ?></h4>
            <p class="description">
                <strong><?php esc_html_e('Beyond Identity Login Button', 'beyond-identity-passwordless'); ?></strong>
            </p>
            <p class="description">
                <code>[beyond_identity_login_button
                    button_text="Button Text"
                    button_color="#4673D3"
                    button_text_color="#FFFFFF"
                    button_border_color="#4673D3"
                    redirect_to="http://your-home-url.com"]
                </code>
            </p>
            <p class="description">
                <strong><?php esc_html_e('Beyond Identity Authentication URL', 'beyond-identity-passwordless'); ?></strong>
            </p>
            <p class="description"><?php esc_html_e('The default values are generated from the above settings.', 'beyond-identity-passwordless'); ?></p>
            <p class="description">
                <code>[beyond_identity_auth_url]</code>
            </p>
        </div>
    <?php
    }

    function configure_settings()
    {
        // Create a Section
        add_settings_section(
            $this->settings_tenant_section_id,
            __('Configure your Beyond Identity Information', 'beyond-identity-passwordless'),
            array($this, 'settings_section_description'),
            $this->settings_page_name,
        );

        // Preprocess text fields and add them to the page.

        // ISSUER_URL
        register_setting(
            $this->settings_page_name,
            BYNDID_APPLICATION_ISSUER_URL,
            array(
                'type' => 'url',
                'sanitize_callback' => function ($input) {
                    return $this->validate_issuer_url($input);
                },
                'default' => ''
            )
        );

        add_settings_field(
            BYNDID_APPLICATION_ISSUER_URL,
            __('Your Application\'s Issuer URL', 'beyond-identity-passwordless'),
            array($this, 'do_issuer_url'),
            $this->settings_page_name,
            $this->settings_tenant_section_id,
        );

        // CLIENT_ID
        register_setting(
            $this->settings_page_name,
            BYNDID_APPLICATION_CLIENT_ID,
            array(
                'type' => 'id',
                'sanitize_callback' => function ($input) {
                    return $this->sanitize_field_callback($input);
                },
                'default' => ''
            )
        );

        add_settings_field(
            BYNDID_APPLICATION_CLIENT_ID,
            __('Your Application\'s Client ID', 'beyond-identity-passwordless'),
            array($this, 'do_client_id'),
            $this->settings_page_name,
            $this->settings_tenant_section_id,
        );

        // CLIENT_SECRET
        register_setting(
            $this->settings_page_name,
            BYNDID_APPLICATION_CLIENT_SECRET,
            array(
                'type' => 'secret',
                'sanitize_callback' => function ($input) {
                    return $this->sanitize_field_callback($input);
                },
                'default' => ''
            )
        );

        add_settings_field(
            BYNDID_APPLICATION_CLIENT_SECRET,
            __('Your Application\'s Client Secret', 'beyond-identity-passwordless'),
            array($this, 'do_client_secret'),
            $this->settings_page_name,
            $this->settings_tenant_section_id,
        );
    }

    function sanitize_field_callback($input)
    {
        if (empty($input)) {
            return $input;
        }

        $charactersToStrip = "!@#$%^&*()+=~`{}[];?<>";
        $sanitized = str_replace(str_split($charactersToStrip), '', sanitize_text_field($input));

        return $sanitized;
    }

    function validate_issuer_url($input)
    {
        $sanitized = $this->sanitize_field_callback($input);
        $url = esc_url_raw($sanitized);
        $error_message = sprintf(
            __('%s is not a valid URL.', 'beyond-identity-passwordless'),
            $sanitized
        );
        $not_https = sprintf(
            __('%s does not contain `https`.', 'beyond-identity-passwordless'),
            $sanitized
        );

        if ($url && !is_wp_error($url)) {
            if (strpos($url, 'http://') !== false) {
                add_settings_error($this->settings_page_name, 'invalid_protocol', esc_html($not_https), 'error');
            }
            return $url;
        } else {
            add_settings_error($this->settings_page_name, 'invalid_url', esc_html($error_message), 'error');
            return get_option(BYNDID_APPLICATION_ISSUER_URL); // Return the previous value or a default
        }
    }

    public function settings_section_description()
    {
        $region = get_option(BYNDID_REGION);
        $region = strtolower(isset($region) && !empty($region) ? $region : 'us');

        $url = "https://console-$region.beyondidentity.com/login";

        $tenant_details = "";
        $tenant_id = get_option(BYNDID_TENANT_ID);

        if ($tenant_id) {
            $tenant_details = sprintf(esc_html__('Your Tenant ID is %s', 'beyond-identity-passwordless'), $tenant_id);
        }

        return printf(
            '<p class="description">%s <a href="%s">%s</a></p>
             <p class="description">%s</p>',
            esc_html__('Open your console to find these values:', 'beyond-identity-passwordless'),
            esc_url($url),
            esc_html($url),
            esc_html($tenant_details),
        );
    }

    function do_issuer_url()
    {
        $id = BYNDID_APPLICATION_ISSUER_URL;
        $current_option = get_option($id);
        $current_value = isset($current_option) ? esc_attr($current_option) : '';
    ?>
        <input type="text" name="<?php print esc_attr($id); ?>" class="text_field" value="<?php echo esc_html($current_value); ?>" />
        <p class="description">
            <?php echo wp_kses_post(__('Navigate to the App you created (in your realm) and find the Issuer URL.', 'beyond-identity-passwordless')); ?>
        </p>
    <?php
    }

    function do_client_id()
    {
        $id = BYNDID_APPLICATION_CLIENT_ID;
        $current_option = get_option($id);
        $current_value = isset($current_option) ? esc_attr($current_option) : '';
    ?>
        <input type="text" name="<?php print esc_attr($id); ?>" class="text_field" value="<?php echo esc_html($current_value); ?>" />
        <p class="description">
            <?php echo wp_kses_post(__('Navigate to the App you created (in your realm) and find it\'s Client ID.', 'beyond-identity-passwordless')); ?>
        </p>
    <?php
    }

    function do_client_secret()
    {
        $id = BYNDID_APPLICATION_CLIENT_SECRET;
        $current_option = get_option($id);
        $current_value = isset($current_option) ? esc_attr($current_option) : '';
    ?>
        <input type="password" name="<?php print esc_attr($id); ?>" class="text_field" value="<?php echo esc_html($current_value); ?>" />
        <p class="description">
            <?php echo wp_kses_post(__('Navigate to the App you created (in your realm) and find it\'s Client Secret.', 'beyond-identity-passwordless')); ?>
        </p>
<?php
    }
}
