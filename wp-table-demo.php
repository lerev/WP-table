<?php defined('ABSPATH') || exit;
/*
Plugin Name: Garnet Debtors Table
Description: Debtors Table
Version: 1.0.0
Author: Webmove
Author URI: //webmove.dev
*/

define('PLG_TABLE__PATH', plugin_dir_path(__FILE__));
define('PLG_TABLE__URL', plugin_dir_url(__FILE__));

include_once __DIR__.'/includes/helpers.php';
include_once __DIR__.'/app/controller/data.php';
include_once __DIR__.'/app/view/index.php';
include_once __DIR__.'/includes/validate.php';

/** Flahs init */
Plg_Table_Helpers::flashInit();

add_action('plugins_loaded', function () {
    if (is_admin()) {
        if ( ! class_exists('WP_List_Table')) {
            require_once ABSPATH.'wp-admin/includes/class-wp-list-table.php';
        }
        add_action('admin_menu', function () {
            $page_title     = __('Debtors table', 'garnet');
            $menu_title     = __('Debtors', 'garnet');
            $ControllerData = new Plg_Table_Controller_Data();
            $hook           = add_menu_page(
                $page_title,
                $menu_title,
                'manage_options',
                'garnet-table',
                array($ControllerData, 'view'),
                'dashicons-media-spreadsheet',
                6
            );
            add_action('load-'.$hook, array($ControllerData, 'action'));

        }, 10);
    }
});

register_activation_hook(__FILE__, 'plg_table_activation');
function plg_table_activation()
{
    global $wpdb;

    require_once ABSPATH.'wp-admin/includes/upgrade.php';

    dbDelta("CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."debtors` (
		`id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
		`name` varchar(50) DEFAULT NULL,
        `phone` int DEFAULT NULL,
        `email` varchar(80) DEFAULT NULL,
        `country` varchar(30) DEFAULT NULL,
        `company` varchar(70) DEFAULT NULL,
        `contract_id` int DEFAULT NULL,
        `payment_sum` int DEFAULT NULL,
        `currency` varchar(20) DEFAULT NULL,
        `token` varchar(80) DEFAULT NULL,
        `link` varchar(100) DEFAULT NULL,
        `status` int DEFAULT NULL,
        `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
	) {$wpdb -> get_charset_collate()};");

    return true;
}

//register_uninstall_hook(__FILE__, 'plg_table_uninstall');
//function plg_table_uninstall()
//{
//    global $wpdb;
//
//    $wpdb->query("DROP TABLE IF EXISTS `".$wpdb->prefix."debtors`");
//
//    return true;
//}