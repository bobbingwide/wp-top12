<?php // (C) Copyright Bobbing Wide 2015

/**
 * CSV_merger
 * 
 * Allows you to merge multiple associative arrays 
 * to produce new associative arrays which can then be printed as a single multi-column CSV.
 * 
 * Use this to create data for multiline charts.
 *
 * array of arrays() 
 * where internal array can either be non-associative or named
 * 
 * $merged[ $key ]  = array(  0 => value, 1 => next_value, 2=> etc )   
 * 
 */
class CSV_merger {

	public $merged;
	
	public $array_index;

	function __construct() {
		$this->merged = array();
		$this->array_index = 0;
	}
	
	function append( $appendages ) {
		echo "Appendages: " . count( $appendages ) . PHP_EOL;
		foreach ( $appendages as $key => $appendage ) {
			$this->merged[ $key ][ $this->array_index ] = $appendage;
		}
		$this->array_index++;
	}
	
	function report() {
		echo "Merged:" . count( $this->merged ) . PHP_EOL;
		foreach ( $this->merged as $key => $appendages ) {
			$oline = array();
			$oline[] = $key;
			for ( $ai = 0; $ai < $this->array_index; $ai++ ) {
				if ( isset( $appendages[ $ai ] ) ) {
					$oline[] = $appendages[ $ai ];
				} else {
					$oline[] = 0;
				}
				 
			}
			$line = implode( $oline, "," );
			echo $line . PHP_EOL;
		}
	}
			



}
