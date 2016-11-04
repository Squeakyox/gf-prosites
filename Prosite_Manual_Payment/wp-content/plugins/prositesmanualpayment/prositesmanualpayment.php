<?php 
/*
 * Plugin Name: ProSite Gform - Manual Purchase
 * Plugin URI: http://www.squeakyox.com
 * Description: Allows your users to manual purchase using Pro Sites and Gravity Forms 
 * Author: Sybre Waaijer & SqueakyOx
 * Version: 0.0.4
 * Base Version: 1.1.1 of prosite upgrade by Syber Waaijer
 * Author URI: http://www.squeakyox.com
 */

/*	UPDATES 
	
	0.0.4: Fixed bug when pulling gravity forms due to CSS layout error. Have edited for fixes (works with 2.1.0.1 of gravity forms)
	0.0.3: Fixed bug in prepopulate that affect rendering of other forms as well & removal of extra non-required variable that may cause errors.
	0.0.2: Redirect to home_url/login when user is not logged in
	0.0.1: Code rehash for other purpose - 3 forms (1 x order, 1 x payment confirmation, 1 x auto populate). Also include custom currency

*/


/*

	SHORTCODE SECTION!!
*/


add_action( 'init', 'prosite_manual_payment_shortcode' );
function prosite_manual_payment_shortcode() {
	add_shortcode( 'temp_purchase', 'temp_purchase_shortcode');
	add_shortcode( 'redirect_purchase', 'redirect_purchase_shortcode');
	add_shortcode( 'redirect_confirm', 'redirect_confirm_shortcode');
	add_shortcode( 'redirect_auto', 'redirect_auto_shortcode');
}

function temp_purchase_shortcode() {
	if (!is_user_logged_in() ) {
		wp_redirect(home_url('/login')); exit; //Added in v0.0.2
	} else if (!is_super_admin()) {
		wp_redirect(home_url('/purchase/'));
		exit;
	}
}

function redirect_purchase_shortcode() {
	global $wpdb,$psts;
	if ( is_user_logged_in() ) {
		$myformid = '14'; //EDIT HERE
	
		$user_arg = wp_get_current_user();
		$user_id = $user_arg->ID;
		$user_blog_id = get_user_meta($user_id, 'primary_blog', true);
		$user_is_admin = current_user_can_for_blog($user_blog_id, 'edit_pages');
		
		$protimesql = $wpdb->get_var($wpdb->prepare("SELECT expire FROM {$wpdb->base_prefix}pro_sites WHERE blog_ID = %d", $user_blog_id));
		$max_1_year = time() + 63115200; /* 2 years in seconds, the maximum allowed time that a user is able to edit his subscription - NOTE: this will redirect the user after form submission if his subscription ends beyond 2 years */
		/* $max_1_year = 9999999999; */ /*Uncomment this if you basically want people to extend forever, lol (the line above is allowed to stay, this will overwrite it - might throw a notice through) */
		
		if ($protimesql < $max_1_year || is_super_admin() && $user_is_admin) {			
			wp_enqueue_style( 'pro-manual', plugins_url( 'css/pro-manual.css', __FILE__ ), false, '1.1.0' );
			
			$mapped_domain = $wpdb->get_var($wpdb->prepare("SELECT domain FROM {$wpdb->base_prefix}domain_mapping WHERE blog_id = %d", $user_blog_id));
			$user_blog_url = get_blogaddress_by_id( $user_blog_id );
			$prolevelsql = $wpdb->get_var($wpdb->prepare("SELECT level FROM {$wpdb->base_prefix}pro_sites WHERE blog_ID = %d", $user_blog_id));
			$prositelevelname = $psts->get_level_setting( $psts->get_level( $user_blog_id ), 'name' );
		
			$user_mapped_url = $mapped_domain ? $mapped_domain : $user_blog_url;
			$user_mapped_url = str_replace( array( 'http://', 'https://' ), '', $user_mapped_url );
			$user_mapped_url = str_replace( '/','', $user_mapped_url);
			
			$stripped_user_blog_url = str_replace (array( 'http://', 'https://'), '', $user_blog_url );
			$stripped_user_blog_url = str_replace ( '/', '', $stripped_user_blog_url );
	
			if ($prolevelsql == 0) {
				$prositeleveltime = "Undetermined";
			} else {
				$prositeleveltime = date_i18n('d F, Y', $protimesql); /* exchange d and F if you're a weird American, example: ('F d, Y', $protimesql) */ 
			}
			
			echo '<h2 class="upgradetitle">Purchase for Site: ' . $user_mapped_url . '</h2>'; /* Change the title if you'd like, '<h2 ... Site ' and '</h2>' allows HTML changes, keep the '' */
			echo '<ul class="prositesupgrade">';
			echo '<li class="prositecurrentlevel">Current Pro Site level: ' . $prositelevelname . '</li>'; /* Change the title if you'd like, '<li ... vel: ' and '</li>' allows HTML changes, keep the '' */
			echo '<li class="prositecurrenttime">Expires on: ' . $prositeleveltime . '</li>'; /* Change the title if you'd like, '<li on: ' and '</li>' allows HTML changes, keep the '' */
			echo '<li class="prositecurrentlevel">Base User Blog URL: ' . $stripped_user_blog_url . '</li>'; /* Change the title if you'd like, '<li ... vel: ' and '</li>' allows HTML changes, keep the '' */
			echo '<li class="prositecurrenttime">Mapped User Blog URL: ' . $user_mapped_url . '</li>'; /* Change the title if you'd like, '<li on: ' and '</li>' allows HTML changes, keep the '' */
			echo '</ul>';
			echo '<div class="prosite_clearfix"></div>';
			echo do_shortcode( '[gravityform id="' . $myformid . '" title="false" description="false"]' );
		} else {
			wp_redirect(home_url()); exit;
		}
	} else {
		wp_redirect(home_url('/login')); exit; /*Added in v0.0.2 */
	}
}

