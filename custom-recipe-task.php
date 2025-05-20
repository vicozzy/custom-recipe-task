<?php
/*
Plugin Name: Custom Recipe Task
Description: Displays recipes with filtering support and Gutenberg integration.
Version: 1.0
Author: Vitor Teixeira
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Enqueue scripts and styles
function recipe_enqueue_scripts() {
    wp_enqueue_style('recipe-style', plugins_url('/templates/single-recipe.css', __FILE__));
    #wp_enqueue_style( 'load-fa', 'https://use.fontawesome.com/releases/v5.5.0/css/all.css' );

    wp_enqueue_script('recipe-ajax-filter', plugins_url('/includes/ajax-handler.js', __FILE__), array('jquery'), false, true);
    wp_localize_script('recipe-ajax-filter', 'recipe_ajax', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'recipe_enqueue_scripts');

// Add template for post-type 'recipe'
add_filter( 'template_include', 'insert_recipe_template' );
function insert_recipe_template( $template )
{
    if ( 'recipe' === get_post_type() )
        return dirname( __FILE__ ) . '/templates/single-recipe.php';

    return $template;
}

// Register Gutenberg block
require_once plugin_dir_path(__FILE__) . 'build/index.php';

// Render Callback for block
require_once plugin_dir_path(__FILE__) . 'includes/render_callback.php';

// AJAX Handler
require_once plugin_dir_path(__FILE__) . 'includes/ajax-handler.php';

// Admin Page for Importing Recipes
require_once plugin_dir_path(__FILE__) . 'admin/admin-page.php';
require_once plugin_dir_path(__FILE__) . 'admin/settings-page.php';

// API Importer Logic
require_once plugin_dir_path(__FILE__) . 'includes/api-importer.php';