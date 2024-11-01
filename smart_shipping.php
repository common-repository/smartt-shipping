<?php
/**
 * Plugin Name: SMARTT Shipping
 * Plugin URI: https://wordpress.org/plugins/smartt-shipping
 * Description: To embed SMARTT Shipping Methods for WooCommerce into your website.
 * Version: 1.1.9.1
 * Author: Complete Shipping Solutions
 * Author URI: https://completeshipping.ca/
 * Text Domain: smartt-shipping
 */

if (!defined('ABSPATH'))
    exit;
if (!defined('Smartt_Shipping_Plugin_File')) {
    define('Smartt_Shipping_Plugin_File', __FILE__);
}

define( 'PLUGIN_DIR_URL', plugin_dir_url(__FILE__) );

$smart_shipping_base_mode = '';
$smart_shipping_setting = maybe_unserialize(get_option('woocommerce_smartshipping_settings'));
if (!empty($smart_shipping_setting)) {
    $smart_shipping_base_mode = $smart_shipping_setting['Default_mode'];
}

if ($smart_shipping_base_mode == 'Sandbox') {
    define("smart_shipping_api_url", 'https://api.sandbox.smarttshipping.ca/');
} else {
    define("smart_shipping_api_url", 'https://smarttshipping.ca/');
}

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    deactivate_plugins(plugin_basename(__FILE__));
    unset($_GET['activate']);
    add_action('admin_notices', 'smarttshipping_error_notice', 999);
} else {
    include_once dirname(Smartt_Shipping_Plugin_File) . '/includes/packages-products-list.php';
    include_once dirname(Smartt_Shipping_Plugin_File) . '/includes/countries-info.php';
    include_once dirname(Smartt_Shipping_Plugin_File) . '/includes/functions.php';
    include_once dirname(Smartt_Shipping_Plugin_File) . '/includes/class-smart-shipping.php';
    include_once dirname(Smartt_Shipping_Plugin_File) . '/admin/ajax-functions.php';
    include_once dirname(Smartt_Shipping_Plugin_File) . '/admin/smartt-shipping-fields.php';
}

/* admin notice */
function smarttshipping_error_notice()
{
    ?>
    <div class='notice notice-warning is-dismissible'>
        <p>SMARTT Shipping plugin requires WooCommerce plugin to be activated.</p>
    </div>
    <?php
}

add_filter( 'plugin_action_links', 'sts_settings_plugin_link', 10, 2 );
function sts_settings_plugin_link( $links, $file ) 
{
    if ( $file == plugin_basename(dirname(__FILE__) . '/smart_shipping.php') ) 
    {
        $admin_url = admin_url( 'admin.php?page=wc-settings&tab=shipping&section=smartshipping' );
        $in = '<a href="'.$admin_url.'">Settings</a>';
        array_unshift($links, $in);
    }
    return $links;
}

/* function to enqueue admin scripts & styles */
add_action('admin_enqueue_scripts', 'sts_enqueue_admin_scripts');
function sts_enqueue_admin_scripts()
{
    wp_register_style('sts_datatables_style', 'https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css', array(), time());
    wp_enqueue_style('sts_datatables_style');

    wp_register_script('sts_datatables_script', 'https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js', array('jquery'), null, true);
    wp_enqueue_script('sts_datatables_script');
    wp_register_style('sts_admin_style', plugin_dir_url(__FILE__) . 'admin/assets/css/sts_admin_style.css', array(), time());
    wp_enqueue_style('sts_admin_style');

    wp_register_script('sts_admin_script', plugin_dir_url(__FILE__) . 'admin/assets/js/sts_admin_script.js', array('jquery'), time());
    wp_enqueue_script('sts_admin_script');
    wp_localize_script('sts_admin_script', 'sts_admin_ajax', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('ajax-nonce'), 'smtLoaderUrl' => plugins_url( 'admin/assets/img/loader.gif', __FILE__ )));

}

/* function to enqueue user scripts & styles */
add_action('wp_enqueue_scripts', 'sts_enqueue_frontend_scripts');
function sts_enqueue_frontend_scripts()
{
    wp_enqueue_style('sts_front_style', plugin_dir_url(__FILE__) . 'assets/css/sts_front_style.css', array(), time());
}

/** function to add smartt shipping submenu in wc */
add_action('admin_menu', 'sts_add_submenu_item');
function sts_add_submenu_item()
{
    add_submenu_page('woocommerce', 'SMARTT Shipping Manifest', 'SMARTT Shipping Manifest', 'manage_options', 'smartt-manifest', 'smartt_manifest_page_layout');
    add_submenu_page('woocommerce', 'SMARTT Shipping Warehouse Address', 'SMARTT Shipping Warehouse Address', 'manage_options', 'smartt-warehouse-house', 'smartt_warehouse_address_page_layout');
    add_submenu_page('woocommerce', 'SMARTT Shipping Preferred Carriers', 'SMARTT Shipping Preferred Carriers', 'manage_options', 'smartt-preferred-carriers', 'smartt_preferred_page_layout');
}

/* Smartt Shipping Manifest Callback  */
function smartt_manifest_page_layout()
{
    require_once dirname(Smartt_Shipping_Plugin_File) . '/admin/views/manifest.php';
}

/* Smartt Shipping Warehouse Address Callback */
function smartt_warehouse_address_page_layout()
{
    require_once dirname(Smartt_Shipping_Plugin_File) . '/admin/views/warehouse_address.php';
}

/* Smartt Shipping Preferred Carriers callback */
function smartt_preferred_page_layout()
{
    require_once dirname(Smartt_Shipping_Plugin_File) . '/admin/views/preferred_carriers.php';
}
