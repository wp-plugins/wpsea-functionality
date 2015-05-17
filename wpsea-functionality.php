<?php
/*
Plugin Name: WP Seattle Functionality
Plugin URI: https://wordpress.org/plugins/wpsea-functionality/
Description: Functionality plugin for code/settings commonly use in the Seattle WordPress community. Provides Functionality this is common to most sites: Google Analytics, No wordpress update nag, Support Information Dashboard Widget
Version: 0.8.2
Author: Andrew Woods
Author URI: http://andrewwoods.net
Text Domain: wpsea-func
Network: false
License: GPL2

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or ( at your option ) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/


//---------------------------------
//      CONSTANTS
//---------------------------------
define( 'WPSEA_FUNC_PLUGIN_DIR', trailingslashit( dirname( __FILE__) ) );
define( 'WPSEA_FUNC_TEXT_DOMAIN', 'wpsea-func' );
define( 'WPSEA_FUNC_VERSION', '0.8.2' );
define( 'WPSEA_FUNC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( ! defined('EOL_CR') ) {
	define('EOL_CR', "\r");
}
if ( ! defined('EOL_LF') ) {
	define('EOL_LF', "\n");
}

if ( ! defined('EOL_CRLF') ) {
	define('EOL_CRLF', "\r\n");
}


//---------------------------------
//      HOOKS
//---------------------------------
if ( get_option( 'wpsea_func_generator_enabled' ) == 'no' ) {
	remove_action( 'wp_head', 'wp_generator' );
}

if ( get_option( 'wpsea_func_feed_links_enabled' ) == 'no' ) {
	remove_action( 'wp_head', 'feed_links_extra', 3 ); 
	remove_action( 'wp_head', 'feed_links', 2 ); 
}

if ( get_option( 'wpsea_func_wlwmanifest_enabled' ) == 'no' ) {
	remove_action( 'wp_head', 'wlwmanifest_link' );
}

if ( get_option( 'wpsea_func_rsd_enabled' ) == 'no' ) {
	remove_action( 'wp_head', 'rsd_link');
}

register_activation_hook( __FILE__, 'wpsea_func_setup' );
register_uninstall_hook( __FILE__, 'wpsea_func_teardown' );


add_action( 'wp_enqueue_scripts', 'wpsea_func_load_js' );

add_action( 'admin_init', 'no_update_nag' );
add_action( 'admin_init', 'wpsea_func_admin_init' );
add_action( 'admin_menu', 'wpsea_func_modify_menu' );

add_action( 'wp_dashboard_setup', 'example_remove_dashboard_widgets' );
add_action( 'wp_dashboard_setup', 'wpsea_func_add_dashboard_widgets' );

if ( get_option( 'wpsea_func_analytics_enabled' ) == 'yes' ) {
	add_action( 'wp_footer', 'wpsea_func_analytics_js' );
}
add_shortcode( 'wpsea_contactform', 'wpsea_func_contact_form' );


//---------------------------------
//      FUNCTIONS
//---------------------------------
function wpsea_func_setup() {
	add_option( 'wpsea_func_generator_enabled', 'yes' );
	add_option( 'wpsea_func_feed_links_enabled', 'yes' );
	add_option( 'wpsea_func_wlwmanifest_enabled', 'yes' );
	add_option( 'wpsea_func_rsd_enabled', 'yes' );

	add_option( 'wpsea_func_analytics_id' );
	add_option( 'wpsea_func_analytics_enabled', 'yes' );
	add_option( 'wpsea_func_noframes_enabled', 'no' );
}

function wpsea_func_teardown() {
	delete_option( 'wpsea_func_generator_enabled' );
	delete_option( 'wpsea_func_feed_links_enabled' );
	delete_option( 'wpsea_func_wlwmanifest_enabled' );

	delete_option( 'wpsea_func_analytics_id' );
	delete_option( 'wpsea_func_analytics_enabled' );
	delete_option( 'wpsea_func_noframes_enabled' );
}

function wpsea_func_modify_menu() {

	$_settings = wpsea_func_get_settings();

	add_options_page(
		$_settings['wpsea_func_page_title'], // Page Title
		$_settings['wpsea_func_page_title'], // Sub-menu Title
		'manage_options', // Minimum access control
		__FILE__,
		'wpsea_func_options_page' // collback function
	);
}

/** 
 * Helper function for defining variables for the current page 
 * 
 * @return array 
*/
function wpsea_func_get_settings() {  

	$output = array();  

	// put together the output array  
	$output['wpsea_func_option_name']   = ''; // the option name as used in get_option().  
	$output['wpsea_func_page_title']    = __( 'Site Functionality', WPSEA_FUNC_TEXT_DOMAIN );
	$output['wpsea_func_author_name']   = __( 'Wordpress Seattle Meetup', WPSEA_FUNC_TEXT_DOMAIN );
	$output['wpsea_func_page_sections'] = wpsea_func_page_sections();   

	return $output;  
}  

