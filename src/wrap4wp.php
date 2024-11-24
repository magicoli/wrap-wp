<?php
/**
* Plugin Name: W.R.A.P.
* Plugin URI: https://git.magiiic.com/wordpress/plugins/wrap4wp
* Description: This is the very first plugin I ever created.
* Version: 1.0-dev
* Author: Olivier van Helden
* Author URI: https://magiiic.media
**/

function wrap_register_casting() {

	/**
	 * Post Type: Castings.
	 */

	$labels = array(
		"name" => __( "Castings", "the7mk2" ),
		"singular_name" => __( "casting", "the7mk2" ),
		"parent_item_colon" => __( "Casting parent :", "the7mk2" ),
		"name_admin_bar" => __( "Casting", "the7mk2" ),
		"parent_item_colon" => __( "Casting parent :", "the7mk2" ),
	);

	$args = array(
		"label" => __( "Castings", "the7mk2" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"delete_with_user" => false,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"exclude_from_search" => false,
		"capability_type" => array( "casting", "shooting" ),
		"map_meta_cap" => true,
		"hierarchical" => true,
		"rewrite" => array( "slug" => "casting", "with_front" => false ),
		"query_var" => true,
		"menu_position" => 4,
		"menu_icon" => "dashicons-format-video",
		"supports" => array( "title", "author", "page-attributes", "media" ),
	);

	register_post_type( "casting", $args );
}

add_action( 'init', 'wrap_register_casting' );

/*
* Creating a function to create our CPT
*/

function wrap_register_shooting() {

// Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x( 'Shootings', 'Post Type General Name', 'the7mk2' ),
        'singular_name'       => _x( 'Shooting', 'Post Type Singular Name', 'the7mk2' ),
        'menu_name'           => __( 'Shootings', 'the7mk2' ),
        'parent_item_colon'   => __( 'Parent Shooting', 'the7mk2' ),
        'all_items'           => __( 'All Shootings', 'the7mk2' ),
        'view_item'           => __( 'View Shooting', 'the7mk2' ),
        'add_new_item'        => __( 'Add New Shooting', 'the7mk2' ),
        'add_new'             => __( 'Add New', 'the7mk2' ),
        'edit_item'           => __( 'Edit Shooting', 'the7mk2' ),
        'update_item'         => __( 'Update Shooting', 'the7mk2' ),
        'search_items'        => __( 'Search Shooting', 'the7mk2' ),
        'not_found'           => __( 'Not Found', 'the7mk2' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'the7mk2' ),
    );

// Set other options for Custom Post Type

    $args = array(
        'label'               => __( 'shootings', 'the7mk2' ),
        'description'         => __( 'Shooting project name', 'the7mk2' ),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
        'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
        // You can associate this CPT with a taxonomy or custom taxonomy.
        'taxonomies'          => array( 'genres' ),
        /* A hierarchical CPT is like Pages and can have
        * Parent and child items. A non-hierarchical CPT
        * is like Posts.
        */
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'shooting',
    );

    // Registering your Custom Post Type
    register_post_type( 'shootings', $args );

}

/* Hook into the 'init' action so that the function
* Containing our post type registration is not
* unnecessarily executed.
*/

add_action( 'init', 'wrap_register_shooting', 0 );
