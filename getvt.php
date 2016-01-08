<?php // (C) Copyright Bobbing Wide 2015, 2016


 
/**
 * Get the current oik version from the readme.txt file
 *
 */

function get_vt( $host, $date ) {
  $url = "http://$host/bwtrace.vt.$date";
  $result = bw_remote_get2( $url );
  //echo $result;
  echo "$date $host: " . strlen( $result ) . PHP_EOL;
  return( $result );
}

function save_vt( $host, $date, $content ) {
  file_put_contents( "$host/$date.vt", $content ); 
}



oik_require( "wp-batch-remote.php", "play" );

oik_require( "includes/oik-remote.inc" ); 
 
//  "oik-plugins.biz" and oik-plugins.com are now the same
// "oik-plugins.uk" now also oik-plugins.com


$hosts = array( "oik-plugins.com"
              , "oik-plugins.co.uk"
							, "herbmiller.me"
							, "bobbingwide.com"
							//, "wp-a2z.org"
              );
//$hosts = array( "herbmiller.me" ); 
 					
//$dates = array( "0320", "0321", "0322", "0323", "0324", "0325", "0326", "0327", "0328", "0329", "0330", "0331" );
//$dates = array( "0401", "0402", "0403", "0404", "0405", "0406", "0407", "0408", "0409", "0410" );
//$dates = array( "0411", "0412", "0413", "0414", "0415", "0416", "0417", "0418", "0419", "0420" );
//$dates = array( "0421", "0422", "0423", "0424", "0425", "0426", "0427", "0428", "0429", "0430" );
//$dates = array( "0501", "0502", "0503", "0504", "0505" ); // , "0426", "0427", "0428", "0429", "0430" );

$dates = array();
$startdate = strtotime( "2016-01-05" );
$enddate = time();
$enddate = strtotime( "2016-01-07" );

//$startdate = strtotime( "2015-06-01" );
//$enddate = strtotime( "2015-07-0" );

//echo "start: $startdate" ;
//echo "end: $enddate";

for ( $thisdate = $startdate; $thisdate<= $enddate; $thisdate+= 86400 ) {
	$dates[] = date( "md", $thisdate); 
}

/** 
 * Fetch and save the bwtrace.vt.mmdd file for each of the selected hosts
 */
if ( true ) {

  foreach ( $dates as $date ) {
    foreach ( $hosts as $host ) {

      $content = get_vt( $host, $date );
      save_vt( $host, $date, $content );
    }
  }  
}

/**
 * Process each of the files from the hosts
 */
oik_require( "vt.php", "play" );

foreach ( $dates as $date ) {

  foreach ( $hosts as $host ) {
    process_file( "$host/$date.vt" );
  }
}  
