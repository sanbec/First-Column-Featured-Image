<?php

/**
 * Main plugin class
 *
 * @package   FirstColumnFeaturedImage 
 * @author    Santiago Becerra <santi@wpcombo.com>
 * @license   GPL-3.0+
 * @link      https://wpcombo.com
 * @copyright 2019 WPCombo OÜ
 */

class FirstColumnFeaturedImage {

	public function run() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'admin_init', array( $this, 'add_post_type_column' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'featured_image_column_width' ) );

/**
 * Removing featured image column from ajde_events
 * The ajde_events are events from eventON plugins.
 * They have their own featured image in the title column
 */
		add_filter( 'firstcolumnfeaturedimage_post_types', 'remove_events' );
		function remove_events( $post_types ) {
			unset( $post_types['ajde_events'] );
			return $post_types;
		}

	}

	/**
	 * Set up text domain for translations
	 *
	 * @since 1.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'first-column-featured-image', false, plugin_dir_path( __FILE__ ) . '/languages/' );
	}

	public function add_post_type_column() {
		$args       = array(
			'_builtin' => false,
			'show_ui'  => true,
		);
		$output     = 'names';
		$post_types = get_post_types( $args, $output );
		$post_types['post'] = 'post';
		$post_types['page'] = 'page';
		if ( class_exists( 'WooCommerce' ) ) {
			unset( $post_types['product'] );
		}
		$post_types = apply_filters( 'firstcolumnfeaturedimage_post_types', $post_types, $args );
		foreach ( $post_types as $post_type ) {
			if ( ! post_type_supports( $post_type, 'thumbnail' ) ) {
				continue;
			}
			add_filter( "manage_{$post_type}_posts_columns", array( $this, 'add_featured_image_column' ) );
			add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'manage_image_column' ), 10, 2 );
			add_filter( "manage_edit-{$post_type}_sortable_columns", array( $this, 'make_sortable' ) );
			add_action( 'pre_get_posts', array( $this, 'orderby' ) );
		}
	}
	/**
	 * add featured image column
	 * @param array $columns set up new column to show featured image for taxonomies/posts/etc.
	 *
	 * @return array
	 * @since 1.0
	 */
	public function add_featured_image_column( $columns ) {

		$first_column = array('featured_image' => __( 'Image', 'first-column-featured-image' ));

		return array_merge( $first_column, $columns );

	}

	/**
	 * Make the featured image column sortable.
	 * @param $columns
	 * @return mixed
	 * @since 1.0
	 */
	public function make_sortable( $columns ) {
		$columns['featured_image'] = 'featured_image';
		return $columns;
	}

	/**
	 * Set a custom query to handle sorting by featured image
	 * @param $query WP_Query
	 * @since 1.0
	 */
	public function orderby( $query ) {
		if ( ! is_admin() ) {
			return;
		}

		$orderby = $query->get( 'orderby' );
		if ( 'featured_image' === $orderby ) {
			$query->set(
				'meta_query', array(
					'relation' => 'OR',
					array(
						'key'     => '_thumbnail_id',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => '_thumbnail_id',
						'compare' => 'EXISTS',
					),
				)
			);
			$post_type       = $query->get( 'post_type' );
			$secondary_order = is_post_type_hierarchical( $post_type ) ? 'title' : 'date';
			$query->set( 'orderby', "meta_value_num $secondary_order" );
		}
	}

	/**
	 * manage new post_type column
	 * @param  $column string $column  column id is featured_image
	 * @param  $post_id int id of each post
	 *
	 * @since 1.0
	 */
	public function manage_image_column( $column, $post_id ) {

		if ( 'featured_image' !== $column ) {
			return;
		}
		$image_id = get_post_thumbnail_id( $post_id );
		if ( ! $image_id ) {
			printf( '<img src="%1$s" alt="%2$s" title="%2$s" />', plugins_url( '/assets/sin-imagen.svg', __FILE__ ), esc_html__( 'No image', 'first-column-featured-image' ) );
			printf( '<span class="screen-reader-text">%s</span>', esc_html__( 'No image', 'first-column-featured-image' ) );
			return;
		}

		$args = array(
			'image_id' => $image_id,
			'context'  => 'post',
			'alt'      => the_title_attribute( 'echo=0' ),
		);

		echo wp_kses_post( $this->admin_column_image( $args ) );
	}

	/**
	 * Generic function to return featured image
	 * @param $args array of values to pass to function ( image_id, context, alt_tag )
	 *
	 * @return string
	 * @since 1.0
	 */
	protected function admin_column_image( $args ) {
		$image_id = $args['image_id'];
		$preview  = wp_get_attachment_image_src( $image_id, 'thumbnail' );
		$preview  = apply_filters( 'firstcolumnfeaturedimage_thumbnail', $preview, $image_id );
		if ( ! $preview ) {
			return '';
		}
		return sprintf( '<img src="%1$s" alt="%2$s" />', $preview[0], $args['alt'] );
	}

	/**
	 * Creates an inline stylesheet to set featured image column width
	 */
	public function featured_image_column_width() {
		$screen = get_current_screen();
		if ( ! post_type_supports( $screen->post_type, 'thumbnail' ) ) {
			return;
		}
		if ( in_array( $screen->base, array( 'edit' ), true ) ) { ?>
			<style type="text/css">
				.column-featured_image { width: 85px; }
				.column-featured_image img { margin: 0 auto; height: auto; width: auto; max-width: 70px; max-height: 70px; border-radius: 50%; border: 3px solid transparent;}
				.column-featured_image img:hover { border-color: blue;}
				@media screen and (max-width: 782px) { .column-featured_image, .wp-list-table .is-expanded td.column-featured_image:not(.hidden) { display: table-cell !important; width: 52px; } .column-featured_image.hidden { display: none !important; } .column-featured_image img { margin: 0; max-width: 42px; } }
			</style> <?php
		}
	}
}
