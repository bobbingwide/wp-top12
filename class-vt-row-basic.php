<?php // (C) Copyright Bobbing Wide 2015

/**
 * VT_row_basic implements a subset of the information for a row from a bwtrace.vt.mmdd file
 * 
 * Note: There could be multiple versions of the contents of this row.
 * In early versions of this routine we won't attempt to cater for this unless it's absolutely necessary
 * There was also a problem with some unexpected commas in the URL and/or AJAX admin parms.
 * We may need to cater for this as well.
 *
 * 
 * This class is also implemented as Trans in vt.php
 */
 
 






/**
 * 
 Array
(
    [0] => /oik-plugins/oik/?bwscid1=4&bwscid2=8&bwscid4=3&bwscid5=7
    [1] =>
    [2] => 13.834431
    [3] => 5.3.29
    [4] => 3160
    [5] => 4041
    [6] => 483
    [7] => 44
    [8] => 398
    [9] => 57
    [10] => 28
    [11] => 14
    [12] => 129
    [13] => 1.07188820839
    [14] =>
    [15] => 13.830200
    [16] => 2015-03-14T00:00:01+00:00
)

/**
 * Original version 
 *  
 * 0- request URI
 * 1 - AJAX action
 * 2 - elapsed ( final figure )
 * 3 - PHP version
 * 4 - PHP functions
 * 5 - User functions
 * 6 - Classes
 * 7 - Plugins
 * 8 - Files
 * 9 - Registered Widgets
 * 10 - Post types
 * 11 - Taxonomies
 * 12 - Queries
 * 13 - Query time
 * 14 - Trace records
 * 15 - Elapsed
 * 16 - Date - ISO 8601 date 
 *
 * OR it may end
 * 
 * 14 - Trace records
 * 15 - Remote address ( IP address )
 * 16 - Elapsed
 * 17 - Date - ISO 8601 date 
 
 * OR it may end
 * 
 * 14 tracefile - which may be null
 * 15 trace records - a number
 * 16 - Remote address ( IP address ) 
 * 17 - Elapsed
 * 18 - Date - ISO 8601 date
*/


class VT_row_basic { 

//class Trans {
  //private $trans = null;
  public $uri;		// $this->trans[0];
  public $action; // $this->trans[1];
  public $final ; // $this->trans[2];
  //public $phpver; // $this->trans[3];
  //public $phpfns; // $this->trans[4];
  //public $userfns; // $this->trans[5];
  public $classes; // $this->trans[6];
  public $plugins; // $this->trans[7];
  public $files ; // $this->trans[8];
  public $widgets; // $this->trans[9];
  public $types ; // $this->trans[10];
  public $taxons; // $this->trans[11];
  public $queries; // $this->trans[12];
  public $qelapsed; // $this->trans[13];
	//public $tracefile; // $this->trans[14]; or not at all 
  //public $traces; // $this->trans[15] or 14;
	//public $remote_IP; // $this->trans[16] or 15 or 14;
  public $elapsed; // $this->trans[17] or 16 or 15;
  public $isodate; //$this->trans[18] or 17 or 16;
  
  public $suri;   // Stripped URI
	public $suritl; // Top level part of stripped URI
	public $suril;  // Last part of stripped URI
  //public $qparms; // Query parameters
	
	/**
	 *
     # | name | sample value
     - | ---- | ------------	 
     0 | uri | /examples/how-to-create-a-contact-page-using-oik-and-jetpack/jetpack-contact-field-shortcode-country-select-list,
     1 | action | ,
     2 | final |0.309858,
     3 | phpver | 5.6.16,
     4 | phpfns | 2089,
     5 | userfns | 4109,
     6 | classes | 378,
     7 | plugins | 41,
     8 | files | 448,
     9 | widgets | 58,
     10 | types | 28,
     11 | taxons | 14,
     12 | queries | 22,
     13 | qelapsed | 0,
     14 | tracefile | ,
     15 | traces | ,
     16 | remote_IP | 68.180.229.222,
     17 | elapsed | 0.309793,
     18 | isodate | 2015-12-28T23:57:58+00:00

		 Other examples
		 `
			/oik_api/_bw_acronym/,,0.474451,5.6.16,2089,4512,385,41,488,58,28,14,74,0,,,202.46.56.177,0.474420,2015-12-28T23:59:09+00:00
			/robots.txt,,0.333942,5.6.16,2089,4162,380,41,451,58,28,14,23,0,,,157.55.39.56,0.333895,2015-12-28T23:59:11+00:00
		 `
	*/
  public function __construct( $transline ) {
    $this_trans = str_getcsv( $transline );
    $this->uri = $this_trans[0];
    $this->action = $this_trans[1];
    $this->final  = $this_trans[2];
    //$this->phpver = $this_trans[3];
    //$this->phpfns = $this_trans[4];
    //$this->userfns= $this_trans[5];
    $this->classes= $this_trans[6];
    $this->plugins= $this_trans[7];
    $this->files  = $this_trans[8];
    $this->widgets= $this_trans[9];
    $this->types  = $this_trans[10];
    $this->taxons = $this_trans[11];
    $this->queries = $this_trans[12];
    $this->qelapsed = $this_trans[13];
		//$this->tracefile = $this_trans[14];
    //$this->traces = $this_trans[15];
		
		/**
		 * remote_IP is now 16, was 15, was 14 - not catered for
		 */
//		$this->remote_IP = $this_trans[16];
//		$IP_parts = explode( '.', $this->remote_IP );
//		if ( count( $IP_parts ) != 4 ) {
//			$this->remote_IP = null;
  //    $this->elapsed = $this_trans[16];
	//		$this->isodate= $this_trans[17];
		//} else {
		  $this->elapsed = $this_trans[17];
			$this->isodate = $this_trans[18];
		//}
		$this->uri_parser();
		
    
    
     
  }
	
	/**
	 * Parse the URI
	 * 
	 * 
	 * Create separate fields
	 * - stripped URI ( suri ) 
	 * - top level ( suritl)
	 * - bottom level ( suril )
	 * 
	 */ 
	
	function uri_parser() {
		//echo $this->uri . PHP_EOL;
		
    list( $this->suri, $qparms, $blah ) = explode( "?", $this->uri . "???" , 3 );
		$suri = trim( $this->suri, "/" ); 
    $blah = explode( "/", $suri ); 
	  $this->suritl = $blah[0];
		$this->suril = end( $blah );
		unset( $blah );
		//echo $this->suritl . PHP_EOL;
		//echo $this->suril . PHP_EOL;
		
		//gob();
	}

}