/** 
 * Define our settings sections 
 * 
 * @return array 
*/  
function wpsea_func_page_sections() {

	$sections = array();  
	$sections['wpsea_func_main'] = array( 
		'title' => __( 'Main', WPSEA_FUNC_TEXT_DOMAIN ),
		'callback' => 'wpsea_func_main_callback', 
	);  
	$sections['header_section'] = array(
		'title' => __( 'Page Headers', WPSEA_FUNC_TEXT_DOMAIN ),
		'callback' => 'wpsea_func_header_callback',
		);  
	$sections['analytics_section'] = array(
		'title' => __( 'Analytics', WPSEA_FUNC_TEXT_DOMAIN ),
		'callback' => 'wpsea_func_analytics_callback',
		);  
	$sections['javascript_section'] = array( 
		'title' => __( 'Javascript', WPSEA_FUNC_TEXT_DOMAIN ),
		'callback' => 'wpsea_func_javascript_callback',
		);  
	$sections['shortcode_section'] = array(
		'title' => __( 'Shortcodes', WPSEA_FUNC_TEXT_DOMAIN ),
		'callback' => 'wpsea_func_shortcode_callback',
		);  
	$sections['widget_section'] = array( 
		'title' => __( 'Widgets', WPSEA_FUNC_TEXT_DOMAIN ),
		'callback' => 'wpsea_func_widget_callback',
		);  
	
	return $sections;  
}

/* stub function */
function section_callback() {
	echo 'this is callback function text ';
	// return;
}

/**
 * Setup Essential Settings options page
 *
 * Register the settings and build of form fields for the options page.
 *
 * @since 0.1
 *
 * @param  type $name  it does something
 * @return void
*/
function wpsea_func_admin_init() {

	$_settings = wpsea_func_get_settings();
	$_sections = wpsea_func_page_sections();

	// add_settings_section( $id, $title, $callback, $page );  
	if ( isset( $_sections ) ) {
		// call the "add_settings_section" for each!  
		foreach ( $_sections as $id => $data ) {
			$title    = $data['title'];
			$callback = ( isset( $data['callback'] ) ) ? $data['callback'] : 'section_callback';

			add_settings_section( $id, $title, $callback, 'wpsea_func' );
		}  
	}  

	register_setting(
		'analytics_section',
		'wpsea_func_analytics_id'
	);

	register_setting(
		'analytics_section',
		'wpsea_func_analytics_enabled'
	);

	register_setting(
		'javascript_section',
		'wpsea_func_noframes_enabled'
	);

	register_setting(
		'javascript_section',
		'wpsea_func_setting_jquery_enabled'
	);


	register_setting(
		'wpsea_func_main',
		'wpsea_func_contact_sendto'
	);

	add_settings_field(
		'wpsea_func_generator_enabled', // string used in the 'id' attribute of tags
		'Generator Enabled', // Title of the Field
		'wpsea_func_setting_generator_enabled', // function that renders the field
		'wpsea_func', // the type of settings page on which to show the field
		'header_section' // The section of the settings page in which to show the box
	);

	add_settings_field(
		'wpsea_func_feed_links_enabled', // string used in the 'id' attribute of tags
		'Feed Links Enabled', // Title of the Field
		'wpsea_func_setting_feed_links_enabled', // function that renders the field
		'wpsea_func', // the type of settings page on which to show the field
		'header_section' // The section of the settings page in which to show the box
	);

	add_settings_field(
		'wpsea_func_wlwmanifest_enabled', // string used in the 'id' attribute of tags
		'WLW Manifest Enabled', // Title of the Field
		'wpsea_func_setting_wlwmanifest_enabled', // function that renders the field
		'wpsea_func', // the type of settings page on which to show the field
		'header_section' // The section of the settings page in which to show the box
	);

	add_settings_field(
		'wpsea_func_rsd_enabled', // string used in the 'id' attribute of tags
		'RSD Enabled', // Title of the Field
		'wpsea_func_setting_rsd_enabled', // function that renders the field
		'wpsea_func', // the type of settings page on which to show the field
		'header_section' // The section of the settings page in which to show the box
	);

	add_settings_field(
		'wpsea_func_analytics_enabled', // string used in the 'id' attribute of tags
		'GA Enabled', // Title of the Field
		'wpsea_func_setting_analytics_enabled', // function that renders the field
		'wpsea_func', // the type of settings page on which to show the field
		'analytics_section' // The section of the settings page in which to show the box
	);

	add_settings_field(
		'wpsea_func_analytics_id',  // string used in the 'id' attribute of tags
		'Google Analytics ID',  // Title of the Field
		'wpsea_func_setting_analytics_id', // function that renders the field
		'wpsea_func',  // the type of settings page on which to show the field
		'analytics_section' // The section of the settings page in which to show the box
	);

	add_settings_field(
		'wpsea_func_noframes_enabled', // string used in the 'id' attribute of tags
		'No Frames Enabled', // Title of the Field
		'wpsea_func_setting_noframes_enabled', // function that renders the field
		'wpsea_func',  // the type of settings page on which to show the field
		'javascript_section' // The section of the settings page in which to show the box
	);

	add_settings_field(
		'wpsea_func_jquery_enabled', // string used in the 'id' attribute of tags
		'jQuery Enabled',  // Title of the Field
		'wpsea_func_setting_jquery_enabled', // function that renders the field
		'wpsea_func', // the type of settings page on which to show the field
		'javascript_section' // The section of the settings page in which to show the box
	);


	add_settings_field(
		'wpsea_func_contact_sendto', // string used in the 'id' attribute of tags
		'Send Contact Form To',  // Title of the Field
		'wpsea_func_setting_contact_sendto', // function that renders the field
		'wpsea_func', // the type of settings page on which to show the field
		'wpsea_func_main' // The section of the settings page in which to show the box
	);

}

function wpsea_func_main_callback() {
?>	
	<p>
	The email address is used by the Contact Form
	</p>
<?php	
}

