<?php 
/*
 * Plugin Name: GForms ProSite Trial Registration
 * Plugin URI: https://hostmijnpagina.nl/
 * Description: Allows users to register user for trial 14days - for the first plan in your table
 * Author: Sybre Waaijer & SqueakyOx
 * Version: 0.0.1
 * Version Base: 1.2.0
 * Author URI: https://cyberwire.nl/
 */
 
 // sorry, no tutorial included - This one is also much simpler than the upgrade/extend/downgrade function as you can see :)
 // for users whom have installed this before, only line 43 has been updated to prevent SQL injections. Replace it with your own.
 // Also, switch_to_blog and restore_current_blog has been removed because they had no purpose.
 
 /* IMPORTANT: This trial code will not work with the original registration plugin!
  * This code is based on Sybre Waaijer's code with some editing. Thanks again for sharing Sybre!
  * Also you will need to remap field ID's if required.
  */
 
 /*
  * Update 1.1.0 : Preparing for ProSite Upgrade/Downgrade/Extend
  * Update 1.2.0 : Fixed critical bug when the fields below are split over several pages.
  *
  */
 
//* Gforms Pro Sites Creation
function enter_pro_site_level($site_id, $user_id, $entry, $config, $user_pass) {
	global $wpdb,$psts;
	
	$pstslevel =  '1'; // Preset prolevel to 1, where zero is free but can be disabled
	
	//Setting Trial Days and time
	$getperiod = 	'14days';
	$pststime = strtotime("+14 days");
	$pststimelastpayment = 'Trial'; 
	
	/* new since 1.1.0 */
	/* upgraded option = termtime level */
	$upgraded_option = $pststimelastpayment . $pstslevel;
	
	$pro_blog_option_upgrade = 'pro_site_last_payment';
	add_blog_option($site_id, $pro_blog_option_upgrade, $upgraded_option);
	
	$pstsgateway = 'GFormRegister';
	$pststerm = '';
	$pstsamount = '';
	
	$nowplusonehour = time() + 3600; /* new since 1.2.0 */

	if(!empty($site_id)){
		if ( !empty($getperiod) || $pststime > $nowplusonehour ) { /* new since 1.2.0 */
			$update_level = $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->base_prefix}pro_sites (blog_ID, level, expire, gateway, term) VALUES (%d, %d, %d, %s, %s)", $site_id, $pstslevel, $pststime, $pstsgateway, $pststerm));
		}
	}

	$psts->record_stat($site_id, 'upgrade');
	if ( !empty($getperiod) || $pststime > $nowplusonehour ) { /* new since 1.2.0 */
		$psts->log_action($site_id, __("GFormsRegister changed Pro-Sites level to Level ID {$pstslevel} with site_option ({$upgraded_option}).") );
	}
}
add_action("gform_site_created", "enter_pro_site_level", 10, 5);