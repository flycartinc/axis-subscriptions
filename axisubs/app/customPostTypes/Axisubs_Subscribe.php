<?php


namespace Axisubs\CustomPostTypes;

class Axisubs_Subscribe {


	public function register() {

		add_action('init', function() {

			$labels = [
				'name'                => _x( 'Subscribes', 'Subscribe Type General Name', 'text_domain' ),
				'singular_name'       => _x( 'Subscribe', 'Subscribe Type Singular Name', 'text_domain' ),
				'menu_name'           => __( 'Subscribe', 'text_domain' ),
				'name_admin_bar'      => __( 'Subscribe', 'text_domain' ),
				'parent_item_colon'   => __( 'Parent Plan:', 'text_domain' ),
				'all_items'           => __( 'All Subscribe', 'text_domain' ),
				'add_new_item'        => __( 'Add New Subscribe', 'text_domain' ),
				'add_new'             => __( 'Add Subscribe', 'text_domain' ),
				'new_item'            => __( 'New Subscribe', 'text_domain' ),
				'edit_item'           => __( 'Edit Subscribe', 'text_domain' ),
				'update_item'         => __( 'Update Subscribe', 'text_domain' ),
				'view_item'           => __( 'View Subscribe', 'text_domain' ),
				'search_items'        => __( 'Search Subscribe', 'text_domain' ),
				'not_found'           => __( 'Not found', 'text_domain' ),
				'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
			];
			$rewrite = [
				'slug'                => 'axisubs',
				'with_front'          => true,
				'pages'               => true,
				'feeds'               => true,
			];
			$args = [
				'label'               => __( 'Subscribes', 'text_domain' ),
				'description'         => __( 'List of Subscribes', 'text_domain' ),
				'labels'              => $labels,
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => false, // For show in slidebar
				'show_in_menu'        => true,
				'menu_position'       => 5,
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => true,
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'query_var'           => true,
				'rewrite'             => $rewrite,
//				'rewrite'             => true,
				'capability_type'     => 'page',
				'supports'           => array( 'title', 'editor', 'thumbnail','page-attributes')
			];
				register_post_type( 'Axisubs_Subscribes', $args );

		}, 0);
	}

}


