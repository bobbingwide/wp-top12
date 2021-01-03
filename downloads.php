<?php
/**
 * @copyright (C) Copyright Bobbing Wide 2015-2021
 * @package wp-top12
 * Syntax: oikwp downloads.php process
 *
 * Count the downloads from wordpress.org for the given plugins
 *
 * Run this once a month before the WordPress Portsmouth Meetup.
 *
 * {@link https://dd32.id.au/projects/wordpressorg-plugin-information-api-docs}
 * {@link http://code.tutsplus.com/tutorials/communicating-with-the-wordpress-org-plugin-api--wp-33069}

 * Test output created during the coding of this routine are called wahay(n) where n starts from 2
 *
 */
ini_set('memory_limit','1024M');

require_once( ABSPATH . "wp-admin/includes/plugin-install.php" );
//oik_require( "includes/oik-remote.inc" );
oik_require_lib( "class-oik-remote" );
oik_require_lib( 'oik-blocks');

oik_require( "libs/class-wp-org-downloads.php", "wp-top12" );
oik_require( "libs/class-object-sorter.php", "wp-top12" );
oik_require( "libs/class-object.php", "wp-top12" );
oik_require( "libs/class-object-grouper.php", "wp-top12" );
oik_require( 'libs/class-csv-merger.php', 'wp-top12');


/**
 * Usage suggestions:
 * - Pass the name of the process you want to run to the routine.
 * - Only do the download once a month or so. It takes about 12-15 minutes to run.
 * - Then run reports.
 * - This creates a new wporg_plugins.csv
 *
 * oikwp downloads.php v2
 * oikwp downloads.php reports
 *
 * oikwp downloads.php plugin

 */
//query_my_plugins();

$process = oik_batch_query_value_from_argv( 1, null );
$process = strtolower( trim( $process ));
switch ( $process ) {
	case 'download':
	case 'v2':
		downloads_v2();
		break;

	case 'reports':
	case 'rv2':
		reports();
		break;

	// Blocks not available with v2 of the REST API but they are with the WordPress blocks API v1.2.
	case 'blocks':
		block_plugins();
		break;

	case 'rb':
		report_blocks();
		break;

	default:
		if ( $process ) {
			plugin_info_v2( $process );
			plugin_info_v12( $process );
		} else {
			echo "Syntax TBC";
		}
}



/**
 * Lists all plugins from wordpress.org
 *
 */
function downloads_v2() {
	$wpod = new WP_org_downloads();
	$wpod->query_all_plugins_v2();
}

/**
 * Produce reports for all plugins in wordpress.org
 */
function reports() {
	$wpod = new WP_org_downloads();
	$loaded = $wpod->load_all_plugins();
	$wpod->top1000( null );
	$wpod->summarise();
	$wpod->report_top1000( 100 );
	$wpod->count_things();
	//$wpod->list_block_plugins();
}

/**
 *
 */
function block_plugins() {
	oik_require( "class-wp-org-v12-downloads.php", "wp-top12" );
	$wpod = new WP_org_v12_downloads();
	$letters = 'abcdefghijklmnopqrstuvwxyz';
	for  ( $i = 0; $i < strlen( $letters); $i++ ) {
		$wpod->query_all_plugins_v12( 1, 100, $letters[$i] );
	}
	//$loaded = $wpod->load_all_plugins();
	//$wpod->list_block_plugins();
}

function report_blocks() {
	oik_require( "class-wp-org-v12-downloads.php", "wp-top12" );
	$wpod = new WP_org_v12_downloads();
	$wpod->load_all_plugins();

}

function plugin_info_v2( $plugin ) {
	$sorted = file( 'wporg_plugins.csv');
	$total = count( $sorted );
	$total--;
	echo $total;
	echo PHP_EOL;

	foreach ( $sorted as $key => $info ) {

		$pos = stripos( $info, $plugin  );
		if ( false !== $pos ) {
			echo "$key,";
			echo $info;
			//echo PHP_EOL;

		}
	}
}


/**
 * Returns the prefix used for blocks created by this plugin.
 * To be used in blocker.
 * @param $plugin
 */

function plugin_info_v12( $plugin ) {
	oik_require( "class-wp-org-v12-downloads.php", "wp-top12" );
	$wpod = new WP_org_v12_downloads();
	$wpod->get_download( $plugin );
	$prefix = $wpod->get_block_prefix();
	echo "Prefix: " . $prefix;
	echo PHP_EOL;

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


