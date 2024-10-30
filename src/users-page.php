<?php
defined('ABSPATH') or die('Not allowed.');

// Handle displaying Beyond Identity meta data in the Admin's User page. 
class BYNDID_BeyondIdentityUsersPage
{
    private $beyond_identity_user_meta_data_key;

    function __construct($beyond_identity_user_meta_data_key)
    {
        $this->beyond_identity_user_meta_data_key = $beyond_identity_user_meta_data_key;

        // Add is paswordless User meta data to User screen for Admin
        add_filter('manage_users_columns', array($this, 'show_user_bi_id_meta_data'));
        add_filter('manage_users_custom_column', array($this, 'custom_user_column_content'), 10, 3);
        add_filter('views_users', array($this, 'passwordless_user_count'), 10, 1);

        // Filter users by meta data
        add_action('pre_get_users', array($this, 'filter_users_by_meta_data'));
    }

    function show_user_bi_id_meta_data($columns)
    {
        // Add a new column to the Users screen
        $columns[$this->beyond_identity_user_meta_data_key] = __('Beyond Identity ID', 'beyond-identity-passwordless');
        return $columns;
    }

    function custom_user_column_content($value, $column_name, $user_id)
    {
        // Display the meta data for the user if the column name matches
        if ($this->beyond_identity_user_meta_data_key === $column_name) {
            $beyond_identity = get_user_meta($user_id, $this->beyond_identity_user_meta_data_key, true);
            $value = !empty($beyond_identity) ? esc_html($beyond_identity) : '-';
        }
        return $value;
    }

    function passwordless_user_count($views)
    {
        $count = 0;
        $users_url = add_query_arg(
            array(
                'beyond_identity_user' => 'true',
            ),
            admin_url('users.php')
        );

        $users = get_users(array(
            'meta_key' => $this->beyond_identity_user_meta_data_key,
            'meta_value' => '',
            'meta_compare' => '!='
        ));

        if ($users) {
            $count = count($users);
            $views[$this->beyond_identity_user_meta_data_key] = '<a href="' . $users_url . '">' . sprintf(__('Beyond Identity (%d)', 'beyond-identity-passwordless'), $count) . '</a>';
        } else {
            $views[$this->beyond_identity_user_meta_data_key] = sprintf(__('Beyond Identity (%d)', 'beyond-identity-passwordless'), $count);
        }

        return $views;
    }

    function filter_users_by_meta_data($query)
    {
        if (isset($_GET['beyond_identity_user']) && $_GET['beyond_identity_user'] === 'true') {
            $query->query_vars['meta_key'] = $this->beyond_identity_user_meta_data_key;
            $query->query_vars['meta_value'] = '';
            $query->query_vars['meta_compare'] = '!=';
        }
    }
}
