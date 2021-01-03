<?php
/**
 * @copyright (C) Copyright Bobbingwide 2015, 2016, 2020, 2021
 * @package wp-top12
 *
 * Syntax: oikwp vt-stats.php process
 * 
 * 
 */
ini_set('memory_limit','1572M');

$plugin = "wp-top12";
oik_require( "libs/class-vt-stats.php", $plugin );
oik_require( "libs/class-vt-row-basic.php", $plugin );
oik_require( "libs/class-object-sorter.php", $plugin );
oik_require( "libs/class-object.php", $plugin );
oik_require( "libs/class-object-grouper.php", $plugin );
oik_require( "libs/class-csv-merger.php", $plugin );

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