function redirect_confirm_shortcode() {
	if (!is_user_logged_in() ) {
		wp_redirect(home_url('/login')); exit; //Added in v0.0.2
	} else if (!is_super_admin()) {
		display_payment_gform() ;
	}
}

function display_payment_gform () {
	global $wpdb,$psts;
	if ( is_user_logged_in() ) {
		$myformid = '15'; /* Your form ID here */
	
		$user_arg = wp_get_current_user();
		$user_id = $user_arg->ID;
		$user_blog_id = get_user_meta($user_id, 'primary_blog', true);
		$user_is_admin = current_user_can_for_blog($user_blog_id, 'edit_pages');
		
		$protimesql = $wpdb->get_var($wpdb->prepare("SELECT expire FROM {$wpdb->base_prefix}pro_sites WHERE blog_ID = %d", $user_blog_id));
		$max_1_year = time() + 63115200; /* 2 years in seconds, the maximum allowed time that a user is able to edit his subscription - NOTE: this will redirect the user after form submission if his subscription ends beyond 2 years */
		/* $max_1_year = 9999999999; */ /*Uncomment this if you basically want people to extend forever, lol (the line above is allowed to stay, this will overwrite it - might throw a notice through) */
		
		if ($protimesql < $max_1_year || is_super_admin() && $user_is_admin) {			
			wp_enqueue_style( 'pro-manual', plugins_url( 'css/pro-manual.css', __FILE__ ), false, '1.1.0' );
			
			$mapped_domain = $wpdb->get_var($wpdb->prepare("SELECT domain FROM {$wpdb->base_prefix}domain_mapping WHERE blog_id = %d", $user_blog_id));
			$user_blog_url = get_blogaddress_by_id( $user_blog_id );
			$prolevelsql = $wpdb->get_var($wpdb->prepare("SELECT level FROM {$wpdb->base_prefix}pro_sites WHERE blog_ID = %d", $user_blog_id));
			$prositelevelname = $psts->get_level_setting( $psts->get_level( $user_blog_id ), 'name' );
		
			$user_mapped_url = $mapped_domain ? $mapped_domain : $user_blog_url;
			$user_mapped_url = str_replace( array( 'http://', 'https://' ), '', $user_mapped_url );
			$user_mapped_url = str_replace( '/','', $user_mapped_url);
			
			$stripped_user_blog_url = str_replace (array( 'http://', 'https://'), '', $user_blog_url );
			$stripped_user_blog_url = str_replace ( '/', '', $stripped_user_blog_url );
	
			if ($prolevelsql == 0) {
				$prositeleveltime = "Undetermined";
			} else {
				$prositeleveltime = date_i18n('d F, Y', $protimesql); /* exchange d and F if you're a weird American, example: ('F d, Y', $protimesql) */ 
			}
			
			echo '<h2 class="upgradetitle">Payment Confirmation: ' . $user_mapped_url . '</h2>'; /* Change the title if you'd like, '<h2 ... Site ' and '</h2>' allows HTML changes, keep the '' */
			echo '<ul class="prositesupgrade">';
			echo '<li class="prositecurrentlevel">Current Pro Site level: ' . $prositelevelname . '</li>'; /* Change the title if you'd like, '<li ... vel: ' and '</li>' allows HTML changes, keep the '' */
			echo '<li class="prositecurrenttime">Expires on: ' . $prositeleveltime . '</li>'; /* Change the title if you'd like, '<li on: ' and '</li>' allows HTML changes, keep the '' */
			echo '<li class="prositecurrentlevel">Base User Blog URL: ' . $stripped_user_blog_url . '</li>'; /* Change the title if you'd like, '<li ... vel: ' and '</li>' allows HTML changes, keep the '' */
			echo '<li class="prositecurrenttime">Mapped User Blog URL: ' . $user_mapped_url . '</li>'; /* Change the title if you'd like, '<li on: ' and '</li>' allows HTML changes, keep the '' */
			echo '</ul>';
			echo '<div class="prosite_clearfix"></div>';
			echo do_shortcode( '[gravityform id="' . $myformid . '" title="false" description="false"]' );
		} else {
			wp_redirect(home_url()); exit;
		}
	} else {
		wp_redirect(home_url('/login')); exit; /*Added in v0.0.2 */
	}
}

