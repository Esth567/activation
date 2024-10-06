<?php

if (!defined('ABSPATH')) {
    exit; 
}

function display_activated_details() {
    if (!is_user_logged_in()) {
        wp_redirect('https://tiiza.com.ng/login'); 
        exit;
    }
    
 ob_start();
 ?>
 <div id="form_success" style="color:#074d2a; font-weight: 500; margin-bottom: 20px;"></div>
 <div id="form_error" style="color:#cb2538; font-weight: 500; margin-bottom: 20px;" ></div>
 
 <div id="form-info-container">
 <form id="form-details" enctype="multipart/form-data">
<?php wp_nonce_field('activation_action', 'activation_nonce'); ?>
  <input type="hidden" name="action" value="handle_activated_details"> <!-- Add action field -->

  <div class="form-group">
    <label for="item-name">Item Name:</label>
    <input type="text" id="item-name" name="item_name" placeholder="Eg. Bag" required>
    </div>

    <div class="form-group"> 
    <label for="tracker-id">Tag Number:</label>
    <input type="text" id="tracker-id" name="tracker_id" placeholder="ABC12345" required>
    </div>

    <div id="color-form">
    <div id="color-box">
    <label for="color">Color:</label>
    <input type="color" id="color" name="color" class="color-picker small-square" required>
    </div>
    
    <label>
        <input type="radio" id= "color_option" name="color_option" value="custom" style="margin-left: 10px;"> Other
    </label>
    
    <input type="text" id="custom_color" name="custom_color" placeholder="Enter your color" style="display: none;">
</div>

   <div id= "form-image">
    <label for="image">Upload Image:</label>
    <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.pdf" required><br>
    </div>
   
    <div id= "form-message">
    <label>Additional Information</label><br />
    <textarea name="message" rows="4" cols="50" placeholder="Type..."></textarea><br /><br />
   </div>

   <div id="btn-sub">
     <button type="submit">Activate Tag</button>
    </div>
 </form>
 <div class="spinner" id="spinner"></div>
</div>
 <?php
 return ob_get_clean();
}

add_shortcode('activated4578', 'display_activated_details');


function handle_activated_details_submission() {
   
    if (!isset($_POST['activation_nonce']) || !wp_verify_nonce($_POST['activation_nonce'], 'activation_action')) {
        wp_send_json_error('failed.');
        return;
    }
    

    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to submit this form.');
        wp_die();
    }

    global $wpdb;
    $tracker_numbers_table = $wpdb->prefix . 'tracker_numbers';
    $activated_details_table = $wpdb->prefix . 'activated_details';

    $item_name = sanitize_text_field($_POST['item_name']);
    $tracker_id = sanitize_text_field($_POST['tracker_id']);
    $color = sanitize_text_field($_POST['color']);
    $message = sanitize_textarea_field($_POST['message']);
    $allowed_file_types = ['jpg', 'jpeg', 'png', 'pdf'];

        // Check if the tracker ID exists in the tracker numbers table
        $tracker_number_exist = $wpdb->get_var($wpdb->prepare("SELECT id FROM $tracker_numbers_table WHERE tracker_numbers = %s", $tracker_id));
    
        if (!$tracker_number_exist) {
            wp_send_json_error('Tag number does not exist.');
        }

        $existing_tracker_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $activated_details_table WHERE tracker_id = %s", $tracker_id));
    
        if ($existing_tracker_id) {
            wp_send_json_error('Tag number already registered.');
        }
        
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image'];
        $file_type = wp_check_filetype($image['name']);
        if (!in_array($file_type['ext'], $allowed_file_types)) {
            wp_send_json_error('Invalid file type. Only JPG, JPEG, PNG, and PDF are allowed.');
        }

        $max_file_size = 5 * 1024 * 1024; // 5 MB
        if ($image['size'] > $max_file_size) {
            wp_send_json_error('File size exceeds the 5MB limit.');
        }

        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['path'] . '/' . $image['name'];
        $image_url = $upload_dir['url'] . '/' . $image['name'];

        if (!move_uploaded_file($image['tmp_name'], $upload_path)) {
            wp_send_json_error('Failed to upload the image.');
        }
    } else {
        wp_send_json_error('No image file uploaded or an error occurred during upload.');
        wp_die();
    }

    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $user_first_name = $current_user->first_name;
    $middle_name = get_user_meta($user_id, 'middle_name', true);
    $user_last_name = $current_user->last_name;

    $phone = get_user_meta($user_id, 'phone', true);

    $activation_data = array(
        'user_id' => $user_id,
        'username' => $current_user->user_login,
        'first_name' => $user_first_name,
        'middle_name' => $middle_name,
        'last_name' => $user_last_name,
        'phone' => $phone,
        'item_name' => $item_name,
        'tracker_id' => $tracker_id,
        'color' => $color,
        'image_url' => $image_url,
        'message' => $message
    );

    $success = $wpdb->insert($activated_details_table, $activation_data);
    if ($success === false) {
        wp_send_json_error('Failed to submit activation details.');
    }

    $user_email = $current_user->user_email;
    $user_first_name = $current_user->user_firstname; 
    $user_last_name = $current_user->user_lastname;

    $subject = 'Tag Activation';
    $body = 'Hello ' . esc_html($user_first_name) . ' ' . esc_html($user_last_name) . ',<br><br>Activation successfully.<br><br>Thank you!';
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    wp_mail($user_email, $subject, $body, $headers);
    
    wp_send_json_success('Item activated successfully.');
    wp_die();
}


add_action('wp_ajax_handle_activated_details', 'handle_activated_details_submission');
add_action('wp_ajax_nopriv_handle_activated_details', 'handle_activated_details_submission');