<?php
/**
 * This simple plugin add the post thumbnail to the first column of WordPress Admin 
 *
 * @package   FirstColumnFeaturedImage 
 * @author    Santiago Becerra <santi@wpcombo.com>
 * @license   GPL-3.0+
 * @link      https://wpcombo.com
 * @copyright 2020 WPCombo OU
 *
 * @wordpress-plugin
 * Plugin Name:       First Column Featured Image
 * Plugin URI:        https://github.com/sanbec/First-Column-Featured-Image
 * Description:       This plugin adds a featured image column to the WordPress admin.
 * Version:           1.0
 * Author:            Santiago Becerra
 * Author URI:        https://wpcombo.com
 * Text Domain:       first-column-featured-image
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'FIRSTCOLUMNFEATUREDIMAGE_BASENAME' ) ) {
	define( 'FIRSTCOLUMNFEATUREDIMAGE_BASENAME', plugin_basename( __FILE__ ) );
}

// Include classes
function firstcolumnfeaturedimage_require() {
	$files = array(
		'class-firstcolumnfeaturedimage',
	);

	foreach ( $files as $file ) {
		require plugin_dir_path( __FILE__ ) . 'includes/' . $file . '.php';
	}
}
firstcolumnfeaturedimage_require();

// Instantiate main class
$firstcolumnfeaturedimage = new FirstColumnFeaturedImage();

// Run the plugin
$firstcolumnfeaturedimage->run();
