<?php // (C) Copyright Bobbing Wide 2015, 2016

/**
 * Syntax: oikwp vt-driver.php bwtrace.vt.1224
 *
 * Input: file e.g. gt100s.csv
 * Output: bwtrace.ct.mmdd - client trace report
 *
 * Purpose: To run a set of sample requests to a website in order
 * to get it to use oik-bwtrace to produce a summary of the transactions run on the server. 
 * Note: oik-bwtrace should not be active on the server, only the functionality to produce the daily trace summary report.
 * 
 * The requests can be run against the current site
 * but it's more likely that they should be directed to
 * another site specifically configured with a defined set of plugins.
 * 
 * e.g. To measure the performance of the 12 plugins of Christmas we start with Akismet
 * and either add the others one by one, or replace them with the others, or both.
 *
 *
 * The transactions should be representative of real transactions
 * and performed against a real website configuration.
 * 
 * To do this on a copy of oik-plugins.com means that we'll have the background overhead of the oik plugins.
 * How we measure this extra overhead is an interesting question.
 *
 * 
 */
oik_require( "includes/oik-remote.inc" );
 
$driver = new VT_driver();

$file = oik_batch_query_value_from_argv( 1, null );
if ( $file ) {
	$loops = oik_batch_query_value_from_argv( 2, 1 );
} else {
	$file = "gt100-2016.csv";
	$loops = 1;
}

$driver->prepare( $file, $loops );
$driver->loop();

/**
 
function vt_driver() {
	$file = file( "oik-plugins.com/1221.vt" );
	$file = file( "gt100.csv" );
	$total = count( $file );
	$count = 0;
	for ( $loop = 1; $loop<=2; $loop++ ) {
	foreach ( $file as $line ) {
		$vt = str_getcsv( $line );
		if ( 0 !== strpos( $vt[0], "/wp-admin" ) ) {
			$timestart = microtime( true );
			echo $loop .  "." . $count++ . '/' .  $total . " " . $vt[0] . PHP_EOL;
			$url = build_url( $vt[0] );
			$result = bw_remote_get2( $url ); 
			//echo $result;
			$timeend = microtime( true );
			$timetotal = $timeend - $timestart;
			echo $vt[0] . " " . $timetotal . PHP_EOL; 
			
		}
	}
	}
	
}
*/

function build_url( $uri ) {
	$url = "http://qw/oikcouk";
	$url = "http://qw/oikcom";
	$url .= $uri;
	return( $url );
}


class VT_driver {

	public $timestart;
	
	public $file;
	
	public $lines;
	
	public $loops;
	
	public $total; 
	
	public $request;
	
	public $result;
	
	public $timetotal;
	
	public $cache_time;
	
	/**
	 * Constructor for VT_driver
	 * 
	 *
	 */
	public function __construct() {
		//$this->file = file( "gtsc.csv" );
		//$this->file = file( "gt100.csv" );
		//$this->file = file( "gt100s.csv" );
		
		//$this->file = file( "gt100-2016.csv" );
		
		
	}
	/**
	 * Load the URLs to process
	 */
	public function prepare( $file, $loops ) {
		$this->file = file( $file );
		$this->total = count( $this->file );
		$this->loops = $loops;		
		$this->lines = $this->total;
	}
	
	
	public function loop() {
		static $count = 0;
		for ( $loop = 1; $loop<=$this->loops; $loop++ ) {
			$count = 0;
			$lines = 0;
			for ( $lines = 0; $lines< $this->lines; $lines++ ) {
				$line = $this->file[ $lines ];
				$vt = str_getcsv( $line );
				if ( 0 !== strpos( $vt[0], "/wp-admin" ) ) {
					echo $loop .  "." . $count++ . '/' .  $this->total . " " . $vt[0] . PHP_EOL;
					$this->process_request( $vt[0], $line );
				}
			}
		}
	}
	
