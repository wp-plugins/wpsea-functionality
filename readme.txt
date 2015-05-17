=== Seattle WordPress Functionality === 
Contributors: awoods
Tags: widget, shortcode, google, analytics, meta
Requires at least: 3.0
Tested up to: 4.1.0
Stable Tag: 0.8.0
License: GPLv2
License URI: http://www.opensource.org/licenses/GPL-2.0

Provides some essential site features: Google analytics, popular posts widget, random post widget, contact form short code, easily modify wp_head.

== Description ==

A functionality plugin is one that provides a common set of functionality that is
used across a wide range of WordPress based websites. This plugin contains a set 
of functionality that has been deemed useful by the Seattle WordPress community.

* Contact Form shortcode
* Popular posts widget
* Random post widget
* provides an easy way to disable some meta info from wp_head

== Installation ==

1. Upload the files to the `/wp-content/plugins/wpsea-functionality/` directory or install through WordPress directly.
1. Activate the "WPSea Functionality" plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 0.8.2 =
* Fix missing site_essential.js
* Replaced deprecated get_bloginfo parameter 'siteurl' with current value 'url'.
* Replaced inline CSS error styles with class names to give designers control.


= 0.8.0 =
* Removed ability to override WordPress' jQuery with Google's, because it's a bad practice and can cause instability.


= 0.7.4 =
* Added setting to Hide meta generator from wp_head (radio)
* Added setting to Hide RSD link from wp_head (radio) 
* Added Hide feeds links from wp_head (radio) 
* Added Hide wlwmanifest from wp_head (radio) 
* Replaced calls to deprecated widget function with current implementation.
* Fixed contact form shortcode to use correct name.

= 0.7 =
* Added UI under Settings menu
* Added several theme options
* Added Google Analytics - Analytics ID is entered on settings page
* Added jQuery to Settings page - jQuery can be turned on/off via settings page
* Added option to use jQuery on Google CDN via settings page
* Added popular posts and random post widgets
* Added shortcode for contact form
* Added No Frames Enabled - If this is turned on, it will break out of the 
  frameset that your site is in.

= 0.1 = 
* First version. Created project skeleton

