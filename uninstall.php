<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Delete options from the database
delete_option('woocommerce_smartshipping_settings');
delete_option('woocommerce_smartshipping_api_data');


// Array of meta keys 
$meta_keys = array('sm_city_id', 'sm_state_id', 'sm_country_id', 'sm_postalCode', 'sm_shipper_from', 'sm_street', 'sm_customerid', 'sm_email', 'sm_phone', 'ShipmentId', '_select-mcountry','_select-mstate', '_select-package', '_select', '_is_stackable', '_is_dangerous', 'sm_default_address', 'sm_country', 'sm_city', 'sm_state', 'Bol_Path', 'shipping_date', 'Dispatch_created', 'Tracking_Number', 'Smartt_BL_Number', 'Carrier_Name', 'PickUp_Number', 'Tracking_Url', 'current_shipping_price', 'ShipmentGuid');

foreach ($meta_keys as $meta_key) {
    // Get all posts that have the specified meta key
    $postmeta_table = $wpdb->prefix . "postmeta";
    $post_ids = $wpdb->get_results("SELECT post_id FROM $postmeta_table WHERE meta_key = '$meta_key'");

    foreach ($post_ids as $post_id) {
        delete_post_meta($post_id->post_id, $meta_key);
    }
}