function redirect_auto_shortcode() {
	if (!is_user_logged_in() ) {
		wp_redirect(home_url('/login')); exit; //Added in v0.0.2
	} else if (!is_super_admin()) {
		display_auto_gform() ;
	}
}

function display_auto_gform () {
	global $wpdb,$psts;
	if ( is_user_logged_in() ) {
		$myformid = '16'; /* Your form ID here */
	
		$user_arg = wp_get_current_user();
		$user_id = $user_arg->ID;
		$user_blog_id = get_user_meta($user_id, 'primary_blog', true);
		$user_is_admin = current_user_can_for_blog($user_blog_id, 'edit_pages');
		
		$protimesql = $wpdb->get_var($wpdb->prepare("SELECT expire FROM {$wpdb->base_prefix}pro_sites WHERE blog_ID = %d", $user_blog_id));
		$max_1_year = time() + 63115200; /* 2 years in seconds, the maximum allowed time that a user is able to edit his subscription - NOTE: this will redirect the user after form submission if his subscription ends beyond 2 years */
		/* $max_1_year = 9999999999; */ /*Uncomment this if you basically want people to extend forever, lol (the line above is allowed to stay, this will overwrite it - might throw a notice through) */
		
		if ($protimesql < $max_1_year || is_super_admin() && $user_is_admin) {			
			wp_enqueue_style( 'pro-manual', plugins_url( 'css/pro-manual.css', __FILE__ ), false, '1.1.0' );
			
			$mapped_domain = $wpdb->get_var($wpdb->prepare("SELECT domain FROM {$wpdb->base_prefix}domain_mapping WHERE blog_id = %d", $user_blog_id));
			$user_blog_url = get_blogaddress_by_id( $user_blog_id );
			$prolevelsql = $wpdb->get_var($wpdb->prepare("SELECT level FROM {$wpdb->base_prefix}pro_sites WHERE blog_ID = %d", $user_blog_id));
			$prositelevelname = $psts->get_level_setting( $psts->get_level( $user_blog_id ), 'name' );
		
			$user_mapped_url = $mapped_domain ? $mapped_domain : $user_blog_url;
			$user_mapped_url = str_replace( array( 'http://', 'https://' ), '', $user_mapped_url );
			$user_mapped_url = str_replace( '/','', $user_mapped_url);
			
			$stripped_user_blog_url = str_replace (array( 'http://', 'https://'), '', $user_blog_url );
			$stripped_user_blog_url = str_replace ( '/', '', $stripped_user_blog_url );
	
			if ($prolevelsql == 0) {
				$prositeleveltime = "Undetermined";
			} else {
				$prositeleveltime = date_i18n('d F, Y', $protimesql); /* exchange d and F if you're a weird American, example: ('F d, Y', $protimesql) */ 
			}
			
			echo '<h2 class="upgradetitle">Domain: ' . $user_mapped_url . '</h2>'; /* Change the title if you'd like, '<h2 ... Site ' and '</h2>' allows HTML changes, keep the '' */
			echo '<ul class="prositesupgrade">';
			echo '<li class="prositecurrentlevel">Current Pro Site level: ' . $prositelevelname . '</li>'; /* Change the title if you'd like, '<li ... vel: ' and '</li>' allows HTML changes, keep the '' */
			echo '<li class="prositecurrenttime">Expires on: ' . $prositeleveltime . '</li>'; /* Change the title if you'd like, '<li on: ' and '</li>' allows HTML changes, keep the '' */
			echo '<li class="prositecurrentlevel">Base User Blog URL: ' . $stripped_user_blog_url . '</li>'; /* Change the title if you'd like, '<li ... vel: ' and '</li>' allows HTML changes, keep the '' */
			echo '<li class="prositecurrenttime">Mapped User Blog URL: ' . $user_mapped_url . '</li>'; /* Change the title if you'd like, '<li on: ' and '</li>' allows HTML changes, keep the '' */
			echo '</ul>';
			echo '<div class="domain_checker"> Domain Checker:'. do_shortcode( '[wpdomainchecker]') . '</div>';
			echo '<div class="prosite_clearfix"></div>';
			echo do_shortcode( '[gravityform id="' . $myformid . '" title="false" description="false"]' );
		} else {
			wp_redirect(home_url()); exit;
		}
	} else {
		wp_redirect(home_url('/login')); exit; /*Added in v0.0.2 */
	}
}

