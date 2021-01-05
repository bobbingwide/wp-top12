<?php
/**
 * @copyright (C) Copyright Bobbing Wide 2015-2021
 * @package wp-top12
 *
 * VT_row implements a row from a bwtrace.vt.ccyymmdd file
 * 

 * This class is / was also implemented as Trans in vt.php
 */



/**
 * Note: There could be multiple versions of the structure of the row.
 *
 * In early versions of this routine (2015) we won't attempt to cater for this unless it's absolutely necessary.
 * There was also a problem with some unexpected commas in the URL and/or AJAX admin parms.
 * We may need to cater for this as well.
 *
 * This class ( originally)  supported trace summary records with varying numbers of fields
 *
 * 0-16 - Original version: request URI to ISO 8601 date
 * 0-17 - with remote address ( IP address ) at index 15
 * 0-18 - inserted tracefile (which may be null ) before trace records
 *
 * VT_row_basic now supports more fields than VT_row.
 * @TODO Eliminate VT_row_basic and just use VT_row.
 *
 */


class VT_row { 

//class Trans {
  //private $trans = null;
  public $uri;		// $this->trans[0];
  public $action; // $this->trans[1];
  public $final ; // $this->trans[2];
  public $phpver; // $this->trans[3];
  public $phpfns; // $this->trans[4];
  public $userfns; // $this->trans[5];
  public $classes; // $this->trans[6];
  public $plugins; // $this->trans[7];
  public $files ; // $this->trans[8];
  public $widgets; // $this->trans[9];
  public $types ; // $this->trans[10];
  public $taxons; // $this->trans[11];
  public $queries; // $this->trans[12];
  public $qelapsed; // $this->trans[13];
	public $tracefile; // $this->trans[14]; or not at all 
  public $traces; // $this->trans[15] or 14;
	public $remote_IP; // $this->trans[16] or 15 or 14;
  public $elapsed; // $this->trans[17] or 16 or 15;
  public $isodate; //$this->trans[18] or 17 or 16;
  
  public $suri;   // Stripped URI
	public $suritl; // Top level part of stripped URI
	public $suril;  // Last part of stripped URI
  public $qparms; // Query parameters
	public $surisl; // Second level part of stripped URI - when run in a subdirectory install
	
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
    $this->phpver = $this_trans[3];
    $this->phpfns = $this_trans[4];
    $this->userfns= $this_trans[5];
    $this->classes= $this_trans[6];
    $this->plugins= $this_trans[7];
    $this->files  = $this_trans[8];
    $this->widgets= $this_trans[9];
    $this->types  = $this_trans[10];
    $this->taxons = $this_trans[11];
    $this->queries = $this_trans[12];
    $this->qelapsed = $this_trans[13];
		$this->tracefile = $this_trans[14];
    $this->traces = $this_trans[15];
		
		/**
		 * remote_IP is now 16, was 15, was 14 - not catered for
		 */
		$this->remote_IP = $this_trans[16];
		$IP_parts = explode( '.', $this->remote_IP );
		if ( count( $IP_parts ) != 4 ) {
			$this->remote_IP = null;
      $this->elapsed = $this_trans[16];
			$this->isodate= $this_trans[17];
		} else {
		  $this->elapsed = $this_trans[17];
			$this->isodate = $this_trans[18];
		}
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
		
    list( $this->suri, $this->qparms, $blah ) = explode( "?", $this->uri . "???" , 3 );
		$suri = trim( $this->suri, "/" ); 
    $blah = explode( "/", $suri ); 
		if ( isset( $blah[0] ) ) { 
			$this->suritl = $blah[0];
		} 
		if ( isset( $blah[1] ) ) {
      $this->surisl = $blah[1];
		}
		$this->suril = end( $blah );
		//echo $this->suritl . PHP_EOL;
		//echo $this->suril . PHP_EOL;
		
		//gob();
	}

}
