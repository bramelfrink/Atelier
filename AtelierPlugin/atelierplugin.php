<?php
/*
Plugin Name: Atelier Plugin
Description: This is our own custom plugin to manage the atelier page.
Version: 1.0
Author: Bram Elfrink & Luuk Godtschalk
*/

global $atelier_version;
$atelier_version = '1.0';

function atelier_install() {
	global $wpdb;
	global $atelier_version;
	
	$charset_collate = $wpdb->get_charset_collate();
	$queries = Array();
	
	$students_table_name = $wpdb->prefix . 'students';
	if( $wpdb->get_var( "SHOW TABLES LIKE '$db_table_name'" ) != $students_table_name ) {
		$queries[] = "CREATE TABLE $students_table_name (
			student_id mediumint(9) NOT NULL AUTO_INCREMENT,
			first_name tinytext NOT NULL,
			last_name tinytext NOT NULL,
			PRIMARY KEY (student_id)
		) $charset_collate;";
	}
	
	print(implode($queries));
	#die();
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $queries );

	add_option( 'atelier_version', $atelier_version );
}
register_activation_hook( __FILE__, 'atelier_install' );


add_action('wp_enqueue_scripts', 'atelier_custom_init');

add_action( 'wp_enqueue_scripts', 'register_jquery' );

add_action( 'wp_enqueue_scripts', 'register_angular' );

add_action( 'wp_enqueue_scripts', 'register_routes' );

function atelier_custom_init() {
    wp_enqueue_script('atelier_custom_script', plugins_url('/js/atelier_custom_script.js', __FILE__));
}

function register_jquery() {
    if (!is_admin() && $GLOBALS['pagenow'] != 'wp-login.php') {
        // comment out the next two lines to load the local copy of jQuery
        wp_deregister_script('jquery');
        wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js', false, '1.12.4');
        wp_enqueue_script('jquery');
    }
}

function register_angular() {
    if (!is_admin() && $GLOBALS['pagenow'] != 'wp-login.php') {
        // comment out the next two lines to load the local copy of jQuery
        wp_deregister_script('angular');
        wp_register_script('angular', 'https://ajax.googleapis.com/ajax/libs/angularjs/1.5.7/angular.min.js', false, '1.5.7');
        wp_enqueue_script('angular');
    }
}

function register_angular() {
    if (!is_admin() && $GLOBALS['pagenow'] != 'wp-login.php') {
        // comment out the next two lines to load the local copy of jQuery
        wp_deregister_script('routes');
        wp_register_script('routes', 'http://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular-route.js', false, '1.4.8');
        wp_enqueue_script('angular');
    }
}

function atelier_api_init( $server ) {
	global $atelier_api_student;
	
	if ( ! class_exists( 'WP_REST_Student_Controller' ) ) {
		require_once dirname( __FILE__ ) . '/endpoints/student_controller.php';
	}
	$atelier_api_student = new WP_REST_Student_Controller( $server );
	$atelier_api_student->register_routes();
}
add_action( 'rest_api_init', 'atelier_api_init' );

?>