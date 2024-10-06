<?php

if (!defined('ABSPATH')) {
    exit; 
}


function lost_report_form() {
    if (!is_user_logged_in()) {
        wp_redirect('https://tiiza.com.ng/login'); 
        exit;
    }
    
    ob_start();
    ?>
    <div id="form_success" style="color:#074d2a; font-weight: 500; margin-bottom: 20px;"></div>
    <div id="form_error" style="color:#cb2538; font-weight: 500; margin-bottom: 20px;" ></div>
    
    <div id="form-info-container">
    <form id="lost_report_form" enctype="multipart/form-data">
    <?php wp_nonce_field('lost_report_action', 'lost_report_nonce'); ?>
    <input type="hidden" name="action" value="handle_lost_report">
    <h3 style="padding: 10px; color: #344989;">Report all your lost items​</h3>
    <div class="form-group">
    <label for="item-name">Item Name:</label>
    <input type="text" id="item-name" name="item_name" placeholder="Eg. Bag" required>
    </div>

    <div class="form-group"> 
    <label for="tracker-id">Tag Number:</label>
    <input type="text" id="tracker-id" name="tracker_id" placeholder="ABC12345" required>
    </div>

   <div id= "form-image">
    <label for="image">Upload Image:</label>
    <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.pdf" ><br>
    </div>

    <div id="form-report">
    <label for="reporter">Reporting:</label>
    <input type="radio" name="reporter" value="self" required> Self
    <input type="radio" name="reporter" value="third_party" required> Third_Party
    </div>
   
    <div id= "form-message">
    <label>Additional Information</label><br />
    <textarea name="message" rows="4" cols="50" placeholder="Type..."></textarea><br /><br />
   </div>

    <div id="btn-sub">
        <button type="submit">Submit Report</button>
    </div>
    </form>
    <div class="spinner" id="spinner"></div>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('lost_item_form234', 'lost_report_form');

add_action('wp_ajax_handle_lost_report', 'handle_lost_report_submission');
add_action('wp_ajax_nopriv_handle_lost_report', 'handle_lost_report_submission');

function handle_lost_report_submission() {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_send_json_error('Invalid request method.');
        return;
    }

    if (!isset($_POST['lost_report_nonce']) || !wp_verify_nonce($_POST['lost_report_nonce'], 'lost_report_action')) {
        wp_send_json_error('failed.');
        return;
    }

    // Check if the user is logged in
    if (!is_user_logged_in()) {
        echo json_encode(array('error' => 'You must be logged in to submit this form.'));
        wp_die();
    }

    global $wpdb;
    $lost_report_table = $wpdb->prefix . 'lost_report';

    // Sanitize and validate form input
    $item_name = sanitize_text_field($_POST['item_name']);
    $tracker_id = sanitize_text_field($_POST['tracker_id']);
    $reporter = sanitize_text_field($_POST['reporter']);
    $message = sanitize_textarea_field($_POST['message']);

      // Check if the tracker ID exists in the tracker numbers table
        $tracker_number_exist = $wpdb->get_var($wpdb->prepare("SELECT id FROM $tracker_numbers_table WHERE tracker_numbers = %s", $tracker_id));
    
        if (!$tracker_number_exist) {
            wp_send_json_error('Tag number does not exist.');
        }

    // Validate and handle file upload
    $image = $_FILES['image'];
    $allowed_file_types = ['jpg', 'jpeg', 'png', 'pdf'];
    $max_file_size = 2 * 1024 * 1024; // 2MB
    $image_url = '';

    if ($image && $image['error'] === UPLOAD_ERR_OK) {
        $file_type = wp_check_filetype($image['name']);
        if (!in_array($file_type['ext'], $allowed_file_types)) {
            wp_send_json_error('Invalid file type. Only JPG, JPEG, PNG, and PDF are allowed.');
        }

        if ($image['size'] > $max_file_size) {
            wp_send_json_error('File size exceeds the limit of 2MB.');
        }

        $upload_overrides = ['test_form' => false];
        $uploaded_file = wp_handle_upload($image, $upload_overrides);

        if ($uploaded_file && !isset($uploaded_file['error'])) {
            $image_url = esc_url_raw($uploaded_file['url']);
        } else {
            wp_send_json_error('File upload error: ' . $uploaded_file['error']);
        }
    }

    // Get current user data
    $user_id = get_current_user_id();
    $current_user = wp_get_current_user();
    $username = sanitize_text_field($current_user->user_login);
    $first_name = sanitize_text_field($current_user->first_name);
    $middle_name = sanitize_text_field(get_user_meta($user_id, 'middle_name', true));
    $last_name = sanitize_text_field($current_user->last_name);
    $phone = sanitize_text_field(get_user_meta($user_id, 'phone', true));

    // Insert data into database using prepared statement
    $insert_data = $wpdb->prepare(
        "INSERT INTO $lost_report_table (user_id, username, first_name, middle_name, last_name, phone, item_name, tracker_id, image_url, reporter, message) 
        VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
        $user_id, $username, $first_name, $middle_name, $last_name, $phone, $item_name, $tracker_id, $image_url, $reporter, $message
    );

    if ($wpdb->query($insert_data) === false) {
        error_log('Database Insert Error: ' . $wpdb->last_error);
        echo json_encode(array('error' => 'Database insert error: ' . $wpdb->last_error));
    } else {
        // Send email notification
        $user_email = sanitize_email($current_user->user_email);
        $user_first_name = $current_user->user_firstname; 
        $user_last_name = $current_user->user_lastname;

        $subject = 'Form Submission Confirmation';
         $body = 'Hello ' . esc_html($user_first_name) . ' ' . esc_html($user_last_name) . ',<br><br>Your report has been submitted successfully.<br><br>Thank you!';
        $headers = ['Content-Type: text/html; charset=UTF-8'];

         wp_mail($user_email, $user_name, $subject, $body, $headers);
        wp_send_json_success('Report submitted successfully.');
    }
    wp_die();
}
