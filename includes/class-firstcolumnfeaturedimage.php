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
/*
 * Holds the values to be used in the fields callbacks
 */
	private $post_type_options;  // assigned at add_post_type_column()
	private $post_type_defaults; // populated at admin_settings_init()

	public function run() {

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'admin_init', array( $this, 'admin_settings_init'));
		add_action( 'admin_init', array( $this, 'add_post_type_column' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'featured_image_column_width' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_link') );
/*
 * Removing featured image column from ajde_events
 * The ajde_events are events from eventON plugins.
 * They have their own featured image in the title column
 */
		add_filter( 'firstcolumnfeaturedimage_post_types', 'remove_ajde_events' );
		function remove_ajde_events( $post_types ) {
			unset( $post_types['ajde_events'] );
			return $post_types;
		}
	} //END run()
/**
 * Set up text domain for translations
 *
 * @since 1.0
 */
	public function load_textdomain() {
		load_plugin_textdomain( 'first-column-featured-image', false, plugin_dir_path( __FILE__ ) . '/languages/' );
	}

/*
 * BEGIN Admin dashboard
 */
 
/*
 * Add link to the fcfi-settings at the Setting menu
 */
	public function add_admin_link() {
		add_options_page(
			__( 'Feat. Image Col. Settings', 'first-column-featured-image' ), // title of the settings page
			__('Featured Image Column', 'first-column-featured-image' ),// title of the submenu
			'manage_options', // capability of the user to see this page
			'fcfi-settings', // slug of the settings page
			array( $this, 'settings_page_html') // callback function when rendering the page
		);
	}
/*
 * Create the fcfi-settings page: /wp-admin/options-general.php?page=fcfi-settings
 */

	public function admin_settings_init() {
		// Add the style settings section
		add_settings_section(
			'settings-section-style', // id of the section
			__('Style Settings', 'first-column-featured-image' ),// title to be displayed
			'style_cb', // callback function to be called when opening section, currently empty
			'fcfi-settings' // page on which to display the section
		);
		
		function style_cb( $args ) {
		// echo section intro text here
			echo __('Choose the size and shape of the featured image at the list table', 'first-column-featured-image');
		}
		
		
		// register the size setting
		register_setting(
			'fcfi-settings', // option group
			'fcfi_size'
		);
		// Add the size setting field on the style section
		add_settings_field(
			'size-field', // id of the settings field
			esc_html__('Featured Image Size:', 'first-column-featured-image' ), // title
			array( $this, 'size_cb'), // callback function
			'fcfi-settings', // page on which settings display
			'settings-section-style' // section on which to show settings
		);
		// register the shape setting
		register_setting(
			'fcfi-settings', // option group
			'fcfi_shape'
		);
		// Add the shape setting field on the style section
		add_settings_field(
			'shape-field', // id of the settings field
			__('Shape: ', 'first-column-featured-image' ), // title
			array( $this, 'shape_cb'), // callback function
			'fcfi-settings', // page on which settings display
			'settings-section-style' // section on which to show settings
		);
		// Add the post types section
		add_settings_section(
			'settings-section-cpt', // id of the section
			__('Post Types', 'first-column-featured-image' ), // title to be displayed
			'post_types_section_cb', // callback function to be called when opening section, currently empty
			'fcfi-settings' // page on which to display the section
		);

		function post_types_section_cb( $args ) {
		// echo section intro text here
		echo __('Select the post types where you want the featured image column to be displayed', 'first-column-featured-image');
		}

		// prepare loop the defined post types to add each setting
		$args = array('_builtin' => false,'show_ui'  => true,);
		$output = 'names';
		$post_types = get_post_types( $args, $output );
		$post_types['post'] = 'post';
		$post_types['page'] = 'page';
		if ( class_exists( 'WooCommerce' ) ) {
			unset( $post_types['product'] );
		}
		$post_types = apply_filters( 'firstcolumnfeaturedimage_post_types', $post_types, $args );
		// register the post types setting
//delete_option('fcfi_post_types');
		register_setting(
			'fcfi-settings', // option group
			'fcfi_post_types' /*,
			array( $this, 'sanitize' )*/
		);
		// loop the post type
		foreach ( $post_types as $post_type ) {
			if ( ! post_type_supports( $post_type, 'thumbnail' ) ) continue;
			$this->post_type_defaults [$post_type]="ON";
			$args = ['post_type'=>$post_type];
			add_settings_field(
				'pt-field-'.$post_type, // id of the settings field
				'', // title
				array( $this, 'post_types_cb'), // callback function
				'fcfi-settings', // page on which settings display
				'settings-section-cpt', // section on which to show settings
				$args
			);
		}

}

		/** 
		 * Get the size settings option and print it value
		 */


function size_cb() {
		$size = esc_attr(get_option('fcfi_size', '70px'));
?>

 <select name="fcfi_size">
  <option <?php if ($size=='70px') echo "selected "; ?>value="70px">XL</option>
  <option <?php if ($size=='60px') echo "selected "; ?>value="60px">L</option>
  <option <?php if ($size=='50px') echo "selected "; ?>value="50px">M</option>
  <option <?php if ($size=='40px') echo "selected "; ?>value="40px">S</option>
  <option <?php if ($size=='32px') echo "selected "; ?>value="32px">XS</option>
</select>

<?php
}

		/** 
		 * Get the shape settings option and print it value
		 */

function shape_cb() {
		$shape = esc_attr(get_option('fcfi_shape', 'circle'));

		printf( '<select name="fcfi_shape"><option ' );
		if ($shape=='circle') echo "selected ";
		printf( 'value="circle">%s</option>',  esc_html__( 'Circle', 'first-column-featured-image' ) );
		echo  '<option ';
		if ($shape=='square') echo "selected ";
		printf( 'value="square">%s</option>',  esc_html__( 'Square', 'first-column-featured-image' ) );
		echo '</select>';

}
		/** 
		 * Get the post types settings option array and print its values
		 */
function post_types_cb(array $args) {
	$post_type=$args['post_type'];
	$pt_selected = esc_attr($this->post_type_options[$post_type]);
	if ($pt_selected=="ON") $checked=" checked "; else $checked="";
	echo '<input type="hidden" name="fcfi_post_types['.$post_type.']" value="OFF" />
			<input type="checkbox" name="fcfi_post_types['.$post_type.']" value="ON"'.$checked.'>
			<span style="font-weight: bold;">'.get_post_type_object($post_type)->label.'</span> (<code>'.$post_type.'</code>)';
}

	function settings_sections_boxes ($page) {
		global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections[$page] ) ) {
			return;
		}

		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
			echo '
					<div class="meta-box-sortables ui-sortable">';
			if ( $section['title'] ) {
				echo "<h2>{$section['title']}</h2>\n";
			}
			echo '
						<div class="postbox">
							<div class="inside">';
			if ( $section['callback'] ) {
				call_user_func( $section['callback'], $section );
			}
			if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
				continue;
			}
			echo '<table class="form-table" role="presentation">';
			do_settings_fields( 'fcfi-settings' , $section['id'] );
			echo '</table>';
			echo '
							</div>
						</div>
					</div>';
		}
	}

	public function settings_page_html() {
		// check user capabilities
		if (!current_user_can('manage_options')) return;
		?>

<div class="wrap">
	<h2><?=__('First Column Featured Image Settings', 'first-column-featured-image' )?></h2>
		<form method="POST" action="options.php">
		<?php 
			settings_fields('fcfi-settings'); 
//			do_settings_sections('fcfi-settings');
			$this->settings_sections_boxes('fcfi-settings');
			submit_button();
		?>
		</form>
</div>
<?php
}
/* END Admin dashboard */