function wpsea_func_header_callback() {
?>
	<p>
		These settings update actions that modify the page head of your theme
	</p>
<?php
}

function wpsea_func_analytics_callback() {
?>
	<p>
		These settings provide some control over the website.
		<strong>Google Analytics</strong> provides website tracking
		for free. Just set the 'Google Analytics Enabled' to yes and provide the website 
		tracking id, and your site will be sending tracking information to Google.
	</p>
<?php
}

function wpsea_func_javascript_callback() {
?>
	<p>
		<strong>jQuery</strong> can be turned on. Without any other settings,
		jquery will be loaded from Wordpress' core.
		<strong>'No Frames'</strong> is an option that detects
		when your website has been loaded into a frameset. When enabled, it
		will break your site out of the frameset, so that your website will
		behave normally and prevent other sites from branding or hijacking
		your site.
	</p>
<?php
}

function wpsea_func_shortcode_callback() {
?>
	<p>
		Simply create a page and add the shortcode to it. 
		There are 2 attributes that can be used to override default functionality.
		This helps designers by providing flexibility. 
		<ul style="list-style-type: disc; list-style-position: inside;">
		<li>send_to</li>
		<li>redirect_to</li>
		</ul>
	</p>
	
	<p> <strong>Implement the basic contact form:</strong><br />
		[wpsea_contactform]
		<br />
		<br/>
		The form will submit the information to the same page, and send the form data
		to the 'Send Contact Form To' specified above.
	</p>

	<p> <strong>Use a different email address to send to:</strong><br/>
		[wpsea_contactform send_to="new_email@hostname.com"]
		<br />
		<br />
	</p>

	<p> <strong>To Redirect the user to a new page upon success:</strong><br/>
		[wpsea_contactform redirect_to="/thank-you"]
	</p>

	<p> <strong>Add some information above the form</strong><br/>
		[wpsea_contactform sendto="new_email@hostname.com"]<br />
			&nbsp;&nbsp;&nbsp;&nbsp;This will be displayed above the form<br/> 
		[/wpsea_contactform]
	</p>
<?php
}


function wpsea_func_widget_callback() {
?>
	<p>
		There are 2 <a href="<?php echo admin_url('widgets.php'); ?>">widgets</a>
		that come with this module - <strong>Popular Posts</strong>,
		and <strong>Latest Posts</strong>
	</p>
<?php
}

/**
 * Render the Analytics ID field
 *
 * @since 0.1
 *
 * @param  wp_option $wpsea_func_analytics_id
 * @return void
*/
function wpsea_func_setting_analytics_id() {
	$analytics_id = get_option( 'wpsea_func_analytics_id' );
	?>
	<input id="wpsea_func_analytics_id" name="wpsea_func_analytics_id"
		type="text" value="<?php echo $analytics_id; ?>" /> e.g. UA-xxxxxxxx-x
	<?php
}

/**
 * Render the Generator Enabled field
 *
 * @since 0.3
 *
 * @param  wp_option $wpsea_func_generator_enabled
 * @return void
*/
function wpsea_func_setting_generator_enabled() {
	$generator_enabled = get_option( 'wpsea_func_generator_enabled', 'yes' );

	if ( $generator_enabled == 'yes' ) {
		?>
		<input type="radio" id="wpsea_func_generator_enabled_yes" 
		name="wpsea_func_generator_enabled" checked="checked" value="yes"/>
		<label for="wpsea_func_generator_enabled_yes">Yes</label>
		<input type="radio" id="wpsea_func_generator_enabled_no" 
		name="wpsea_func_generator_enabled" value="no"/>
		  <label for="wpsea_func_generator_enabled_no">No</label>
		<?php
	} else {
	?>
		<input type="radio" id="wpsea_func_generator_enabled_yes" 
		name="wpsea_func_generator_enabled" value="yes"/>
		<label for="wpsea_func_generator_enabled_yes">Yes</label>
		<input type="radio" id="wpsea_func_generator_enabled_no" 
		name="wpsea_func_generator_enabled" checked="checked" value="no"/>
		<label for="wpsea_func_generator_enabled_no">No</label>
	<?php
	}
}

/**
 * Render the Manifest Enabled field
 *
 * @since 0.3
 *
 * @param  wp_option $wpsea_func_wlwmanifest_enabled
 * @return void
*/
function wpsea_func_setting_wlwmanifest_enabled() {
	$wlwmanifest_enabled = get_option( 'wpsea_func_wlwmanifest_enabled', 'yes' );
	?>
	<p>Determine if <em>Windows Live Writer(WLW) manifest</em> should be available.</p>
	<?php
	if ( $wlwmanifest_enabled == 'yes' ) {
		?>
		<input type="radio" id="wpsea_func_wlwmanifest_enabled_yes" 
		name="wpsea_func_wlwmanifest_enabled" checked="checked" value="yes"/>
		<label for="wpsea_func_wlwmanifest_enabled_yes">Yes</label>
		<input type="radio" id="wpsea_func_wlwmanifest_enabled_no" 
		name="wpsea_func_wlwmanifest_enabled" value="no"/>
		  <label for="wpsea_func_wlwmanifest_enabled_no">No</label>
		<?php
	} else {
	?>
		<input type="radio" id="wpsea_func_wlwmanifest_enabled_yes" 
		name="wpsea_func_wlwmanifest_enabled" value="yes"/>
		<label for="wpsea_func_wlwmanifest_enabled_yes">Yes</label>
		<input type="radio" id="wpsea_func_wlwmanifest_enabled_no" 
		name="wpsea_func_wlwmanifest_enabled" checked="checked" value="no"/>
		<label for="wpsea_func_wlwmanifest_enabled_no">No</label>
	<?php
	}
}

