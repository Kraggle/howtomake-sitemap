<?php

/**
 * Plugin Name: How to Make - Sitemap
 * Plugin URI: https://howtomakemoneyfromhomeuk.com/
 * Description: This is a plugin that adds a shortcode for a HTML Sitemap with options. 
 * Version: 0.1
 * Author: Kraggle
 * Author URI: https://kragglesites.com/
 **/

define('HTM_L_DIR', plugin_dir_path(__FILE__));
define('HTM_L_URI', plugins_url('', __FILE__));

// Create the core functions, classes and create the tables
require_once(HTM_L_DIR . '/core/functions.php');
require_once(HTM_L_DIR . '/core/create-tables.php');
require_once(HTM_L_DIR . '/core/class.php');

add_action('init', function () {
	HTM_Core_Menu::getInstance();
});
