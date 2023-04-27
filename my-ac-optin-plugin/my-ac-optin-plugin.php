<?php

/*
Plugin Name: My AC Opt-in Plugin
Plugin URI: https://supersitegood.com/my-ac-optin-plugin
Description: A custom plugin for opt-in forms and ActiveCampaign integration (Skill Test TimCakep.com)
Version: 1.0
Author: Nur Yanwar Affandi
Author URI: https://supersitegood.com
License: GPL2
*/

// Add custom post type for subscribers
function my_acoptin_register_subscriber_post_type() {
    $args = array(
        'public' => false,
        'label' => 'Subscribers',
        'supports' => array( 'title' ),
        'menu_icon' => 'dashicons-email-alt',
    );
    register_post_type( 'my_acoptin_subscriber', $args );
}
add_action( 'init', 'my_acoptin_register_subscriber_post_type' );

// Add opt-in form shortcode
function my_acoptin_optin_form_shortcode() {
    $output = '<form method="post" action="' . esc_url( admin_url('admin-post.php') ) . '">';
    $output .= '<input type="hidden" name="action" value="my_acoptin_submit_form">';
    $output .= '<input type="hidden" name="redirect" value="' . esc_url( get_permalink() ) . '">';
    $output .= '<input type="text" name="name" placeholder="Your name" required>';
    $output .= '<input type="email" name="email" placeholder="Your email address" required>';
    $output .= '<input type="submit" value="Subscribe">';
    $output .= '</form>';
    return $output;
}
add_shortcode( 'my_acoptin_optin_form', 'my_acoptin_optin_form_shortcode' );

// Add settings menu
function my_acoptin_settings_menu() {
    add_options_page(
        'My AC Opt-in Plugin Settings',
        'My AC Opt-in Plugin',
        'manage_options',
        'my-ac-optin-plugin-settings',
        'my_acoptin_settings_page'
    );
}
add_action( 'admin_menu', 'my_acoptin_settings_menu' );