/**
 * Render the Feed Links Enabled field
 *
 * @since 0.3
 *
 * @param  wp_option $wpsea_func_feed_links_enabled
 * @return void
*/
function wpsea_func_setting_feed_links_enabled() {
	$feed_links_enabled = get_option( 'wpsea_func_feed_links_enabled', 'yes' );

	if ( $feed_links_enabled == 'yes' ) {
		?>
		<input type="radio" id="wpsea_func_feed_links_enabled_yes" 
		name="wpsea_func_feed_links_enabled" checked="checked" value="yes"/>
		<label for="wpsea_func_feed_links_enabled_yes">Yes</label>
		<input type="radio" id="wpsea_func_feed_links_enabled_no" 
		name="wpsea_func_feed_links_enabled" value="no"/>
		  <label for="wpsea_func_feed_links_enabled_no">No</label>
		<?php
	} else {
	?>
		<input type="radio" id="wpsea_func_feed_links_enabled_yes" 
		name="wpsea_func_feed_links_enabled" value="yes"/>
		<label for="wpsea_func_feed_links_enabled_yes">Yes</label>
		<input type="radio" id="wpsea_func_feed_links_enabled_no" 
		name="wpsea_func_feed_links_enabled" checked="checked" value="no"/>
		<label for="wpsea_func_feed_links_enabled_no">No</label>
	<?php
	}
}

/**
 * Render the RSD Enabled field
 *
 * @since 0.3
 *
 * @param  wp_option $wpsea_func_rsd_enabled
 * @return void
*/
function wpsea_func_setting_rsd_enabled() {
	$rsd_enabled = get_option( 'wpsea_func_rsd_enabled', 'yes' );

	if ( $rsd_enabled == 'yes' ) {
		?>
		<input type="radio" id="wpsea_func_rsd_enabled_yes" 
		name="wpsea_func_rsd_enabled" checked="checked" value="yes"/>
		<label for="wpsea_func_rsd_enabled_yes">Yes</label>
		<input type="radio" id="wpsea_func_rsd_enabled_no" 
		name="wpsea_func_rsd_enabled" value="no"/>
		  <label for="wpsea_func_rsd_enabled_no">No</label>
		<?php
	} else {
	?>
		<input type="radio" id="wpsea_func_rsd_enabled_yes" 
		name="wpsea_func_rsd_enabled" value="yes"/>
		<label for="wpsea_func_rsd_enabled_yes">Yes</label>
		<input type="radio" id="wpsea_func_rsd_enabled_no" 
		name="wpsea_func_rsd_enabled" checked="checked" value="no"/>
		<label for="wpsea_func_rsd_enabled_no">No</label>
	<?php
	}
}


/**
 * Render the Google Analytics Enabled field
 *
 * @since 0.7
 *
 * @param  wp_option $wpsea_func_analytics_enabled
 * @return void
*/
function wpsea_func_setting_analytics_enabled() {
	$analytics_enabled = get_option( 'wpsea_func_analytics_enabled' );

	if ( $analytics_enabled == 'yes' ) {
		?>
		<input type="radio" id="wpsea_func_analytics_enabled_yes" 
		name="wpsea_func_analytics_enabled" checked="checked" value="yes"/>
		<label for="wpsea_func_analytics_enabled_yes">Yes</label>
		<input type="radio" id="wpsea_func_analytics_enabled_no" 
		name="wpsea_func_analytics_enabled" value="no"/>
		  <label for="wpsea_func_analytics_enabled_no">No</label>
		<?php
	} else {
	?>
		<input type="radio" id="wpsea_func_analytics_enabled_yes" 
		name="wpsea_func_analytics_enabled" value="yes"/>
		<label for="wpsea_func_analytics_enabled_yes">Yes</label>
		<input type="radio" id="wpsea_func_analytics_enabled_no" 
		name="wpsea_func_analytics_enabled" checked="checked" value="no"/>
		<label for="wpsea_func_analytics_enabled_no">No</label>
	<?php
	}
}


/**
 * Render jquery enabled form on options page
 *
 * Long Description
 *
 * @since 0.7
 *
 * @param  type $name  it does something
 * @return type        it does something
*/
function wpsea_func_setting_jquery_enabled() {
	$use_jquery_enabled = get_option( 'wpsea_func_load_jquery_enabled' );

	if ( $use_jquery_enabled == 'yes' ) {
		?>
		<input type="radio" id="wpsea_func_load_jquery_enabled_yes" 
		name="wpsea_func_load_jquery_enabled" checked="checked" value="yes"/>
		<label for="wpsea_func_load_jquery_enabled_yes">Yes</label>
		<input type="radio" id="wpsea_func_load_jquery_enabled_no" 
		name="wpsea_func_load_jquery_enabled" value="no"/>
		<label for="wpsea_func_load_jquery_enabled_no">No</label>
		<?php
	} else {
	?>
		<input type="radio" id="wpsea_func_load_jquery_enabled_yes" 
		name="wpsea_func_load_jquery_enabled" value="yes"/>
		<label for="wpsea_func_load_jquery_enabled_yes">Yes</label>
		<input type="radio" id="wpsea_func_load_jquery_enabled_no" 
		name="wpsea_func_load_jquery_enabled" checked="checked" value="no"/>
		<label for="wpsea_func_load_jquery_enabled_no">No</label>
	<?php
	}
}


