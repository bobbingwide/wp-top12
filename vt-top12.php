<?php // (C) Copyright Bobbingwide 2015, 2016
/**
 * Syntax: oikwp vt-top12.php process
 * 
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

process_files( $files );

$files = array( "vanilla5", "cf7", "ai1seo", "yoastseo", "jetpack", "nextgen", "woocommerce", "wordfence", "analytics" );

process_files( $files );

$files = array( "vanilla5", "sfence2", "cfence2", "ssuper", "csuper" );

process_files( $files );



function process_files( $files ) { 
																											 
	$merger = new CSV_merger();
	$summary = new Group_Summary();

	foreach ( $files as $file ) {
		$stats = new VT_stats_top12();
		$stats->load_file( $file );
		// $grouper = $stats->count_things();
		$grouper = $stats->count_things_differently();
		$grouper->report_total();
		$summary->add_group( $file, $grouper->total, $grouper->total_time ); 
		
		$merger->append( $grouper->elapsed );
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
	 
