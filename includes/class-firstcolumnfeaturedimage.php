<?php
/**
 * Main plugin class
 *
 * @package   ManageAdminColumns
 * @author    Santiago Becerra <santi@wpcombo.com>
 * @license   GPL-3.0+
 * @link      https://wpcombo.com
 * @copyright 2019 WPCombo OÜ
 */

namespace wpcombo\fcfi;

class FeaturedImageColumn {
/*
 * Holds the values to be used in the fields callbacks
 */
	public static $post_type_options;  // assigned at add_post_type_column()
	private static $post_type_defaults; // populated at admin_settings_init()

	public static function run() {

		add_action( 'plugins_loaded', array( __CLASS__, 'load_textdomain' ) );
		add_action( 'admin_init', array( __CLASS__, 'admin_settings_init'));
		add_action( 'admin_init', array( __CLASS__, 'add_post_type_column' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'featured_image_column_width' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_link') );
	} //END run()
/**
 * Set up text domain for translations
 */
	public static function load_textdomain() {
		load_plugin_textdomain( 'manage-admin-columns', false, plugin_dir_path( __FILE__ ) . '/languages/' );
	}

/*
 * BEGIN Admin dashboard
 */
 
/*
 * Add link to the fcfi-settings at the Setting menu
 */
	public static function add_admin_link() {
		add_options_page(
			__( 'Manage Admin Columns Settings', 'manage-admin-columns' ), // title of the settings page
			__('Featured Image Column', 'manage-admin-columns' ),// title of the submenu
			'manage_options', // capability of the user to see this page
			'fcfi-settings', // slug of the settings page
			array( __CLASS__, 'settings_page_html') // callback function when rendering the page
		);
	}
/*
 * Create the fcfi-settings page: /wp-admin/options-general.php?page=fcfi-settings
 */

	public static function admin_settings_init() {
		// Add the style settings section
		add_settings_section(
			'settings-section-style', // id of the section
			__('Style Settings', 'manage-admin-columns' ),// title to be displayed
			'wpcombo\fcfi\style_cb', // callback function to be called when opening section
			'fcfi-settings' // page on which to display the section
		);
		
		function style_cb( $args ) {
		// echo section intro text here
			echo __('Choose the size and shape of the featured image at the list table', 'manage-admin-columns');
		}
		
		
		// register the size setting
		register_setting(
			'fcfi-settings', // option group
			'fcfi_size'
		);
		// Add the size setting field on the style section
		add_settings_field(
			'size-field', // id of the settings field
			esc_html__('Featured Image Size:', 'manage-admin-columns' ), // title
			'wpcombo\fcfi\size_cb', // callback function
			'fcfi-settings', // page on which settings display
			'settings-section-style' // section on which to show settings
		);

		// Get the size settings option and print it value

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
		} // size_cb
	

		// register the shape setting
		register_setting(
			'fcfi-settings', // option group
			'fcfi_shape'
		);
		// Add the shape setting field on the style section
		add_settings_field(
			'shape-field', // id of the settings field
			__('Shape: ', 'manage-admin-columns' ), // title
			'wpcombo\fcfi\shape_cb', // callback function
			'fcfi-settings', // page on which settings display
			'settings-section-style' // section on which to show settings
		);
		// Get the shape settings option and print it value
		function shape_cb() {
			$shape = esc_attr(get_option('fcfi_shape', 'circle'));
	
			printf( '<select name="fcfi_shape"><option ' );
			if ($shape=='circle') echo "selected ";
			printf( 'value="circle">%s</option>',  esc_html__( 'Circle', 'manage-admin-columns' ) );
			echo  '<option ';
			if ($shape=='square') echo "selected ";
			printf( 'value="square">%s</option>',  esc_html__( 'Square', 'manage-admin-columns' ) );
			echo '</select>';
		}
		// register the border setting
		register_setting(
			'fcfi-settings', // option group
			'fcfi_border'
		);
		// Add the shape setting field on the style section
		add_settings_field(
			'border-field', // id of the settings field
			__('Border ', 'manage-admin-columns' ), // title
			'wpcombo\fcfi\border_cb', // callback function
			'fcfi-settings', // page on which settings display
			'settings-section-style' // section on which to show settings
		);
		// Get the border settings option and print it value
		function border_cb() {
			$border = esc_attr(get_option('fcfi_border', 'ON'));
			if ($border=='ON') $checked=" checked "; else $checked="";
			echo '<input type="hidden" name="fcfi_border" value="OFF" />
			<input type="checkbox" name="fcfi_border" value="ON"'.$checked.'>
			<span>'.esc_html__( 'Show border on hover', 'manage-admin-columns' ).'</span>';
		}
		// Add the post types section
		add_settings_section(
			'settings-section-cpt', // id of the section
			__('Post Types', 'manage-admin-columns' ), // title to be displayed
			'wpcombo\fcfi\post_types_section_cb', // callback function to be called when opening section, currently empty
			'fcfi-settings' // page on which to display the section
		);

		function post_types_section_cb( $args ) {
		// echo section intro text here
			echo __('Select the post types where you want the featured image column to be displayed', 'manage-admin-columns');
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
		$post_types = apply_filters( 'fcfi_post_types', $post_types, $args );
		// register the post types setting
		register_setting(
			'fcfi-settings', // option group
			'fcfi_post_types' /*,
			array( $this, 'sanitize' )*/
		);
		// loop the post type
		foreach ( $post_types as $post_type ) {
			if ( ! post_type_supports( $post_type, 'thumbnail' ) ) continue;
			self::$post_type_defaults [$post_type]="ON";
			$args = ['post_type'=>$post_type];
			add_settings_field(
				'pt-field-'.$post_type, // id of the settings field
				'', // title
				'wpcombo\fcfi\post_types_cb', // callback function
				'fcfi-settings', // page on which settings display
				'settings-section-cpt', // section on which to show settings
				$args
			);
		}
		/** 
		 * Get the post types settings option array and print its values
		 */
		function post_types_cb(array $args) {
			$post_type=$args['post_type'];
			$pt_selected = esc_attr(FeaturedImageColumn::$post_type_options[$post_type]);
			if ($pt_selected=="ON") $checked=" checked "; else $checked="";
			echo '<input type="hidden" name="fcfi_post_types['.$post_type.']" value="OFF" />
					<input type="checkbox" name="fcfi_post_types['.$post_type.']" value="ON"'.$checked.'>
					<span style="font-weight: bold;">'.get_post_type_object($post_type)->label.'</span> (<code>'.$post_type.'</code>)';
		}
	

	} // end public static function admin_settings_init()

	private static function settings_sections_boxes ($page) {
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

	public static function settings_page_html() {
		// check user capabilities
		if (!current_user_can('manage_options')) return;
		?>

		<div class="wrap">
			<h2><?=__('Manage Admin Columns Settings', 'manage-admin-columns' )?></h2>
				<form method="POST" action="options.php">
				<?php 
					settings_fields('fcfi-settings'); 
		//			do_settings_sections('fcfi-settings');
					self::settings_sections_boxes('fcfi-settings');
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
	public static function add_post_type_column() {
		self::$post_type_options = get_option( 'fcfi_post_types', self::$post_type_defaults);
		foreach ( self::$post_type_options as $post_type=>$set) {
			if ($set=="ON") {
				add_filter( "manage_{$post_type}_posts_columns", array( __CLASS__, 'add_featured_image_column' ) );
				add_action( "manage_{$post_type}_posts_custom_column", array( __CLASS__, 'manage_image_column' ), 10, 2 );
				add_filter( "manage_edit-{$post_type}_sortable_columns", array(__CLASS__, 'make_sortable' ) );
				add_action( 'pre_get_posts', array( __CLASS__, 'orderby' ) );
			}
		}
	}
	/**
	 * add featured image column
	 * @param array $columns set up new column to show featured image for taxonomies/posts/etc.
	 *
	 * @return array
	 */
	public function add_featured_image_column( $columns ) {

		$first_column = array('featured_image' => __( 'Image', 'manage-admin-columns' ));

		return array_merge( $first_column, $columns );

	}

	/**
	 * Make the featured image column sortable.
	 * @param $columns
	 * @return mixed
	 */
	public static function make_sortable( $columns ) {
		$columns['featured_image'] = 'featured_image';
		return $columns;
	}

	/**
	 * Set a custom query to handle sorting by featured image
	 * @param $query WP_Query
	 */
	public static function orderby( $query ) {
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
	 */
	public static function manage_image_column( $column, $post_id ) {

		if ( 'featured_image' !== $column ) {
			return;
		}
		$image_id = get_post_thumbnail_id( $post_id );
		if ( ! $image_id ) {	
			printf( '<img src="%1$s" alt="%2$s" title="%2$s" />', plugins_url( '/assets/sin-imagen.svg', __FILE__ ), esc_html__( 'No image', 'manage-admin-columns' ) );
			printf( '<span class="screen-reader-text">%s</span>', esc_html__( 'No image', 'manage-admin-columns' ) );
			return;
		}

		$args = array(
			'image_id' => $image_id,
			'context'  => 'post',
			'alt'      => the_title_attribute( 'echo=0' ),
		);

		echo wp_kses_post( self::admin_column_image( $args ) );
	}

	/**
	 * Generic function to return featured image
	 * @param $args array of values to pass to function ( image_id, context, alt_tag )
	 *
	 * @return string
	 */
	protected static function admin_column_image( $args ) {
		$image_id = $args['image_id'];
		$preview  = wp_get_attachment_image_src( $image_id, 'thumbnail' );
		$preview  = apply_filters( 'fcfi_thumbnail', $preview, $image_id );
		if ( ! $preview ) {
			return '';
		}
		return sprintf( '<img src="%1$s" alt="%2$s" />', $preview[0], $args['alt'] );
	}

	/**
	 * Creates an inline stylesheet to set featured image column width
	 */
	public static function featured_image_column_width() {
		$screen = get_current_screen();
		if ( ! post_type_supports( $screen->post_type, 'thumbnail' ) ) {
			return;
		}
		if ( in_array( $screen->base, array( 'edit' ), true ) ) { ?>
			<style type="text/css">
				.column-featured_image { width: 85px; }
				.column-featured_image img {
					margin: 0 auto; 
					width: <?=esc_attr(get_option('fcfi_size', '70px'));?>;
					height: <?=esc_attr(get_option('fcfi_size', '70px'));?>;
					object-fit: cover;
					<?php if (esc_attr(get_option('fcfi_shape', 'circle'))=='circle') {echo 'border-radius: 50%';}   ?>;
					<?php if (esc_attr(get_option('fcfi_border', 'ON'))=='ON') {echo 'border: 3px solid transparent';}   ?>;
					}
				<?php if (esc_attr(get_option('fcfi_border', 'ON'))=='ON') {echo '.column-featured_image img:hover { border-color: blue;}';}   ?>;

				
				@media screen and (max-width: 782px) {
					.column-featured_image, .wp-list-table .is-expanded td.column-featured_image:not(.hidden) {display: table-cell !important; width: 52px;}
					.column-featured_image.hidden { display: none !important;} 
					.column-featured_image img { margin: 0; max-width: 42px;}
					td.column-featured_image.featured_image::before { display: none !important;} 
				}
			</style> <?php
		}
	}
} // end class FeaturedImageColumn
FeaturedImageColumn::run();