/**
 * Render the No Frames field for the Settings page
 *
 * @since 0.1
 *
 * @param  wp_options $wpsea_func_noframes_enabled
 * @return void
*/
function wpsea_func_setting_noframes_enabled() {

	$noframes_enabled = get_option( 'wpsea_func_noframes_enabled' );

	if ( $noframes_enabled == 'yes' ) {
		?>
		<input type="radio" id="wpsea_func_noframes_enabled_yes" 
		name="wpsea_func_noframes_enabled" checked="checked" value="yes"/>
		<label for="wpsea_func_noframes_enabled_yes">Yes</label>
		<input type="radio" id="wpsea_func_noframes_enabled_no" 
		name="wpsea_func_noframes_enabled" value="no"/>
		<label for="wpsea_func_noframes_enabled_no">No</label>
		<?php

	} else {
	?>
		<input type="radio" id="wpsea_func_noframes_enabled_yes" 
		name="wpsea_func_noframes_enabled" value="yes"/>
		<label for="wpsea_func_noframes_enabled_yes">Yes</label>
		<input type="radio" id="wpsea_func_noframes_enabled_no" 
		name="wpsea_func_noframes_enabled" checked="checked" value="no"/>
		<label for="wpsea_func_noframes_enabled_no">No</label>
	<?php
	}
}

/**
 * Render the Contact Send To field for the Settings page
 *
 * @since 0.1
 *
 * @param  wp_options $wpsea_func_contact_sendto
 * @return void
*/
function wpsea_func_setting_contact_sendto() {
	$contact_sendto = get_option( 'wpsea_func_contact_sendto' );

	?>
	<input id="wpsea_func_contact_sendto" name="wpsea_func_contact_sendto"
		type="text" value="<?php echo $contact_sendto; ?>" /> e.g. webmaster@yourdomain.com
	<?php
}

/**
 * Add the 'No Frames' functionality to the site-essential.js
 *
 * @return void
*/
function wpsea_func_noframes_append() {

	$filename = wpsea_func_get_filename();

	$home_url = get_bloginfo( 'url' );

	try {
		error_log( 'opening file for appending filename=' . $filename );
		$fh = fopen( $filename, 'a+' );
	} catch (Exception $e) {
		error_log("Tried to open the file but couldnt! " . $e->getMessage() );
	}
	if ( ! $fh ){
		die( 'Cannot write filename=' . $filename );
	}

	$no_frames_js = '
	// no frames
	//
	try {
		var parent_location = new String( parent.location );
		var top_location = new String( top.location );
		var cur_location = new String( document.location );

		parent_location = parent_location.toLowerCase();
		top_location = top_location.toLowerCase();
		cur_location = cur_location.toLowerCase();

		if ( ( top_location != cur_location )
		&& parent_location.indexOf("' . $home_url . '" ) != 0 ){
			top.location.href = document.location.href;
		}
	} catch ( err ) {
		top.location.href = document.location.href;
	}
	';

	fputs( $fh, $no_frames_js );
	fclose( $fh );
}

/**
 * Delete the JS file
 *
 * @since 0.8.2
 *
 * @return boolean
 */
function remove_js_file() {

	$js_file = wpsea_func_get_filename();
	if ( file_exists( $js_file ) ){
		if ( WP_DEBUG ){
			error_log('removing JS file. name=' . $js_file);
		}

		$deleted = unlink( $js_file );
		if ( WP_DEBUG ) {
			if ( $deleted ) {
				error_log( 'successfully deleted file=' . $js_file );
			} else {
				error_log( 'could not delete file=' . $js_file );
			}
		}
		return $deleted;
	}

}



/**
 * Render the Form for the Options page
 *
 * @since 0.1
 *
 * @return void
 */
function wpsea_func_options_page() {
	$_settings = wpsea_func_get_settings();
	$errors = array();

	if ( isset($_POST['wpsea_func_submit_button']) ) {
		update_option( 'wpsea_func_generator_enabled', $_POST['wpsea_func_generator_enabled'] );
		update_option( 'wpsea_func_feed_links_enabled', $_POST['wpsea_func_feed_links_enabled'] );
		update_option( 'wpsea_func_wlwmanifest_enabled', $_POST['wpsea_func_wlwmanifest_enabled'] );
		update_option( 'wpsea_func_rsd_enabled', $_POST['wpsea_func_rsd_enabled'] );

		update_option( 'wpsea_func_analytics_id', $_POST['wpsea_func_analytics_id'] );
		update_option( 'wpsea_func_analytics_enabled', $_POST['wpsea_func_analytics_enabled'] );
		update_option( 'wpsea_func_noframes_enabled', $_POST['wpsea_func_noframes_enabled'] );
		update_option( 'wpsea_func_load_jquery_enabled', $_POST['wpsea_func_load_jquery_enabled'] );
		update_option( 'wpsea_func_contact_sendto', $_POST['wpsea_func_contact_sendto'] );

		$check = array();
		$check['analytics_id'] = $_POST['wpsea_func_analytics_id'];
		$check['send_to']      = $_POST['wpsea_func_contact_sendto'];

		$errors = wpsea_func_form_validate( $check );

		remove_js_file();
		if ( 'yes' === $_POST['wpsea_func_noframes_enabled'] ) {
			error_log( 'REGENERATE!' );

			// Check for items that write to the JS.
			// Need to ensure that the JS file exists before enqueueing
			wpsea_func_noframes_append();
		}
	}

	?>
	<div class="wrap">
		<h2><?php echo $_settings['wpsea_func_page_title']; ?></h2>
		<div>This plugin helps you manage the small, common aspects of your website. </div>
		<?php if ( $errors ){
			?>
			<div class="errors-list">
			<?php
			foreach ( $errors AS $error_field => $error_message ){
				echo '<div class="error-messsage">' .  $error_message . '</div><br />';
			}
			?>
			</div>
		<?php } ?>
		<form action="" method="post" accept-charset="utf-8">
			<?php
			settings_fields( 'wpsea_func_options' );
			do_settings_sections( 'wpsea_func' );
			?>
			<input type="submit" class="button-primary" name="wpsea_func_submit_button"
			value="Save Changes" id="wpsea_func_submit_button">
		</form>
	</div>
	<?php
}