	public function process_request( $uri, $line ) {
		$this->timestart = microtime( true );
		$url = build_url( $uri );
		$result = $this->remote_get( $url ); 
		//echo $result;
		$timeend = microtime( true );
		$this->timetotal = $timeend - $this->timestart;
		$this->report_vt( $uri, $result );
		echo $uri . " " . $this->timetotal . " " . $this->cache_time .  PHP_EOL; 
	}
	
	
	/**
	 * Produce output in the form of a .ct.mmdd file
	 *
	 * So that we can analyze the performance when pages are being cached
	 * we produce a trace record with similar format to the bwtrace.vt.mmdd record
	 *
	 * This allows us to use the same logic to summarise .ct.mmdd files as .vt.mmdd
	 * for the important fields at least: uri, final
	 * 
	 * Some of the fields can be extracted from the trace output returned to the client.
	 *
	 * vt information copied from comments in class VT_row
	 
	 * #  | vt       | ct			 | ct field
	 * -- | ----     | ------	 | -----------
	 *  0 | uri      | uri     |
	 *  1 | action   |         | 
   *  2 | final    | final   | Elapsed time to receive the response
   *  3 | phpver   | response | HTTP response code
   *  4 | phpfns   | result bytes | strlen of the result
   *  5 | userfns  | tags | Number of tags
   *  6 | classes  | links | Number of link tags
   *  7 | plugins  | scripts | Number of script tags
   *  8 | files    | styles | Number of style tags
   *  9 | widgets  | images | Number of img tags
   * 10 | types    | anchors | Number of anchor tags
   * 11 | taxons   | 
   * 12 | queries   | 
   * 13 | qelapsed  | 
   * 14 | tracefile | cached | caching mechanism 
   * 15 | traces    | cache_time |
   * 16 | remote_IP | 
   * 17 | elapsed   | server elapsed | Set to final if not found
   * 18 | isodate   | isodate | 2015-12-28T23:57:58+00:00
	 *
	 * 
	 */
	function report_vt( $uri, $result ) {
		$output = array();
		$output[] = $uri;
		$output[] = null;
		$output[] = $this->timetotal;
		$output[] = wp_remote_retrieve_response_code( $this->request );
		$output[] = strlen( $result );
		$output[] = $this->count_tags( $result );  //  5 | userfns  | 4109
    $output[] = $this->count_links( $result );  //*  6 | classes  | 378
    $output[] = $this->count_scripts( $result );  //*  7 | plugins  | 41
    $output[] = $this->count_styles( $result );  //*  8 | files    | 448
    $output[] = $this->count_images( $result );  //*  9 | widgets  | 58
    $output[] = $this->count_anchors( $result );  //* 10 | types    | 28
    $output[] = null;  //* 11 | taxons   | 14,
    $output[] = null;  //* 12 | queries   | 22,
    $output[] = null;  //* 13 | qelapsed  | 0,
    $output[] = $this->cached( $result );  //* 14 | tracefile | ,
    $output[] = $this->cache_time();  //* 15 | traces    | ,
    $output[] = null;  //* 16 | remote_IP | 68.180.229.222,
    $output[] = $this->extract_elapsed( $result );  //* 17 | elapsed   | 0.309793,
		$output[] = date( 'c' );
	
		$line = implode( $output, "," );
		$line .= PHP_EOL;
		
		$this->write_ct( $line );
		//gob(); 
		
		

	}
	
	function write_ct( $line ) { 
		$file = ABSPATH . "bwtrace.ct." .  date( "md" );
		bw_write( $file, $line );
	}
	
	/**
	 *
	 */
	function remote_get( $url ) {
		$this->request = wp_remote_get( $url );
		if ( is_wp_error ($this->request ) ) {
			bw_trace2( $this->request, "request is_wp_error" );
			$this->result = null;
		} else {
			bw_trace2( $this->request, "request is expected", false );
			$this->result = bw_retrieve_result( $this->request );
		}
		bw_trace2( $this->result, "result" );
		return( $this->result );
	}
	
