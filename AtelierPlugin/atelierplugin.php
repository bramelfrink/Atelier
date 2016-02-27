<?php
/*
Plugin Name: Atelier Plugin
Description: This is our own custom plugin to manage the atelier page.
Version: 1.0
Author: Bram Elfrink & Luuk Godtschalk
*/

add_action('wp_enqueue_scripts', 'atelier_custom_init');

function atelier_custom_init() {
    wp_enqueue_script('atelier_custom_script', plugins_url('/js/atelier_custom_script.js', __FILE__));
}
?>