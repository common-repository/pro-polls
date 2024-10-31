<?php
/**
 * @package Hello_Dolly
 * @version 1.6
 */
/*
Plugin Name: Pro Polls
Plugin URI: http://wordpress.org/extend/plugins/pro-polls-polls
Description: Multi Question Polls.
Author: Amit Joshi
Version: 1.0
Author URI: http://www.samyaksolutions.com
*/ 

global $wpdb;
if (!defined('PRO_POLL_PLUGIN_FOLDER_NAME'))
    define('PRO_POLL_PLUGIN_FOLDER_NAME', trim(dirname(plugin_basename(__FILE__)), '/'));

if (!defined('PRO_POLL_PLUGIN_DIR'))
    define('PRO_POLL_PLUGIN_DIR',WP_PLUGIN_DIR.'/'. PRO_POLL_PLUGIN_FOLDER_NAME);


if (!defined('PRO_POLL_THEME_DIR'))
    define('PRO_POLL_THEME_DIR', PRO_POLL_PLUGIN_DIR."themes");

if (!defined('PRO_POLL_PLUGIN_URL'))
    define('PRO_POLL_PLUGIN_URL', WP_PLUGIN_URL . '/' . PRO_POLL_PLUGIN_FOLDER_NAME);
	
if (!defined('PRO_POLL_RESULT_TABLE'))
    define('PRO_POLL_RESULT_TABLE', $wpdb->prefix."pro_polls_results");	
	
if (!defined('PRO_POLL_POST_TYPE_SLUG'))
    define('PRO_POLL_POST_TYPE_SLUG', "pro-poll");
	
	
	 
global $ppools_db_version;
$ppools_db_version = "1.0";	
	
//	die("gg".MYPLUGIN_PLUGIN_URL);

include( PRO_POLL_PLUGIN_DIR. '/functions.php');
include( PRO_POLL_PLUGIN_DIR. '/pro-polls-admin-html.php');
//Our class extends the WP_List_Table class, so we need to make sure that it's there
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


register_activation_hook( __FILE__, 'pro_polls_install' );

include( PRO_POLL_PLUGIN_DIR. '/classes/classes_list_questions.php');
include( PRO_POLL_PLUGIN_DIR. '/classes/classes_results.php');
include( PRO_POLL_PLUGIN_DIR. '/classes/classes_polls.php');
include( PRO_POLL_PLUGIN_DIR. '/classes/classes_options_page.php');

add_filter('manage_pro-poll_posts_columns', 'pro_polls_columns');

add_action('manage_pro-poll_posts_custom_column',  'pro_polls_show_columns');

//add_filter( "single_template", "pro_polls_get_custom_post_type_template" ) ;
add_action( "admin_menu", "pro_polls_add_admin_menu" ) ;
wp_register_style( "pro_polls_styles", PRO_POLL_PLUGIN_URL."/themes/pro-polls.css", $deps, $ver, $media );


//$wctest = new Ppolls_options();

//createCustomPostType('book','book', 'books' );

add_action( 'init', 'pro_polls_init' );
?>