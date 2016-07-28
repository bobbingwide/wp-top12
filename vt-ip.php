<?php // (C) Copyright Bobbing Wide 2016

/**
 * Syntax: php vt-ip.php $file
 * 
 * @TODO Make it work off parameters rather than hardcoded.
 *
 */

function vt_ip_loaded() {
	$year = "2016";
	$host = "bobbingwide.org.uk";
	$mmdd = "0728";
	$file = "vt$year/$host/$mmdd.vt";
	$contents = file( $file );
	global $ips;
	$ips = array();
	//echo count( $contents) ;
	count_ips( $contents );
	//print_r( $ips );
	
	print_ips( 100, $file, $contents );
	
}

ini_set( "memory_limit", "256M" ); 

vt_ip_loaded();

/**
 * Count the requests by IP
 * 
 * Format of a record ( $content ) when extracted to $data is expected to be
 *
   `
    [0] => /oik_api/_deprecated_argument/
    [1] =>
    [2] => 0.648111
    [3] => 7.0.8
    [4] => 1947
    [5] => 4212
    [6] => 333
    [7] => 19
    [8] => 361
    [9] => 50
    [10] => 23
    [11] => 11
    [12] => 51
    [13] =>
    [14] =>
    [15] =>
    [16] => 66.249.66.179
    [17] => 0.648082
    [18] => 2016-07-27T00:00:06+00:00
    [19] => Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)
    [20] => GET
		`
 *		
 */
function count_ips( $contents ) {
	foreach ( $contents as $content ) {
		$data = str_getcsv( $content );
		//print_r( $data );
		//gob();
		$ip = $data[16] ;
		$elapsed = $data[2];
		$http_user_agent = $data[19];
		add_ip( $ip, $elapsed, $http_user_agent );
	}
}

/**
 * Accumulate data for each IP
 *
 * @param string $ip - e.g. 66.249.64.180
 * @param float $elapsed - the elapsed time for the request
 * @param string $http_user_agent 
 */
function add_ip( $ip, $elapsed, $http_user_agent ) { 
	global $ips;
	
	if ( !isset( $ips[ $ip ] ) ) {
		$http_user_agent = str_replace( ",", " ", $http_user_agent );
		$ips[ $ip ] = array( 0, 0.0, $http_user_agent );
	}
	$ips[ $ip ][0] = $ips[ $ip ][0] + 1;
	$ips[ $ip ][1] = $ips[ $ip ][1] + $elapsed;
	// $ips[ $ip ][2] = 
}

/**
 * Print the IPs summary
 *
 * @TODO Add average column
 *
 * @param integer $limit - number of hits to report individually
 * @param string $file name - so we know where the information comes from
 * @param array $content - the file contents 
 */
function print_ips( $limit, $file, $contents ) {
	global $ips;
	$rest_ip_count = 0;
	$rest_count = 0;
	$rest_elapsed = 0;
	$total_ips = 0;
	$total_elapsed = 0;
	
	echo "IP,count,elapsed,HTTP_user_agent $file" . PHP_EOL; 
  foreach ( $ips as $ip => $data ) {
		$count = $data[0];
		$elapsed = $data[1];
		$http_user_agent = $data[2];
		if ( $count >= $limit ) {

			echo "$ip,$count,$elapsed,$http_user_agent" . PHP_EOL;
		} else {
			$rest_ip_count++;
			$rest_count += $count;
			$rest_elapsed += $elapsed;
		}
		$total_ips++;
		$total_elapsed += $elapsed;
	}
	$total_requests = count( $contents );
	echo "the.rest.lt.$limit,$rest_count,$rest_elapsed,$rest_ip_count" . PHP_EOL;
	echo "TOTAL,$total_requests,$total_elapsed,$total_ips" . PHP_EOL;
}	
		

 
	
	
	
	
