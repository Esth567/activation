<?php

if (!defined('ABSPATH')) {
    exit; 
}


function device_info_form() {
    if (!is_user_logged_in()) {
        wp_redirect('login'); 
        exit;
    }
    

    ob_start();
    ?>
    <div id="form-success" style="color:#074d2a; font-weight: 500;"></div>
    <div id="form-failed" style="color:#cb2538; font-weight: 500;"></div>
    <form id="device-registration-form" enctype="multipart/form-data">
        <h5>Fill the form below</h5>
        <?php wp_nonce_field('device_registration_action', 'device_registration_nonce'); ?>
        <input type="hidden" name="action" value="handle_device_info_form"> <!-- Add action field -->
        <label for="device-name">Device Name:</label>
        <input type="text" id="device-name" name="device_name" required><br>

        <label for="imei">IMEI Number:</label>
        <input type="text" id="imei" name="imei" required><br>

        <label for="serial-number">Serial Number:</label>
        <input type="text" id="serial-number" name="serial_number" required><br>

        <label for="purchase-receipt">Purchase Receipt:</label>
        <input type="file" id="purchase-receipt" name="purchase_receipt" accept=".jpg,.jpeg,.png,.pdf" required><br>

        <input type="hidden" id="latitude" name="latitude" value="">
        <input type="hidden" id="longitude" name="longitude" value="">

         <div class="btn-sub">
           <button type="submit">Submit Report</button>
         </div>
    </form>
    <?php
    return ob_get_clean();
}

// Register the shortcode
function register_device_form_shortcode() {
    add_shortcode('device_form045', 'device_info_form');
}

// Hook the function to 'init' to ensure it runs when WordPress initializes
add_action('init', 'register_device_form_shortcode');


function handle_device_info_form_submission() {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_send_json_error('Invalid request method.');
        return;
    }

    if (!isset($_POST['device_registration_nonce']) || !wp_verify_nonce($_POST['device_registration_nonce'], 'device_registration_action')) {
        wp_send_json_error('Nonce verification failed.');
        return;
    }
    
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to submit this form.');
        wp_die();
    }

    global $wpdb;
    $device_registered_table = $wpdb->prefix . 'device_registered';

    $device_name = sanitize_text_field($_POST['device_name']);
    $imei = sanitize_text_field($_POST['imei']);
    $serial_number = sanitize_text_field($_POST['serial_number']);
    $latitude = sanitize_text_field($_POST['latitude']);
    $longitude = sanitize_text_field($_POST['longitude']);
    $purchase_receipt = $_FILES['purchase_receipt'];
   

    // Validate IMEI and serial number formats
    if (!preg_match('/^\d{15}$/', $imei)) {
        wp_send_json_error('Invalid IMEI number.');
        wp_die();
    }

    if (!preg_match('/^[a-zA-Z0-9]+$/', $serial_number)) {
        wp_send_json_error('Invalid serial number.');
        wp_die();
    }

  
    $allowed_file_types = ['jpg', 'jpeg', 'png', 'pdf'];
    $max_file_size = 2 * 1024 * 1024; // 2MB
    $purchase_receipt_url = '';

    if ($purchase_receipt && $purchase_receipt['error'] === UPLOAD_ERR_OK) {
        $file_type = wp_check_filetype($purchase_receipt['name']);
        if (!in_array($file_type['ext'], $allowed_file_types)) {
            wp_send_json_error('Invalid file type. Only JPG, JPEG, PNG, and PDF are allowed.');
        }

        if ($purchase_receipt['size'] > $max_file_size) {
            wp_send_json_error('File size exceeds the limit of 2MB.');
        }

        $upload_overrides = ['test_form' => false];
        $uploaded_file = wp_handle_upload($purchase_receipt, $upload_overrides);

        if ($uploaded_file && !isset($uploaded_file['error'])) {
            $purchase_receipt_url = esc_url_raw($uploaded_file['url']);
        } else {
            wp_send_json_error('File upload error: ' . $uploaded_file['error']);
        }
    }


    $existing_imei = $wpdb->get_var($wpdb->prepare("SELECT id FROM $device_registered_table WHERE imei = %s", $imei));
    if ($existing_imei) {
        wp_send_json_error('IMEI number already registered.');
        wp_die();
    }

    $existing_serial_number = $wpdb->get_var($wpdb->prepare("SELECT id FROM $device_registered_table WHERE serial_number = %s", $serial_number));
    if ($existing_serial_number) {
        wp_send_json_error('Serial number already registered.');
        wp_die();
    }

    $user_id = get_current_user_id();
    $current_user = wp_get_current_user();
    $username = sanitize_text_field($current_user->user_login);
    $first_name = sanitize_text_field($current_user->first_name);
    $middle_name = sanitize_text_field(get_user_meta($user_id, 'middle_name', true));
    $last_name = sanitize_text_field($current_user->last_name);

    // Check if the device is already registered to update its location
    $device_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $device_registered_table WHERE imei = %s", $imei));
    
    if ($device_id) {
        // Update the current location in the database
        $wpdb->update(
            $device_registered_table,
            array(
                'latitude' => $latitude,
                'longitude' => $longitude,
                'track_time' => current_time('mysql')
            ),
            array('id' => $device_id),
            array('%f', '%f', '%s'),
            array('%d')
        );
    } else {
        // Insert the new device registration data
        $wpdb->insert(
            $device_registered_table,
            array(
                'user_id' => $user_id,
                'username' => $username,
                'first_name' => $first_name,
                'middle_name' => $middle_name,
                'last_name' => $last_name,
                'device_name' => $device_name,
                'imei' => $imei,
                'serial_number' => $serial_number,
                'purchase_receipt_url' => $purchase_receipt_url,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'track_time' => current_time('mysql')
            )
        );
    }

    if ($wpdb->last_error) {
        wp_send_json_error('Database error: ' . $wpdb->last_error);
    } else {
        $user_email = $current_user->user_email;
        $user_name = $current_user->first_name . ' ' . $current_user->last_name;
        $subject = 'Device Registration Confirmation';
        $body = 'Hello,<br><br>Your device has been registered successfully.<br><br>Thank you!';
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        wp_mail($user_email, $user_name, $subject, $body, $headers);

        wp_send_json_success('Device registered successfully.');
    }

    wp_die();
}
add_action('wp_ajax_handle_device_info_form', 'handle_device_info_form_submission');
add_action('wp_ajax_nopriv_handle_device_info_form', 'handle_device_info_form_submission');
