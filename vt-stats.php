<?php // (C) Copyright Bobbingwide 2015
/**
 * Syntax: oikwp vt-stats.php process
 * 
 * 
 */
ini_set('memory_limit','1572M');

//require_once( ABSPATH . "wp-admin/includes/plugin-install.php" );
//oik_require( "includes/oik-remote.inc" );

//oik_require( "class-wp-org-downloads.php", "play" );

oik_require( "class-vt-stats.php", "play" );

oik_require( "class-vt-row-basic.php", "play" );

oik_require( "class-object-sorter.php", "play" );
oik_require( "class-object.php", "play" );
oik_require( "class-object-grouper.php", "play" );

oik_require( "class-CSV-merger.php", "play" );


//query_my_plugins();

//downloads();
$stats = new VT_stats();

$stats->from_date( "2015-10-01" ); 
$stats->from_date( "2015-12-29" );
$stats->populate();

$stats->count_things();