/**
 * Attach site_essential.js to the html page
 *
 * @return void
*/
function wpsea_func_load_js() {
	$in_footer    = false;

	$essential_js_url  = wpsea_func_get_filename(true);
	$essential_js_file = wpsea_func_get_filename();

	$dependencies = array();

	$noframes_enabled = get_option( 'wpsea_func_noframes_enabled' );
	if ( file_exists( $essential_js_file ) ) {
		wp_enqueue_script(
			'wpsea_func_main',
			$essential_js_url,
			$dependencies,
			WPSEA_FUNC_VERSION,
			$in_footer
		);
	}
}

/**
 * Display the Google Analytics in the theme
 * It needs to be displayed just before </body>.
 * To do so, the wp_footer hook is used.
 *
 * @since 0.1
 *
 * @param wp_option $wpsea_func_analytics_id
 * @return void
*/
function wpsea_func_analytics_js() {

	$analytics_id = get_option( 'wpsea_func_analytics_id' );

	?>
	<script type="text/javascript" class="wpsea_func">

	  var _gaq = _gaq || [];
	  _gaq.push( ['_setAccount', '<?php echo $analytics_id; ?>'] );
	  _gaq.push( ['_trackPageview'] );

	  ( function() {
		var ga = document.createElement( 'script' );
		ga.type = 'text/javascript';
		ga.async = true;
		ga.src = ( 'https:' == document.location.protocol
			? 'https://ssl' : 'http://www' ) + '.google-analytics.com/ga.js';

		var s = document.getElementsByTagName( 'script' )[0];
		s.parentNode.insertBefore( ga, s );
	  } )();

	</script>
	<?php
}

/**
 * Display the js to break your website out of a frameset.
 *
 * @since 0.1
 *
 * @return void
 */
function wpsea_func_killframes() {
	$home_url = strtolower( get_bloginfo( 'url' ) );
	?>
	<?php
}

/**
 * Add all widgets to the dashboard
*/
function wpsea_func_add_dashboard_widgets() {
	global $wp_meta_boxes;

	wp_add_dashboard_widget(
		'custom_help_widget',
		'Website Support',
		'wpsea_func_website_help'
	);
}

/**
 * Display Help to your Client 
*/
function wpsea_func_website_help() {
	?>
	<h4>Need help with your website?</h4>
	<p>Contact Company Name by email <strong>support@yourcompany.com</strong>
	or by phone <strong>206-555-1212</strong></p>
	<?php
}

/**
 * Removes some of the cruft on the Dashboard
*/
function example_remove_dashboard_widgets() {

	// Wordpress Updates
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );

	// Wordpress News
	remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );

	// Links from Google Blog Search
	remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
}

/**
 * Turns off the message to update wordpress
*/
function no_update_nag() {
	remove_action( 'admin_notices', 'update_nag', 3 );
}

/**
 * Contact form shortcode function
 *
 * @since 0.1
 *
 * @param  Array $attr it does something
 * @param  String $content optional. if present it'll be displayed above the form
 * @return void
*/
function wpsea_func_contact_form( $attr, $content = false ) {

	$errors = array();

	if ( isset( $_POST['submit_button'] ) ){
		if ( isset( $attr['send_to'] ) ) {
			$errors = wpsea_func_contactform_submit( $attr['send_to'] );
		} else {
			$errors = wpsea_func_contactform_submit();
		}
	} else {
?>
	<form id="wpsea-func-contact-form" class="contact" action="" method="post">

		<?php 
		if ( $content ) {
		?>
			<p><?php echo $content; ?></p>
		<?php
		}
		?>
		<?php
		if ( count( $errors ) > 0 ){
				echo '<div class="message error">';
				echo implode( '<br />', $errors );
				echo '</div>';
		}

		if ( isset( $attr['redirect_to'] ) ){
			?>
			<input type="hidden" id="redirect_to"  name="redirect_to"
			value="<?php echo $attr['redirect_to']; ?>" />
			<?php
		}

		$style = '';
		?>

		<div>
			<label for="sender_name" >Your Name</label>
			<input id="sender_name" class="text required" type="text"
			name="sender_name" value="<?php
			if ( isset( $_POST['sender_name'] ) ) {
				echo $_POST['sender_name'];
			} ?>" />
		</div>
		<div>
			<label for="sender_email" >Your Email</label>
			<input id="sender_email" class="text required"
			type="text" name="sender_email" value="<?php
			if ( isset( $_POST['sender_email'] ) ) {
				echo $_POST['sender_email'];
			} ?>"  />
		</div>
		<div>
			<label for="subject" >Subject</label>
			<input id="subject" class="text"  type="text" name="subject"
			value="<?php
			if ( isset( $_POST['subject'] ) ) {
				echo $_POST['subject'];
			} ?>" />
		</div>
		<div>
			<label for="message" >Message</label>
			<textarea id="message" class="required" name="message"
			rows="5" cols="40">
			<?php
			if ( isset( $_POST['message'] ) ) {
				echo $_POST['message'];
			}
			?></textarea>
		</div>
		<div>
			<input id="submit_button" type="submit" name="submit_button" value="Send Message " />
		</div>

	</form>
<?php
	}
}

