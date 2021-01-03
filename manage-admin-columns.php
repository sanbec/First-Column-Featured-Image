<?php
/**
 * This plugin adds a featured image thumbnail column to WordPress admin dashboard for all types of posts 
 *
 * @package   ManageAdminColumns 
 * @author    Santiago Becerra <santi@wpcombo.com>
 * @license   GPL-3.0+
 * @link      https://wpcombo.com
 * @copyright 2020 WPCombo OU
 *
 * @wordpress-plugin
 * Plugin Name:       Manage Admin Columns
 * Plugin URI:        https://github.com/sanbec/manage-admin-columns
 * Description:       This plugin adds a featured image column to the WordPress admin.
 * Version:           1.3.0
 * Author:            Santiago Becerra
 * Author URI:        https://wpcombo.com
 * Text Domain:       manage-admin-columns
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'MANAGEADMINCOLUMNS_BASENAME' ) ) {
	define( 'MANAGEADMINCOLUMNS_BASENAME', plugin_basename( __FILE__ ) );
}

// Include classes
function wpcmac_featuredimagecolumn_require() {
	$files = array(
		'class-firstcolumnfeaturedimage',
	);

	foreach ( $files as $file ) {
		require plugin_dir_path( __FILE__ ) . 'includes/' . $file . '.php';
	}
}
wpcmac_featuredimagecolumn_require();

// Instantiate main class
$firstcolumnfeaturedimage = new wpcmac_FeaturedImageColumn();

// Run the plugin
$firstcolumnfeaturedimage->wpcmac_run();