// Add settings page
function my_acoptin_settings_page() {
    ?>
    <div class="wrap">
        <h1>My AC Opt-in Plugin Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'my_acoptin_settings_group' ); ?>
            <?php do_settings_sections( 'my-ac-optin-plugin-settings' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API URL</th>
                    <td><input type="text" name="my_acoptin_api_url" value="<?php echo esc_attr( get_option('my_acoptin_api_url') ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td><input type="text" name="my_acoptin_api_key" value="<?php echo esc_attr( get_option('my_acoptin_api_key') ); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register settings
function my_acoptin_register_settings() {
    register_setting( 'my_acoptin_settings_group', 'my_acoptin_api_url' );
    register_setting( 'my_acoptin_settings_group', 'my_acoptin_api_key' );
}
add_action( 'admin_init', 'my_acoptin_register_settings' );

function my_acoptin_test_api_connection() {
    // Get ActiveCampaign API credentials
    $api_url = get_option( 'my_acoptin_api_url' );
    $api_key = get_option( 'my_acoptin_api_key' );

    // Connect to ActiveCampaign API and get account information
    $ac = new ActiveCampaign( $api_url, $api_key );
    $response = $ac->api( "account/view" );

    // Check if API connection is successful
    if ( isset( $response->success ) && $response->success == 1 ) {
        return true;
    } else {
        return false;
    }
}

function my_acoptin_admin_init() {
    // Check if API connection is successful
    if ( my_acoptin_test_api_connection() ) {
        add_action( 'admin_notices', 'my_acoptin_api_success_message' );
    } else {
        add_action( 'admin_notices', 'my_acoptin_api_error_message' );
    }
}

function my_acoptin_api_success_message() {
    echo '<div class="notice notice-success is-dismissible">
        <p>' . esc_html__( 'API connection test successful.', 'my-ac-optin' ) . '</p>
    </div>';
}

function my_acoptin_api_error_message() {
    echo '<div class="notice notice-error is-dismissible">
        <p>' . esc_html__( 'API connection test failed. Please check your API credentials.', 'my-ac-optin' ) . '</p>
    </div>';
}

// Test ActiveCampaign API connection
function my_acoptin_test_api_connection() {
    $api_url = get_option( 'my_acoptin_api_url' );
    $api_key = get_option( 'my_acoptin_api_key' );

    // Initialize API client
    $ac = new ActiveCampaign( $api_url, $api_key );

    // Test connection
    try {
        $account = $ac->api( "account/view" );
        if ( isset( $account->account ) ) {
            // API connection successful
            echo '<div class="notice notice-success"><p>API connection successful.</p></div>';
        } else {
            // API connection failed
            echo '<div class="notice notice-error"><p>API connection failed. Please check your API URL and key.</p></div>';
        }
    } catch ( Exception $e ) {
        // API connection failed
        echo '<div class="notice notice-error"><p>API connection failed. Please check your API URL and key.</p></div>';
    }
}

// Add test API connection button to settings page
function my_acoptin_settings_page() {
    ?>
    <div class="wrap">
        <h1>My AC Opt-in Plugin Settings</h1>
        <?php my_acoptin_test_api_connection(); ?>
        <form method="post" action="options.php">
            <?php settings_fields( 'my_acoptin_settings_group' ); ?>
            <?php do_settings_sections( 'my-ac-optin-plugin-settings' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API URL</th>
                    <td><input type="text" name="my_acoptin_api_url" value="<?php echo esc_attr( get_option('my_acoptin_api_url') ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td><input type="text" name="my_acoptin_api_key" value="<?php echo esc_attr( get_option('my_acoptin_api_key') ); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Create ActiveCampaign list
function my_acoptin_create_list( $list_name ) {
    $api_url = get_option( 'my_acoptin_api_url' );
    $api_key = get_option( 'my_acoptin_api_key' );

    // Initialize API client
    $ac = new ActiveCampaign( $api_url, $api_key );

    // Create list
    $list = array(
        "name" => $list_name
    );
    $response = $ac->api( "list/add", $list );

    if ( isset( $response->success ) && $response->success == 1 ) {
        return $response->id;
    } else {
        return false;
    }
}

// Add form to create ActiveCampaign list
function my_acoptin_settings_page() {
    ?>
    <div class="wrap">
        <h1>My AC Opt-in Plugin Settings</h1>
        <?php my_acoptin_test_api_connection(); ?>
        <form method="post" action="options.php">
            <?php settings_fields( 'my_acoptin_settings_group' ); ?>
            <?php do_settings_sections( 'my-ac-optin-plugin-settings' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API URL</th>
                    <td><input type="text" name="my_acoptin_api_url" value="<?php echo esc_attr( get_option('my_acoptin_api_url') ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td><input type="text" name="my_acoptin_api_key" value="<?php echo esc_attr( get_option('my_acoptin_api_key') ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Create List</th>
                    <td>
                        <input type="text" name="my_acoptin_list_name" placeholder="List Name" />
                        <button type="submit" name="my_acoptin_create_list" class="button">Create List</button>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Get ActiveCampaign lists
function my_acoptin_get_lists() {
    $api_url = get_option( 'my_acoptin_api_url' );
    $api_key = get_option( 'my_acoptin_api_key' );

    // Initialize API client
    $ac = new ActiveCampaign( $api_url, $api_key );

    // Get all lists
    $response = $ac->api( "list/list", array( "ids" => "all" ) );

    if ( isset( $response->success ) && $response->success == 1 ) {
        return $response->lists;
    } else {
        return false;
    }
}

// Add dropdown to select ActiveCampaign list
function my_acoptin_settings_page() {
    ?>
    <div class="wrap">
        <h1>My AC Opt-in Plugin Settings</h1>
        <?php my_acoptin_test_api_connection(); ?>
        <form method="post" action="options.php">
            <?php settings_fields( 'my_acoptin_settings_group' ); ?>
            <?php do_settings_sections( 'my-ac-optin-plugin-settings' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API URL</th>
                    <td><input type="text" name="my_acoptin_api_url" value="<?php echo esc_attr( get_option('my_acoptin_api_url') ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td><input type="text" name="my_acoptin_api_key" value="<?php echo esc_attr( get_option('my_acoptin_api_key') ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">ActiveCampaign List</th>
                    <td>
                        <select name="my_acoptin_list_id">
                            <?php
                            $lists = my_acoptin_get_lists();
                            if ( $lists ) {
                                foreach ( $lists as $list ) {
                                    echo '<option value="' . $list->id . '">' . $list->name . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Add shortcode to display form
function my_acoptin_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'list' => ''
    ), $atts );

    $list_id = $atts['list'];

    $html = '<form action="' . admin_url( 'admin-ajax.php' ) . '" method="POST" class="my-ac-optin-form">';
    $html .= '<input type="hidden" name="action" value="my_acoptin_submit_form">';
    $html .= '<input type="hidden" name="list_id" value="' . esc_attr( $list_id ) . '">';
    $html .= '<div class="form-group">';
    $html .= '<label for="name">Nama Lengkap</label>';
    $html .= '<input type="text" name="name" id="name" class="form-control" required>';
    $html .= '</div>';
    $html .= '<div class="form-group">';
    $html .= '<label for="email">Email</label>';
    $html .= '<input type="email" name="email" id="email" class="form-control" required>';
    $html .= '</div>';
    $html .= '<button type="submit" class="btn btn-primary">Subscribe</button>';
    $html .= '</form>';

    return $html;
}
add_shortcode( 'my_ac', 'my_acoptin_shortcode' );

// Enqueue script for AJAX
function my_acoptin_enqueue_script() {
    wp_enqueue_script( 'my-ac-optin-script', plugin_dir_url( __FILE__ ) . 'js/my-ac-optin-script.js', array( 'jquery' ), '1.0', true );
    wp_localize_script( 'my-ac-optin-script', 'my_ac_optin', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' )
    ));
}
add_action( 'wp_enqueue_scripts', 'my_acoptin_enqueue_script' );

// Process form submission with AJAX
function my_acoptin_submit_form() {
    $list_id = $_POST['list_id'];
    $name = sanitize_text_field( $_POST['name'] );
    $email = sanitize_email( $_POST['email'] );

    // Add subscriber to ActiveCampaign
    $ac = new ActiveCampaign();
    $ac->setApiUrl( MY_AC_API_URL );
    $ac->setApiKey( MY_AC_API_KEY );
    $ac->api( "contact/sync", array(
        "email" => $email,
        "first_name" => $name,
        "p[{$list_id}]" => $list_id,
        "status[{$list_id}]" => 1,
    ));

    // Add subscriber to WordPress custom post type
    $args = array(
        'post_title'    => $name,
        'post_status'   => 'publish',
        'post_type'     => 'my_ac_subscriber',
        'meta_input'    => array(
            'email'     => $email,
            'list_id'   => $list_id,
        ),
    );
    wp_insert_post( $args );

    wp_send_json_success( 'Success!' );
}
add_action( 'wp_ajax_my_acoptin_submit_form', 'my_acoptin_submit_form' );
add_action( 'wp_ajax_nopriv_my_acoptin_submit_form', 'my_acoptin_submit_form' );
