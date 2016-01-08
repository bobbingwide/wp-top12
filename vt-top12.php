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

oik_require( "class-vt-stats.php", "play" );
oik_require( "class-vt-stats-top12.php", "play" );

oik_require( "class-vt-row.php", "play" );

oik_require( "class-object-sorter.php", "play" );
oik_require( "class-object.php", "play" );
oik_require( "class-object-grouper.php", "play" );

oik_require( "class-CSV-merger.php", "play" );


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

																											 
$merger = new CSV_merger();

foreach ( $files as $file ) {
  $stats = new VT_stats_top12();
	$stats->load_file( $file );
	$grouper = $stats->count_things();
	//$merger->append( $grouper->groups );
	$grouper->report_total();
	$merger->append( $grouper->percentages );
	//$merger->append( $grouper->groups );
}
echo "Elapsed," . implode( $files, "," ) . PHP_EOL;


$merger->report(); 



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
	 
