<?php

if( !defined('ABSPATH') )
{
  die('you cannot be here');
}

add_shortcode('mobile_tracking', 'mobile_tracking_search');

function mobile_tracking_search() {
    if (!is_user_logged_in()) {
        // Redirect to the custom login page if the user is not logged in
        wp_redirect('https://tiiza.com.ng/login'); 
        exit;
    }
    
    ob_start();
    ?>
   <form id="device-search-form">
    <div class="search-dev">
        <label for="device-id">Search by IMEI or Serial Number.</label>
        <input type="text" id="device-id" name="device_id" required>       
        </div>
        <input type="submit" value="Search">
    </form>
    <div id="search-result"></div>
    <div id="map" style="height: 400px; width: 100%;"></div>
</form>
<div id="search-result"></div>

    <?php
    return ob_get_clean();
}


function handle_device_search() {
     // Check if the user is logged in
     if (!is_user_logged_in()) {
        echo json_encode(array('error' => 'You must be logged in to submit this form.'));
        wp_die();
    }
    
    global $wpdb;
    $device_registered_table = $wpdb->prefix . 'device_registered';

    
    if (!isset($_POST['device_id'])) {
        echo json_encode(array(
            'status' => 'error',
            'message' => 'Invalid request'
        ));
        wp_die();
    }

    $device_id = sanitize_text_field($_POST['device_id']);

    $result = $wpdb->get_row($wpdb->prepare(
        "SELECT imei, serial_number, latitude, longitude, track_time FROM $device_registered_table WHERE imei = %s OR serial_number = %s",
        $device_id,
        $device_id
    ));

    if ($result) {
        echo json_encode(array(
            'status' => 'success',
            'data' => $result
        ));
    } else {
        echo json_encode(array(
            'status' => 'error',
            'message' => 'Device not found'
        ));
    }

    wp_die();
}
add_action('wp_ajax_device_search', 'handle_device_search');
add_action('wp_ajax_nopriv_device_search', 'handle_device_search');
