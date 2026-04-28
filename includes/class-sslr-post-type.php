<?php
/**
 * Registers the staff-member custom post type and staff-member-group taxonomy.
 *
 * IMPORTANT: Uses the same post type slug ('staff-member') and taxonomy slug
 * ('staff-member-group') as the original Simple Staff List plugin so all
 * existing data is preserved without any migration.
 *
 * @package SSL_Rewrite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SSLR_Post_Type {

	public function init(): void {
		add_action( 'init', [ $this, 'register' ], 10 );
		add_action( 'after_setup_theme', [ $this, 'add_thumbnail_support' ] );
	}

	/**
	 * Register post type and taxonomy.
	 * Called statically from activation hook too.
	 */
	public static function register(): void {
		self::register_post_type();
		self::register_taxonomy();
	}

	private static function register_post_type(): void {
		$labels = [
			'name'                  => _x( 'Staff Members', 'post type general name', 'ssl-rewrite' ),
			'singular_name'         => _x( 'Staff Member', 'post type singular name', 'ssl-rewrite' ),
			'add_new'               => __( 'Add New', 'ssl-rewrite' ),
			'add_new_item'          => __( 'Add New Staff Member', 'ssl-rewrite' ),
			'edit_item'             => __( 'Edit Staff Member', 'ssl-rewrite' ),
			'new_item'              => __( 'New Staff Member', 'ssl-rewrite' ),
			'view_item'             => __( 'View Staff Member', 'ssl-rewrite' ),
			'search_items'          => __( 'Search Staff Members', 'ssl-rewrite' ),
			'not_found'             => __( 'No staff members found', 'ssl-rewrite' ),
			'not_found_in_trash'    => __( 'No staff members found in Trash', 'ssl-rewrite' ),
			'all_items'             => __( 'All Staff Members', 'ssl-rewrite' ),
			'menu_name'             => __( 'Staff Members', 'ssl-rewrite' ),
			'featured_image'        => __( 'Staff Photo', 'ssl-rewrite' ),
			'set_featured_image'    => __( 'Set Staff Photo', 'ssl-rewrite' ),
			'remove_featured_image' => __( 'Remove Staff Photo', 'ssl-rewrite' ),
		];

		register_post_type( 'staff-member', [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'capability_type'    => 'page',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 100,
			'menu_icon'          => 'dashicons-groups',
			'rewrite'            => [
				'slug'       => 'staff-members',
				'with_front' => false,
			],
			'supports'           => [ 'title', 'thumbnail' ],
			'exclude_from_search' => true,
		] );
	}

	private static function register_taxonomy(): void {
		$labels = [
			'name'              => _x( 'Groups', 'taxonomy general name', 'ssl-rewrite' ),
			'singular_name'     => _x( 'Group', 'taxonomy singular name', 'ssl-rewrite' ),
			'search_items'      => __( 'Search Groups', 'ssl-rewrite' ),
			'all_items'         => __( 'All Groups', 'ssl-rewrite' ),
			'parent_item'       => __( 'Parent Group', 'ssl-rewrite' ),
			'parent_item_colon' => __( 'Parent Group:', 'ssl-rewrite' ),
			'edit_item'         => __( 'Edit Group', 'ssl-rewrite' ),
			'update_item'       => __( 'Update Group', 'ssl-rewrite' ),
			'add_new_item'      => __( 'Add New Group', 'ssl-rewrite' ),
			'new_item_name'     => __( 'New Group Name', 'ssl-rewrite' ),
			'menu_name'         => __( 'Groups', 'ssl-rewrite' ),
		];

		register_taxonomy( 'staff-member-group', [ 'staff-member' ], [
			'hierarchical' => true,
			'labels'       => $labels,
			'show_ui'      => true,
			'query_var'    => true,
			'rewrite'      => [ 'slug' => 'staff-group' ],
			'show_in_rest' => true,
		] );
	}

	public function add_thumbnail_support(): void {
		add_theme_support( 'post-thumbnails', [ 'staff-member' ] );
	}
}
