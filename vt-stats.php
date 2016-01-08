<?php // (C) Copyright Bobbingwide 2015, 2016
/**
 * Syntax: oikwp vt-stats.php process
 * 
 * 
 */
ini_set('memory_limit','1572M');

$plugin = "wp-top12";
oik_require( "class-vt-stats.php", $plugin );
oik_require( "class-vt-row-basic.php", $plugin );
oik_require( "class-object-sorter.php", $plugin );
oik_require( "class-object.php", $plugin );
oik_require( "class-object-grouper.php", $plugin );
oik_require( "class-CSV-merger.php", $plugin );


//query_my_plugins();

//downloads();
$stats = new VT_stats();

$stats->from_date( "2015-10-01" ); 
$stats->from_date( "2015-12-29" );
$stats->populate();

$stats->count_things();


