<?php


namespace Axisubs\CustomPostTypes;

class Axisubs_Plan {


	public function register() {

		add_action('init', function() {

			$labels = [
				'name'                => _x( 'Plans', 'Post Type General Name', 'text_domain' ),
				'singular_name'       => _x( 'Plan', 'Post Type Singular Name', 'text_domain' ),
				'menu_name'           => __( 'Plan', 'text_domain' ),
				'name_admin_bar'      => __( 'Plan', 'text_domain' ),
				'parent_item_colon'   => __( 'Parent Plan:', 'text_domain' ),
				'all_items'           => __( 'All Plan', 'text_domain' ),
				'add_new_item'        => __( 'Add New Plan', 'text_domain' ),
				'add_new'             => __( 'Add Plan', 'text_domain' ),
				'new_item'            => __( 'New Plan', 'text_domain' ),
				'edit_item'           => __( 'Edit Plan', 'text_domain' ),
				'update_item'         => __( 'Update Plan', 'text_domain' ),
				'view_item'           => __( 'View Plan', 'text_domain' ),
				'search_items'        => __( 'Search Plan', 'text_domain' ),
				'not_found'           => __( 'Not found', 'text_domain' ),
				'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
			];
			$rewrite = [
				'slug'                => 'items',
				'with_front'          => true,
				'pages'               => true,
				'feeds'               => true,
			];
			$args = [
				'label'               => __( 'Plans', 'text_domain' ),
				'description'         => __( 'List of Plans', 'text_domain' ),
				'labels'              => $labels,
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => false, //For hide from slidebar
				'show_in_menu'        => true,
				'menu_position'       => 5,
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => true,
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'query_var'           => true,
//				'rewrite'             => $rewrite,
				'rewrite'             => false,
				'capability_type'     => 'page',
				'supports'           => array( 'title', 'editor', 'thumbnail','page-attributes')
			];
			register_post_type( 'Axisubs_Plan', $args );

		}, 0);
	}

}


