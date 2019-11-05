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

$process = oik_batch_query_value_from_argv( 1, null );
switch ( strtolower( trim ( $process ) ) ) {
	case 'download':
		downloads();
		break;

	case 'reports':
		reports();
		break;


	case 'blocks':
		block_plugins();
		break;

	default:
		if ( $process ) {
			plugin_info( $process );
		} else {
			echo "Syntax";
		}
}


//downloads();

//reports();

//block_plugins();



/**
 * Do fancy things for top-10-wp-plugins.com
 */
function downloads() {

	$wpod = new WP_org_downloads();
	$wpod->query_plugins( 1 );
	print_r( $wpod );
	gob();
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
	$wpod->list_block_plugins();
}

function block_plugins() {
	$wpod = new WP_org_downloads();
	$loaded = $wpod->load_all_plugins();
	$wpod->list_block_plugins();
}


function plugin_info( $plugin ) {
	$wpod = new WP_org_downloads();
	$loaded = $wpod->load_all_plugins();
	$sorted = $wpod->sort_by_most_downloaded( null );
	$wpod->report_top1000( $sorted );
	foreach ( $sorted as $key => $plugin_data ) {
		if ( $plugin_data['slug'] == $plugin ) {
			echo $key;
			gob();
		}
	}
	//print_r( $sorted );

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


