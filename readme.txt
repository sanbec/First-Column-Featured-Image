=== Manage Admin Columns ===
Contributors: sanbec, wpcombo
Donate link: https://paypal.me/sanbec
Tags: featured image, admin columns
Requires at least: 5.0
Tested up to: 5.7
Stable tag: 1.4.0
Requires PHP: 7.0
License: GPL 3.0 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html


This plugin adds a featured image column to WordPress Dashboard. It automatically adds a column to any post type which supports a featured image. 
Planning of adding another column for the slug.


== Description ==

This plugin adds a featured image column to WordPress Dashboard. It automatically adds a column to any post type which supports a featured image. It's an improvement from the plugin [Add Featured Image Column](https://wordpress.org/plugins/add-featured-image-column/).


== Installation ==

1. Upload the entire `manage-admin-columns` folder to your `/wp-content/plugins` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Optionally, visit the Settings > Manage Admin Columns to change the default behavior of the plugin.

== Frequently Asked Questions ==

It's simple! Just activate, adjust settings and enjoy.

== Screenshots ==
1. Circle, XL, border on hover posts featured images
1. Square, M, no border on hover posts featured images
1. Manage admin columns settings page
1. Featured Image Ligthbox

== Upgrade Notice ==

= 1.4.0 =
*Enjoy the new features and settings: 
**Open a lightbox when click on featured imagen
**You can enable or disable the blue border on hover
**You can enable or disable the lightbox feature

== Changelog ==
= 1.4.0 =
* new: Lightbox setting
* improvement: Remove border on hover for noimage placeholders
* improvement: Simpler lightbox effect with tickbox

= 1.3.1 =
* new: The image columm opens in a Ligthbox on click
* improvement: Move Image Column after the select checkbox
* improvement: Changed function prefixes to namespaces
* improvement: Convert class into static
* improvement: Better code structure
* improvement: Better code to order columns
* fix: Wrong metadata
* fix: Bad syntax

= 1.3.0 =
* new: setting to choose if the image border is shown on hover

= 1.2.0 =
* new: svg image for posts with no featured image
* improved: better code 

= 1.1.5 =
* improved: output for posts with no featured image

= 1.1.4 =
* fixed: featured image column display on mobile

= 1.1.3 =
* Improved: any post type which supports featured images (including private post types) will display a featured image column
* Added: the args to get the list of post types has been added to the post types filter
* Changed: admin column heading is just "Image" instead of "Featured Image"

= 1.1.2 =
* Added: text_domain, language files
* Fixed (really): featured image column on mobile

= 1.1.1 =
* Fixed: featured image column on mobile

= 1.1.0 =
* Added: the featured image column is now sortable.
* Due to redundancy, this plugin now deactivates if Display Featured Image for Genesis is active.

= 1.0.1 =
* Added filter to exclude post types

= 1.0.0 =
* Initial release on WordPress.org

= 0.9.0 =
* Initial release on Github