	/**
	 * Count tags
	 * 
	 * Count the number of tags 
	 */
	function count_tags( $result ) {
	
		//$dom = new DOMDocument;
		//print_r( $result );
		//$dom->loadHTML( $result );
		//$allElements = $dom->getElementsByTagName('*');
		//echo $allElements->length;
		//return( $allElements->length ); 
		$count = substr_count( $result, "<" );
		
		$chars = count_chars( $result );
		//print_r( $chars );
		//echo $chars[ ord( "<" ) ];
		//echo $chars[ ord( ">" ) ];
		return( $count );
	
	}
	
	function count_links( $result ) {
		$links = substr_count( $result, "<link" );
		return( $links );
	}
	
	
	function count_scripts( $result ) {
		$scripts = substr_count( $result, "<script" );
		return( $scripts );
	}
	
	function count_styles( $result ) {
		$styles = substr_count( $result, "<style" );
		return( $styles );
	}
	
	function count_images( $result ) {
		$images = substr_count( $result, "<img" );
		return( $images );
	}
	
	
	function count_anchors( $result ) {
		$anchors = substr_count( $result, "<a" );
		return( $anchors );
	}
	
	/**
	 * Determine if the content was cached?
	 * 
	 * At the end of the request wp-super-cache returns something like
	 * `
	 * <!-- Dynamic page generated in 0.669 seconds. -->
	 * <!-- Cached page generated by WP-Super-Cache on 2016-01-07 13:12:57 -->
	 * `
	 *
	 * This is returned when WP-Super-Cache is saving the content in the cache
	 * When it's returning a cached page then this is shown in the headers.
	 *
	 * @TODO So we need to look at headers
	 * `
		 *[headers] => Array
		 *(
		 *  [date] => Thu, 07 Jan 2016 13:48:11 GMT
		 *  [server] => Apache/2.4.18 (Win64) PHP/7.0.2RC1
		 *  [x-powered-by] => PHP/7.0.2RC1
		 *  [vary] => Accept-Encoding,Cookie
		 *  [cache-control] => max-age=3, must-revalidate
		 *  [wp-super-cache] => Served supercache file from PHP
		 *  [connection] => close
		 *  [content-type] => text/html; charset=UTF-8
	 * `
	 * or
	 * 'WP-Super-Cache' Served legacy cache file
	 */ 
	function cached( $result ) {
		$this->cache_time = null;
		$cached = null;
		$lookfor = "<!-- Cached page generated by WP-Super-Cache on" ;
		$pos = strrpos( $result, $lookfor );
		if ( $pos ) {
			$cached = "WP-Super-Cache";
			$this->get_cache_time( $result );
		}
		return( $cached );
	}
	
	/**
	 * Get the cache time 
	 * 
	 * Cache time is the time spent creating the content to put into the cache
	 * not the time spent in the server when serving a cached page
	 */
	function get_cache_time( $result ) {
	
		$lookfor = "<!-- Dynamic page generated in " ;
		$pos = strrpos( $result, $lookfor );
		if ( $pos ) {
			$cache_info = substr( $result, $pos );
			$words = explode( " ", $cache_info ); 
			$this->cache_time = $words[5];
		}
		//echo "Cache time: " . $this->cache_time;
		//gob();
	
	}
	
	/**
	 * Return the value for cache_time
	 *
	 * This value is null if the content wasn't cached
	 *
	 */
	function cache_time() {
		return( $this->cache_time );
	}
	
	/**
	 * Extract the server elapsed time from the trace comments, if present
	 * 
	 * `<!--Elapsed (secs):0.589108 -->`
	 * 
	 * Note: When using the bbboing language the decimal point may appear very oddly 
	 * as, for example, nubmer_fmroat_diaemcl_pinot.
	 * 
	 * @TODO Cater for this somehow
	 * @TOOD Ensure we find the last one, not something in the generated content.
	 */
	function extract_elapsed( $result ) {
		$lookfor = "<!--Elapsed (secs):"; 
		$pos = strpos( $result, $lookfor );
		if ( $pos ) {
			$elapsed = substr( $result, $pos+ strlen( $lookfor ) );
			$elapsed = substr( $elapsed, 0, -4 );
			//echo $elapsed;
		} else {
			$elapsed = $this->timetotal;
		}
		return( $elapsed );
	
										
	}
	
	
	
	  


}

