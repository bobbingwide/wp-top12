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

oik_require( 'class-narrator.php', 'oik-i18n');



//query_my_plugins();

//downloads();
$stats = new VT_stats();

$stats->from_date( "2020-12-29" );
//$stats->to_date( '2020-12-30');
$stats->set_host( 'C:/backups-SB/oik-plugins.com/bwtrace');
//$stats->from_date( "2015-12-29" );
$stats->populate();
$stats->populate_grouper();

$stats->count_request_types();
$stats->time_request_types();


$stats->count_things();