/**
 * Take a guess at what the user's email address might be.
 *
 * @since 0.1
 *
 * @return String  an email address
*/
function wpsea_func_from_address() {
	$hostname = php_uname( 'n' );
	$username = get_current_user();

	return $username . '@' . $hostname;
}

/**
 * handle the submission of the contact form.
 *
 * @since 0.1
 *
 * @return void
*/
function wpsea_func_contactform_submit( $send_to = false ) {

	global $wpsea_func_errors;

	$to_address = get_option( 'wpsea_func_contact_sendto' );
	if ( $send_to ){
		// Use the designer-specified value in the shortcode 
		$to_address = $send_to;
	}


	if ( array_key_exists( 'submit_button', $_POST ) ) {
		$strip_regex = '~\\\\~';
		$_POST['message'] = preg_replace( $strip_regex, '', $_POST['message'] );

		$data = array();
		$data['sender_name']   = $_POST['sender_name'];
		$data['sender_email']  = $_POST['sender_email'];
		$data['subject']       = $_POST['subject'];
		$data['message']       = $_POST['message'];
		$data['submit_button'] = $_POST['submit_button'];

		if ( isset( $_POST['redirect_to'] ) ) {
			$data['redirect_to'] = $_POST['redirect_to'];
		}

		$errors = wpsea_func_form_validate( $data );

		if ( count( $errors ) == 0 ) {
			$from_address = wpsea_func_from_address();

			$subject = $data['subject'];
			$message = wordwrap($data['message'], 70, EOL_CR)
			. EOL_CR
			. EOL_CR
			. $data['sender_name'];

			if ( isset( $data['sender_email'] ) ) {
				$from_address = $data['sender_email'];

				$headers  = 'From: ' . $from_address . EOL_CRLF;
				$headers .= 'Reply-To: '  . $data['sender_email']  . EOL_CRLF;
			}

			$headers .= 'X-Mailer: PHP/' . phpversion();

			$sent = mail( $to_address, $subject, $message, $headers );
			if ( ! $sent ){
				error_log('mail to ' . $to_address . ' was NOT SENT!');
			} else {
				if ( WP_DEBUG ) {
					error_log('mail to ' . $to_address . ' SENT!');
				}
			}

			if ( isset( $data['redirect_to'] ) ){
				wp_redirect( $data['redirect_to'] );
			} else {
				wpsea_func_form_thank_you();
			}
		} else {
			$log_message = implode( '. ', $errors );
			error_log( 'contact form errors=' . $log_message );

			return $errors;
		}
	}
}

/**
 * Render a thank you page
 *
 * Display to the user that their form has been submitted successfully
 * and thank them for contacting you
 *
 * @since 1.0
 *
 * @param  String $message a designer customizable message
 * @return void
*/
function wpsea_func_form_thank_you( $message = false ) {

	if ( !$message ) {
		$message = 'Thank you for contacting us. You will hear from us shortly.';
	}

	?>
		<h2>Thank you</h2>
		<p>
			<?php echo $message; ?>
		</p>
	<?php
}


