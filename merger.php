<?php // (C) Copyright Bobbing Wide 2015

oik_require( "class-CSV-merger.php", "play" );


$merger = new CSV_merger();

$fred = array( "a" => "fred", "b" => "blogs" );
$hilma = array( "a" => "hilma", "b" => "blogs2" );

$merger->append( $fred );
$merger->append( $hilma );
$merger->report();
