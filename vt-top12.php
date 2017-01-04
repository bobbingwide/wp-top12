<?php // (C) Copyright Bobbingwide 2015, 2016
/**
 * Syntax: oikwp vt-top12.php process
 * 
 * @TODO Support invocation using parameters
 * 
 */
ini_set('memory_limit','1024M');

//require_once( ABSPATH . "wp-admin/includes/plugin-install.php" );
//oik_require( "includes/oik-remote.inc" );

//oik_require( "class-wp-org-downloads.php", "play" );

$plugin = "wp-top12";

oik_require( "class-vt-stats.php", $plugin );
oik_require( "class-vt-stats-top12.php", $plugin );

oik_require( "class-vt-row.php", $plugin );

oik_require( "class-object-sorter.php", $plugin );
oik_require( "class-object.php", $plugin );
oik_require( "class-object-grouper.php", $plugin );

oik_require( "class-CSV-merger.php", $plugin );

oik_require( "class-group-summary.php", $plugin );


//query_my_plugins();

//downloads();


//	 "statusquo", 					"jetpack", "jetpack-again",

// $files = array( "jetpackx3", "jetpack-opcache", "vanilla", "jp382", "jp382-5" );

//$files = array( "vanilla", "jp382-5", "noedd", "no-eddjp", "minus10" );
$files = array( "nofooter", "minus10", "noedd", "vanilla", "awj", "cf7" );

$files = array( "vanilla-essence", "akismet", "cf7", "ai1seo", "wpseo", "jetpack", "xml", "nextgen", "importer", "woocommerce", "analytics" );


$files = array( "client", "server" );


$files = array( "vanilla7", "vanilla71", "vanilla72", "awj7" );

$files = array( "vanilla7", "sfence", "cfence", "sfence2", "cfence2", "sfence3", "cfence3" );

$files = array( "vanilla", "vanilla02", "vanilla03", "vanilla-essence", "vanilla7", "vanilla71", "vanilla72",
								"vanilla43", "vanilla431", "vanilla432", "vanilla433", "vanilla434", "vanilla435", "vanilla436", "vanilla76", "vanilla4361" );
								
$files = array( "vanilla7", "vanilla76", "vanilla4361", "vanilla4362" );

$files = array( "vanilla7", "ssuper", "csuper", "ssuper2", "csuper2", "scacheuk7", "scache7com", "scache71com", "ccache71com"  );

$files = array( "vanilla", "vanilla5", "akismet", "sitemaps", "importer" );

/*
process_files( $files );

$files = array( "vanilla5", "cf7", "ai1seo", "yoastseo", "jetpack", "nextgen", "woocommerce", "wordfence", "analytics" );

process_files( $files );

$files = array( "vanilla5", "sfence2", "cfence2", "ssuper", "csuper" );

process_files( $files );


$files = array( "vanilla", "nofooter", "gfw", "yoastseo", "genesistant", "oik-widget-cache", "noclone" );

process_files( $files, "201602" );



$files = array( "vanilla", "gfw", "fewerdbs", "noclone" );

process_files( $files, "20160224" );
*/

$files = array( "vanilla", "wp46-initial", "vanilla-34" );

// vanilla-34-kd = Kaspersky Disabled after restarting apache server 
//	"vanilla"
// "vanilla-34", "vanilla-34-kd", "vanilla-34-kd-2", "vanilla-35-owc",   
// "vanilla-wp44-owc", "vanilla-wp44", "vanilla-wp44-owc-2", "vanilla-wp44-issue-15-2", 
// "vanilla-wp47",
//process_files( $files, "20161224" );


$groups = array( "wp44" => array( "vanilla-wp44-issue-15-5", "vanilla-wp44-1", "vanilla-wp44-2", "vanilla-wp44-3", "vanilla-wp44-4" )
							, "wp45" => array( "vanilla-wp453", "vanilla-wp453-1", "vanilla-wp453-2", "vanilla-wp453-3", "vanilla-wp453-4" )
							, "wp46" => array( "vanilla-wp461", "vanilla-wp461-1", "vanilla-wp461-2", "vanilla-wp461-3", "vanilla-wp461-4" )
							, "wp47" => array( "vanilla-wp47", "vanilla-wp47-1", "vanilla-wp47-2", "vanilla-wp47-3", "vanilla-wp47-4" )
							, "wpnr" => array( "vanilla-nr-1", "vanilla-nr-2" )
							, "wpnc" => array( "vanilla-nc-1" )
							);

process_groups( $groups, "20161224" );


/**
 * Process the selected set of groups
 * 
 * @param array $groups - array of file sets ( excluding the .csv extension )
 * @param string $host - directory for files
 */

function process_groups( $groups, $host="2016" ) { 
																											 
	$merger = new CSV_merger();
	$summary = new Group_Summary();

	foreach ( $groups as $key => $group ) {
		$stats = new VT_stats_top12();
		$stats->load_group( $group, $host );
		$grouper = $stats->count_things();
		//$grouper = $stats->count_things_by_queries();
		
		$grouper->report_total();
		$summary->add_group( $key, $grouper->total, $grouper->total_time ); 
		
		//$merger->append( $grouper->elapsed );
		$merger->append( $grouper->percentages );
		//$merger->append( $grouper->groups );
	}
	$merger->report_count();
	echo "Elapsed," . implode( array_keys( $groups ), "," ) . PHP_EOL;
	$merger->sort();
	
	$merger->report(); 
	$merger->accum();
	
	echo "Accum," . implode( array_keys( $groups ), "," ) . PHP_EOL;
	$merger->report_accum();
	
	$summary->report();
}

/**
 * Process the selected set of files
 * 
 * @param array $files - array of file names ( excluding the .csv extension )
 * @param string $host - directory for files
 */
function process_files( $files, $host="2016" ) { 
	$merger = new CSV_merger();
	$summary = new Group_Summary();

	foreach ( $files as $file ) {
		$stats = new VT_stats_top12();
		$stats->load_file( $file, $host );
		$grouper = $stats->count_things();
		//$grouper = $stats->count_things_differently();
		$grouper->report_total();
		$summary->add_group( $file, $grouper->total, $grouper->total_time ); 
		
		//$merger->append( $grouper->elapsed );
		$merger->append( $grouper->percentages );
		//$merger->append( $grouper->groups );
	}
	$merger->report_count();
	echo "Elapsed," . implode( $files, "," ) . PHP_EOL;
	$merger->sort();
	
	$merger->report(); 
	$merger->accum();
	
	echo "Accum," . implode( $files, "," ) . PHP_EOL;
	$merger->report_accum();
	
	$summary->report();
}


//$stats->count_things();

/**
 *
 * -- |	---------------
   1. | akismet
   2. | contact-form-7
   3.	| all-in-one-seo-pack 
	 4.	| wordpress-seo
	 5.	|	jetpack
	 6.	| google-sitemap-generator
	 7.	| nextgen-gallery
	 8.	| wordpress-importer
	 9.	| woocommerce
	 10.| wordfence
	 11.| google-analytics
	 12.| wp-super-cache
 */	 
	 
