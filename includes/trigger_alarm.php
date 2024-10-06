<?php

if (!defined('ABSPATH')) {
    exit; 
}

function trigger_alarm_message() {
    ob_start();
   include 'template/alarm_message.php';
    return ob_get_clean();
}

add_shortcode('alarm20543', 'trigger_alarm_message');

function trigger_alarm_handler() {

    
}
add_action('wp_ajax_trigger_alarm_handler', 'trigger_alarm_handler');