/*

	PARSE INFO SECTION
	

*/

add_filter("gform_field_input", "prepopulate_the_fields", 10, 5);
function prepopulate_the_fields($input, $field, $value, $lead_id, $form_id){
	global $wpdb;
	
	$purchaseform_id = '14';
	$confirmform_id = '15';
	$autoform_id = '16';
	
	
	if ($form_id == $purchaseform_id || $form_id == $confirmform_id || $form_id == $autoform_id) {
		$levelfield_id = '1';
		$emailfield_id = '2';
		$blogurlfield_id = '3';
		$mappedurlfield_id = '4';
		$userfield_id = '5';
		
		$user_arg = wp_get_current_user();
		$user_id = $user_arg->ID;
		$user_email = $user_arg->user_email;
		$user_login = $user_arg->user_login;
		$user_blog_id = get_user_meta($user_id, 'primary_blog', true);

		$currentprositelevel = $wpdb->get_var($wpdb->prepare("SELECT level FROM {$wpdb->base_prefix}pro_sites WHERE blog_ID = %d", $user_blog_id));
		$currentprositetime = $wpdb->get_var($wpdb->prepare("SELECT expire FROM {$wpdb->base_prefix}pro_sites WHERE blog_ID = %d", $user_blog_id));
		$user_blog_url = get_blogaddress_by_id( $user_blog_id );
		$mapped_domain = $wpdb->get_var($wpdb->prepare("SELECT domain FROM {$wpdb->base_prefix}domain_mapping WHERE blog_id = %d", $user_blog_id));
		
						
		if ($field["id"] == $levelfield_id) {
			$input = '<input name="input_' . $levelfield_id . '" id="input_' . $levelfield_id . '" type="hidden"  class="gform_hidden" value="' . $currentprositelevel . '">';
		}
		if ($field["id"] == $emailfield_id) {
			$input = '<input name="input_' . $emailfield_id . '" id="input_' . $emailfield_id . '" type="hidden"  class="gform_hidden" value="' . $user_email . '">';
		}
		if ($field["id"] == $blogurlfield_id) {
			$input = '<input name="input_' . $blogurlfield_id . '" id="input_' . $blogurlfield_id . '" type="hidden"  class="gform_hidden" value="' . $user_blog_url . '">';
		}
		if ($field["id"] == $mappedurlfield_id) {
			$input = '<input name="input_' . $mappedurlfield_id . '" id="input_' . $mappedurlfield_id . '" type="hidden"  class="gform_hidden" value="' . $mapped_domain . '">';
		}
		if ($field["id"] == $userfield_id) {
			$input = '<input name="input_' . $userfield_id . '" id="input_' . $userfield_id . '" type="hidden"  class="gform_hidden" value="' . $user_login . '">';
		}
		
		return $input;
	}
}

/*

	CUSTOM CURRENCY SECTION - DELETE IF NOT REQUIRED
	
*/

add_action('gform_currencies', 'add_idr_currency');
function add_idr_currency( $currencies ) {
    $currencies['IDR'] = array(
        'name'               => __( 'Indonesia Rupiah', 'gravityforms' ),
        'symbol_left'        => 'Rp',
        'symbol_right'       => '',
        'symbol_padding'     => ' ',
        'thousand_separator' => '.',
        'decimal_separator'  => '',
        'decimals'           => 0
    );

    return $currencies;
}