/**
 * Validate the contact form data.
 *
 * @since 0.1
 *
 * @param  array $data the contact form data that needs to be validated
 * @return array $errors it does something
*/
function wpsea_func_form_validate( $data ) {
	require_once "lib/class-wpsea-regex.php";

	$errors = array();
	$re = new Wpsea_Regex();

	$name_regex      = $re->get_name_regex();
	$subject_regex   = $re->get_subject_regex();
	$message_regex   = $re->get_message_regex();
	$empty_regex     = $re->get_empty_regex();
	$analytics_regex = $re->get_analytics_regex();

	if ( ! empty( $data['send_to'] ) ) {
		if ( ! is_email( $data['send_to'] ) ) {
			$errors['send_to'] = __(
				'Sorry. "' . $data['send_to']
				. '" does not look like an email address'
				, WPSEA_FUNC_TEXT_DOMAIN
			);
		}
	}

	/* GOOGLE ANALYTICS ID */
	if ( isset( $data['analytics_id'] ) ) {
		if (
			! preg_match( $empty_regex, $data['analytics_id'] ) &&
			! preg_match( $analytics_regex, $data['analytics_id'] ) ) {
				$errors['analytics_id'] = __(
					'hmmm ... your Google Analytics ID "'
					. $data['analytics_id'] . '" does not look right'
					, WPSEA_FUNC_TEXT_DOMAIN
				);
		}
	}

	if ( isset( $data['sender_name'] ) ) {
		if ( preg_match( $empty_regex, $data['sender_name'] ) ) {
			$errors['sender_name'] = __(
				'Your name is empty. Please tell us your name.'
				, WPSEA_FUNC_TEXT_DOMAIN
			);
		} else {
			if ( ! preg_match( $name_regex, $data['sender_name'] ) ) {
				$errors['sender_name'] = __(
					'Your name does not look like one.'
					. ' Please check it.'
					, WPSEA_FUNC_TEXT_DOMAIN
				);
			}
		}
	}

	if ( isset( $data['subject'] ) ) {
		if ( preg_match( $empty_regex, $data['subject'] ) ) {
			$errors['subject'] = __(
				'The subject is empty. Please tell us'
				. ' your message is about.'
					, WPSEA_FUNC_TEXT_DOMAIN
			);
		} else {
			if ( ! preg_match( $subject_regex, $data['subject'] ) ) {
				$errors['subject'] = __(
					'your subject has odd data.'
					. 'use letters and numbers.'
					, WPSEA_FUNC_TEXT_DOMAIN
				);
			}
		}
	}


	if ( isset( $data['sender_email'] ) ) {
		if ( ! is_email( $data['sender_email'] ) ) {
			$errors['sender_email'] = __( 'Your email address doesn\'t look right.' );
		}
	}

	if ( isset( $data['message'] ) ){
		if ( preg_match( $empty_regex, $data['message'] ) ) {
			$errors['message'] = __( 'Your message is empty. Did you forget?' );
		} else {
			if ( ! preg_match( $message_regex, $data['message'] ) ) {
				$errors['message'] = __(
					'your message has odd data. use letters, '
					. 'numbers, periods, commas, exclamation '
					. 'points, or question marks.'
					, WPSEA_FUNC_TEXT_DOMAIN
				);
			}
		}
	}

	return $errors;
}

/**
 * retrieve a filename for the file
 *
 * @param bool $use_url Determine if you want a url or a directory
 * @return String $file full file system path
 */
function wpsea_func_get_filename( $use_url = false ) {
	$filename = 'site_essential.js';

	if ( $use_url ){
		$url =  WPSEA_FUNC_PLUGIN_URL . $filename;
		if ( WP_DEBUG ){
			error_log( 'use_url=true url=' . $url );
		}
		return $url;
	} else {
		$file =  WPSEA_FUNC_PLUGIN_DIR  . $filename ;
		if ( WP_DEBUG ){
			error_log( 'use_url=false file=' . $file );
		}
		return $file;
	}
}

//---------------------------------
//   WIDGETS
//---------------------------------

function wpsea_popular_posts_sql($limit_count = 10){
	global $wpdb;

	$limit_count = intval( $limit_count );

	$popular = $wpdb->get_results(
		'SELECT id, post_title, comment_count '
		.' FROM ' . $wpdb->prefix . 'posts'
		." WHERE post_type='post' ORDER BY comment_count DESC LIMIT " . $limit_count
	);

	return $popular;
}


/**
 * display a widget of the most popular posts
 *
 * @since 0.1
 *
 * @param  array $args
 * @return void
 */
function wpsea_func_widget_popular_posts( $args ) {

	$popular = wpsea_popular_posts_sql();

	echo $args['before_widget'];
	echo $args['before_title'];?>Popular Posts<?php echo $args['after_title'];

	echo '<ul>';
	foreach( $popular as $post ) :
		$post_link = get_permalink( $post->id );
		echo ' <li><a href="' . $post_link .'">' . $post->post_title .  '</a></li>';
	endforeach;
	echo '</ul>';
	echo $args['after_widget'];
}


/**
 * display a widget of the latest posts
 *
 * @since 0.1
 *
 * @param  array $args
 * @return void
 */
function wpsea_func_widget_latest_post( $args ) {
	global $wpdb;

	echo $args['before_widget'];
	echo $args['before_title'];?>Latest Post<?php echo $args['after_title'];

	$query_args = array( 'posts_per_page' => 1, 'post_status' => 'publish' );
	$latest = new WP_Query( $query_args );

	if ( $latest->have_posts() ) :
		echo '<ul>';
		while ( $latest->have_posts() ) : $latest->the_post();
			$post_link = get_the_permalink();
			?><li><a href="<?php echo $post_link; ?>"><?php the_title(); ?></a></li>
		<?php
		endwhile;
		echo '</ul>';
		wp_reset_postdata();

	else : ?>
		<p>stay tuned for the next post</p>
	<?php
	endif;

	echo $args['after_widget'];
}


function wpsea_func_init() {
	$popular_title = __( 'Essential Popular Posts' );
	$latest_title  = __( 'Essential Latest Post' );

	wp_register_sidebar_widget( 'wpsea_func_widget_popular_posts_id', $popular_title, 'wpsea_func_widget_popular_posts' );
	wp_register_sidebar_widget( 'wpsea_func_widget_latest_post_id', $latest_title, 'wpsea_func_widget_latest_post' );
}

//---------------------------------
//   INTERFACES
//---------------------------------

interface DisplayOnlyWidget
{
	function widget();
}

interface DashboardWidget {
	function widget( $args, $instance );
	function update( $new_instance, $old_instance );
	function form( $instance );
}


require_once "lib/widget.popular-posts.php";
require_once "lib/widget.latest-posts.php";

function wpsea_register_widgets() {
		register_widget( 'WpseaPopularPosts' );
		register_widget( 'WpseaLatestPosts' );
}

add_action( 'widgets_init', 'wpsea_register_widgets' );


//=================================================================
//						   END OF PLUGIN
//=================================================================

