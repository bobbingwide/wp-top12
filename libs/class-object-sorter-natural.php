<?php // (C) Copyright Bobbing Wide 2016

/**
 * natsort can be used to sort arrays using a natural sort order
 *
 * we want to use this to perform a natural sort on objects
 */
class Object_Sorter_Natural extends Object_Sorter {

	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Sort objects by code
	 *
	 * This is the function that does the business - sorting by natural string order
	 *
	 * @param object $a - first item to be sorted
	 * @param object $b - second item to be sorted
	 * @return integer -1 if a to be before b, 0 if equal, 1 if a to be after b
	 */
	function sort_objects_by_code( $a, $b ) {
		$property_name = $this->orderby;
		$result = strnatcmp( $a->{$property_name}, $b->{$property_name} );
		
		if ( $this->order == "desc" ) {
			$result = -$result;
		}
		return( $result );
	}

}



