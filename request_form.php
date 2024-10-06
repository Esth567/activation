<?php
/**
 * @package Request Form 
 */
/*
Plugin Name: Tiiza Request Form 
Plugin URI: https://tiiza.com.ng/about-us/
Description: This form is for the activation of the tag numbers.
Version: 5.4.19
Author: Esther Bassey
Author URI: http://ma.tt/
License: GPLv3 or later
Text Domain: request-form  
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2005-2024 Automattic, Inc.
*/

if ( ! defined( 'ABSPATH' )) {
    die;
};

class RequestForm {
    function __construct() 
    {
         // Include required files
        include_once plugin_dir_path(__FILE__) . 'includes/activation.php';
        include_once plugin_dir_path(__FILE__) . 'includes/found_report.php';
        include_once plugin_dir_path(__FILE__) . 'includes/device_registered.php';
        include_once plugin_dir_path(__FILE__) . 'includes/lost_report.php';
        include_once plugin_dir_path(__FILE__) . 'includes/mobile_tracking.php';
        include_once plugin_dir_path(__FILE__) . 'includes/device_search.php';
        include_once plugin_dir_path(__FILE__) . 'includes/trigger_alarm.php';
        

        register_activation_hook( __FILE__, array($this, 'create_activation_table') );

        add_action('wp_enqueue_scripts', array($this, 'enqueue_device_info_scripts'));

       // add_action('wp_enqueue_scripts', array($this, 'enqueue_intl_tel_input_assets'));

          // Add admin menu page
          add_action('admin_menu', array($this, 'register_activated_info_menu_page'));

          add_action('admin_menu', array($this, 'register_found_report_menu_page'));

          add_action('admin_menu', array($this, 'register_lost_report_menu_page'));

          add_action('admin_menu', array($this, 'register_device_info_menu_page'));
    }

         function create_activation_table() {
            global $wpdb;

            $tracker_numbers_table = $wpdb->prefix . 'tracker_numbers';
            $activated_details_table = $wpdb->prefix . 'activated_details';
            $found_report_table = $wpdb->prefix . 'found_report';
            $lost_report_table = $wpdb->prefix . 'lost_report';
            $device_registered_table = $wpdb->prefix . 'device_registered';
            $charset_collate = $wpdb->get_charset_collate();

             // SQL to create tag_numbers table
             $sql_tracker_numbers = "CREATE TABLE $tracker_numbers_table (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                tracker_numbers varchar(255) NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY tracker_numbers (tracker_numbers)
            ) $charset_collate;";
        