/*
 * Add the featured_image_column at the lists of selected post types.
 */
	public function add_post_type_column() {
		$this->post_type_options = get_option( 'fcfi_post_types', $this->post_type_defaults);
		foreach ( $this->post_type_options as $post_type=>$set) {
			if ($set=="ON") {
				add_filter( "manage_{$post_type}_posts_columns", array( $this, 'add_featured_image_column' ) );
				add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'manage_image_column' ), 10, 2 );
				add_filter( "manage_edit-{$post_type}_sortable_columns", array( $this, 'make_sortable' ) );
				add_action( 'pre_get_posts', array( $this, 'orderby' ) );
			}
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
				.column-featured_image img {
					margin: 0 auto; 
					max-width: <?=esc_attr(get_option('fcfi_size', '70px'));?>;
					max-height: <?=esc_attr(get_option('fcfi_size', '70px'));?>;
					border-radius: <?php if (esc_attr(get_option('fcfi_shape', 'circle'))=='square') {echo 'none';} else {echo '50%';}   ?>; border: 3px solid transparent;}
				.column-featured_image img:hover { border-color: blue;}
				@media screen and (max-width: 782px) {
					.column-featured_image, .wp-list-table .is-expanded td.column-featured_image:not(.hidden) {display: table-cell !important; width: 52px;}
					.column-featured_image.hidden { display: none !important;} 
					.column-featured_image img { margin: 0; max-width: 42px;}
					td.column-featured_image.featured_image::before { display: none !important;} 
				}
			</style> <?php
		}
	}
}
