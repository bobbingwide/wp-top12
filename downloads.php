<?php // (C) Copyright Bobbing Wide 2015, 2016, 2019

/**
 * Syntax: oikwp downloads.php process
 *
 * Count the downloads from wordpress.org for the given plugins
 *
 * Run this daily!
 *
 * {@link https://dd32.id.au/projects/wordpressorg-plugin-information-api-docs}
 * {@link http://code.tutsplus.com/tutorials/communicating-with-the-wordpress-org-plugin-api--wp-33069}

 * Test output created during the coding of this routine are called wahay(n) where n starts from 2
 *
 */
ini_set('memory_limit','512M');

require_once( ABSPATH . "wp-admin/includes/plugin-install.php" );
oik_require( "includes/oik-remote.inc" );

oik_require( "class-wp-org-downloads.php", "wp-top12" );
oik_require( "class-object-sorter.php", "wp-top12" );
oik_require( "class-object.php", "wp-top12" );
oik_require( "class-object-grouper.php", "wp-top12" );


/**
 * Comment out the logic you don't want to run and uncomment that which you do.
 * Downloads takes a long time. So normally it's commented out and we just run reports
 * from the downloaded files.
 */
//query_my_plugins();

//downloads();

reports();



/**
 * Do fancy things for top-10-wp-plugins.com
 */
function downloads() {

	$wpod = new WP_org_downloads();
	//$wpod->query_plugins( 1 );
	//$wpod->save_plugins( 1 );
	//gob();
	$loaded = false;
	if ( !$loaded ) {
		//gob();
		$wpod->query_all_plugins();
		// $wpod->save_plugins();
		$wpod->report_info();
	}

}

function reports() {

	$wpod = new WP_org_downloads();
	$loaded = $wpod->load_all_plugins();

	$wpod->summarise();
	$wpod->top1000();
	$wpod->count_things();
}


/**
 * Query the counts for oik plugins
 * query_my_plugins();
 */

function query_my_plugins( $wpod ) {
	$plugins = bw_as_array( "oik,oik-nivo-slider,oik-privacy-policy,oik-weightcountry-shipping,cookie-cat,oik-read-more,oik-bwtrace,bbboing,oik-batchmove,uk-tides,oik-css" );
	echo "There are: " . count( $plugins ) . PHP_EOL;
	foreach ( $plugins as $plugin ) {
		$wpod->get_download( $plugin );
		$count = $wpod->get_download_count();
		echo "$plugin $count" . PHP_EOL;
	}
}


/**
 * https://api.wordpress.org/stats/plugin/1.0/oik
 *
 * Returns JSON array of versions being run
 *
 */
function get_plugin_stats( $plugin_slug ) {
	gobang();
}