           // SQL to create activation table
            $sql = "CREATE TABLE $activated_details_table (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                user_id mediumint(9) NOT NULL,
                username varchar(60) NOT NULL,
                first_name varchar(255) NOT NULL,
                middle_name varchar(255) NOT NULL,
                last_name varchar(255) NOT NULL,
                phone varchar(15) NOT NULL,
                item_name varchar(255) NOT NULL,
                tracker_id varchar(255) NOT NULL,                
                color varchar(255) NOT NULL,
                image_url varchar(255) NOT NULL,
                message varchar(255) NOT NULL,
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY  (id)
            ) $charset_collate;";

           $sql_found_report = "CREATE TABLE $found_report_table (
               id mediumint(9) NOT NULL AUTO_INCREMENT,
               user_id mediumint(9) NOT NULL,
               username varchar(60) NOT NULL,
               first_name varchar(255) NOT NULL,
               middle_name varchar(255) NOT NULL,
               last_name varchar(255) NOT NULL,
               phone varchar(15) NOT NULL,
               tracker_id varchar(255) NOT NULL,
               image_url varchar(255) NOT NULL,
               message varchar(255) NOT NULL,
               timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
               PRIMARY KEY  (id)
            ) $charset_collate;";

            $sql_lost_report = "CREATE TABLE  $lost_report_table (
               id mediumint(9) NOT NULL AUTO_INCREMENT,
               user_id mediumint(9) NOT NULL,
               username varchar(60) NOT NULL,
               first_name varchar(255) NOT NULL,
               middle_name varchar(255) NOT NULL,
               last_name varchar(255) NOT NULL,
               phone varchar(15) NOT NULL,
               item_name varchar(255) NOT NULL,
               tracker_id varchar(255) NOT NULL,
               image_url varchar(255) NOT NULL,
               reporter varchar(255) NOT NULL,
               message varchar(255) NOT NULL,
               timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
               PRIMARY KEY  (id)
              ) $charset_collate;";

            $sql_device_register = "CREATE TABLE $device_registered_table (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                user_id mediumint(9) NOT NULL,
                username varchar(60) NOT NULL,
                first_name varchar(255) NOT NULL,
                middle_name varchar(255) NOT NULL,
                last_name varchar(255) NOT NULL,
                device_name varchar(255) NOT NULL,
                imei varchar(255) NOT NULL,
                serial_number varchar(255) NOT NULL,
                purchase_receipt_url varchar(255) NOT NULL,
                latitude decimal(10, 8) NOT NULL,
                longitude decimal(11, 8) NOT NULL,
                track_time datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset_collate;";


   
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql_tracker_numbers);
            dbDelta($sql);
            dbDelta($sql_found_report);
            dbDelta($sql_lost_report);
            dbDelta($sql_device_register);
           
    
            // Log to check if the function is called
        error_log('Device registration table created.');
        }  
   

    function enqueue_device_info_scripts() {
    // Enqueue jQuery UI and its dependencies
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-widget');
    wp_enqueue_script('jquery-ui-mouse');
    wp_enqueue_script('jquery-ui-slider');
    wp_enqueue_script('jquery-ui-datepicker'); // Adding datepicker as a dependency for colorpicker
    wp_enqueue_script('jquery-ui-colorpicker', 'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js', array('jquery'), '1.12.1', true);
    
    // Enqueue jQuery UI CSS
    wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');

    // Enqueue custom CSS
    wp_enqueue_style('active-device-styles', plugins_url('assets/request-form.css', __FILE__));

    // Enqueue jQuery (if not already enqueued)
    wp_enqueue_script('jquery');

    // Enqueue custom JS
    wp_enqueue_script('activated-device-script', plugins_url('assets/request-form.js', __FILE__), array('jquery'), null, true);

    // Localize the script with the AJAX URL
    wp_localize_script('activated-device-script', 'AjaxformRequest', array('ajax_url' => admin_url('admin-ajax.php')));
 }

      //  require_once 'path/to/vendor/autoload.php';
       // use Twilio\Rest\Client;

    
        function register_activated_info_menu_page() 
        {
            add_menu_page(
                'Activated Details',
                'Activated Details',
                'manage_options',
                'activated-details',
                array($this, 'display_activated_details_admin_page'),
                'dashicons-admin-generic',
                10
            );
        }

    function display_activated_details_admin_page() 
    { 
          // Check if the submenu is active
    $screen = get_current_screen();
    if (strpos($screen->id, 'activated-details') === false || $screen->id !== 'toplevel_page_activated-details') {
        return;
    }

        global $wpdb;
        $table_name = $wpdb->prefix . 'activated_details';
    
        // Handle the search query
        $search_term = isset($_POST['s']) ? sanitize_text_field($_POST['s']) : '';
    
        // query to include a WHERE clause if a search term is provided
        if ($search_term) {
            $results = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM $table_name WHERE first_name LIKE %s OR last_name LIKE %s OR email LIKE %s OR phone LIKE %s OR tracker_id LIKE %s",
                    '%' . $wpdb->esc_like($search_term) . '%',
                    '%' . $wpdb->esc_like($search_term) . '%',
                    '%' . $wpdb->esc_like($search_term) . '%',
                    '%' . $wpdb->esc_like($search_term) . '%',
                    '%' . $wpdb->esc_like($search_term) . '%'
                )
            );
        } else {
            $results = $wpdb->get_results("SELECT * FROM $table_name");
        }
         
    
        ?>
        <div class="wrap">
        <h1 class="wp-heading-inline">Activated Details</h1>
    
            <!-- Export to Excel Button -->
            <form method="post" action="">
                <input type="submit" name="export_excel" class="button button-primary" value="Export to Excel" />
             </form>
            
            <!-- Search Form -->
            <form method="post">
                <p class="search-box">
                    <label class="screen-reader-text" for="search_id-search-input">Search:</label>
                    <input type="search" id="search_id-search-input" name="s" value="<?php echo esc_attr($search_term); ?>" />
                    <input type="submit" id="search-submit" class="button" value="Search" />
                </p>
            </form>
            
            <table class="widefat fixed" cellspacing="0">
                <thead>
                    <tr>
                        <th id="columnname" class="manage-column column-columnname" scope="col">Username</th>
                        <th id="columnname" class="manage-column column-columnname" scope="col">First Name</th>
                        <th id="columnname" class="manage-column column-columnname" scope="col">Middle Name</th>
                        <th id="columnname" class="manage-column column-columnname" scope="col">Last Name</th>
                        <th id="columnname" class="manage-column column-columnname" scope="col">Email</th>
                        <th id="columnname" class="manage-column column-columnname" scope="col">Phone</th>
                        <th id="columnname" class="manage-column column-columnname" scope="col">Item Name</th>
                        <th id="columnname" class="manage-column column-columnname" scope="col">Tag Number</th>                        
                        <th id="columnname" class="manage-column column-columnname" scope="col">Color</th>
                        <th id="columnname" class="manage-column column-columnname" scope="col">Image Upload URL</th>
                        <th id="columnname" class="manage-column column-columnname" scope="col">Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($results) : ?>
                        <?php foreach ($results as $row) : ?>
                            <tr>
                                <td><?php echo esc_html($row->username); ?></td>
                                <td><?php echo esc_html($row->first_name); ?></td>
                                <td><?php echo esc_html($row->middle_name); ?></td>
                                <td><?php echo esc_html($row->last_name); ?></td>
                                <td><?php echo esc_html($row->email); ?></td>
                                <td><?php echo esc_html($row->phone); ?></td>
                                <td><?php echo esc_html($row->item_name); ?></td>
                                <td><?php echo esc_html($row->tracker_id); ?></td>
                                <td><?php echo esc_html($row->color); ?></td>
                                <td><a href="<?php echo esc_url($row->image_url); ?>" target="_blank">View Image</a></td>
                                <td><?php echo esc_html($row->message); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="12">No records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    
          // Handle Export to Excel
          if (isset($_POST['export_excel'])) {
            export_to_excel($results);
        }
           
    }

    function register_found_report_menu_page() {
        add_submenu_page(
            'activated-details',                      // Parent slug
            'Found Reports',                          // Page title
            'Found Reports',                          // Menu title
            'manage_options',                         // Capability
            'found-reports',                          // Menu slug
            array($this, 'display_found_reports_admin_page'),       // Callback function
        );
    }
    
    function display_found_reports_admin_page() {
        global $wpdb;
        $found_report_table = $wpdb->prefix . 'found_report';
        $results = $wpdb->get_results("SELECT * FROM $found_report_table");
    
        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">Found Reports</h1>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Username</th><th>First Name</th><th>Middle Name</th><th>Last Name</th><th>Phone</th><th>Tracker ID</th><th>Image URL</th><th>Message</th></tr></thead>';
        echo '<tbody>';
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row->id) . '</td>';
            echo '<td>' . esc_html($row->username) . '</td>';
            echo '<td>' . esc_html($row->first_name) . '</td>';
            echo '<td>' . esc_html($row->middle_name) . '</td>';
            echo '<td>' . esc_html($row->last_name) . '</td>';
            echo '<td>' . esc_html($row->phone) . '</td>';
            echo '<td>' . esc_html($row->tracker_id) . '</td>';
            echo '<td><a href="' . esc_url($row->image_url) . '" target="_blank">View Image</a></td>';
            echo '<td>' . esc_html($row->message) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    }

    function register_lost_report_menu_page() {
        add_submenu_page(
            'activated-details',                      // Parent slug
            'Lost Reports',                          // Page title
            'Lost Reports',                          // Menu title
            'manage_options',                         // Capability
            'lost-reports',                          // Menu slug
            array($this, 'display_lost_reports_admin_page'),       // Callback function
        );
    }
    
    function display_lost_reports_admin_page() {
        global $wpdb;
        $lost_report_table = $wpdb->prefix . 'lost_report';
        $results = $wpdb->get_results("SELECT * FROM $lost_report_table");
    
        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">Lost Items</h1>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Username</th><th>First Name</th><th>Middle Name</th><th>Last Name</th><th>Phone</th><th>Item Name</th><th>Tracker ID</th><th>Image URL</th><th>Reporter</th><th>Message</th></tr></thead>';
        echo '<tbody>';
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row->id) . '</td>';
            echo '<td>' . esc_html($row->username) . '</td>';
            echo '<td>' . esc_html($row->first_name) . '</td>';
            echo '<td>' . esc_html($row->middle_name) . '</td>';
            echo '<td>' . esc_html($row->last_name) . '</td>';
            echo '<td>' . esc_html($row->phone) . '</td>';
            echo '<td>' . esc_html($row->item_name) . '</td>';
            echo '<td>' . esc_html($row->tracker_id) . '</td>';
            echo '<td><a href="' . esc_url($row->image_url) . '" target="_blank">View Image</a></td>';
            echo '<td>' . esc_html($row->reporter) . '</td>';
            echo '<td>' . esc_html($row->message) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    }


    function register_device_info_menu_page() {
        add_submenu_page(
            'activated-details',
            'Device Registered',
            'Device Registered',
            'manage_options',
            'device-registrations',
            array($this, 'display_device_info_admin_page'),
        );
    }
  
    // Display the device info admin page
    function display_device_info_admin_page() {
        global $wpdb;
        $device_registered_table = $wpdb->prefix . 'device_registered';
    
        $results = $wpdb->get_results("SELECT * FROM $device_registered_table");
    
        ?> 
        <div class="wrap">
            <h1>Device Registrations</h1>
            <table class="widefat fixed" cellspacing="0">
                <thead>
                    <tr>
                        <th id="columnname" class="manage-column column-columnname" scope="col">ID</th>
                        <th id="columnname" class="manage-column column-columnname" scope="col">Username</th>
                        <th id="columnname" class="manage-column column-columnname" scope="col">First Name</th>
                        <th id="columnname" class="manage-column column-columnname" scope="col">Middle Name</th>
                        <th id="columnname" class="manage-column column-columnname" scope="col">Last Name</th>
                        <th id="columnname" class="manage-column column-columnname" scope="col">Device Name</th>
                        <th id="columnname" class="manage-column column-columnname" scope="col">IMEI</th>
                        <th id="columnname" class="manage-column column-columnname" scope="col">Serial Number</th>
                        <th id="columnname" class="manage-column column-columnname" scope="col">Purchase Receipt URL</th>
                        <th id="columnname" class="manage-column column-columnname" scope="col">Latitude</th>
                        <th id="columnname" class="manage-column column-columnname" scope="col">Longitude</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($results) {
                        foreach ($results as $row) {
                            echo '<tr>';
                            echo '<td>' . esc_html($row->id) . '</td>';
                            echo '<td>' . esc_html($row->username) . '</td>';
                            echo '<td>' . esc_html($row->first_name) . '</td>';
                            echo '<td>' . esc_html($row->middle_name) . '</td>';
                            echo '<td>' . esc_html($row->last_name) . '</td>';
                            echo '<td>' . esc_html($row->device_name) . '</td>';
                            echo '<td>' . esc_html($row->imei) . '</td>';
                            echo '<td>' . esc_html($row->serial_number) . '</td>';
                            echo '<td><a href="' . esc_url($row->purchase_receipt_url) . '" target="_blank">View Receipt</a></td>';
                            echo '<td>' . esc_html($row->latitude) . '</td>';
                            echo '<td>' . esc_html($row->longitude) . '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="4">No device registrations found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }  

}
if ( class_exists( 'RequestForm' )) {
    $requestForm = new RequestForm();
}

