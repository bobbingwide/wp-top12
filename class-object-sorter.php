<?php // (C) Copyright Bobbing Wide 2015

/**
 * Class Object_Sorter
 *
 * A generic object sorter for sorting an array of objects by a given key orderby field and order ( asc or desc )
 * 
 * Allows resorting on different fields and returning a subset of the total array
 *
 * I'm surprised that this isn't a standard part of WordPress. 
 * Maybe it's part of the Walker classes
 * 
 */

class Object_Sorter {

	/** 
	 * Sort order column
	 * 
	 * code or total_references 
	 */
  public $orderby = null;	 	
	
	/** 
	 * Sort order - 'asc' or 'desc'
	 */
	public $order = null;
	
	/** 
	 * This is the array of objects that we're sorting
	 */
	public $objects;
	
	/**
	 * The constructor creates an empty set
	 */
	public function __construct() {
		$this->orderby = null;
		$this->order = 'asc';
		$this->objects = array();
	}
	
	/**
	 * Set the field by which we're ordering
	 * 
	 * @TODO - check that the property exists
	 * 
	 * @param string $orderby the property name - it may be a grouped field
	 * 
	 */
	public function orderby( $orderby ) {
		$this->orderby = $orderby;
	}
	
	/**
	 * Set the ordering: 'asc' or 'desc'
	 * 
	 * @TODO validate the parameter
	 * 
	 * @param string $order should become 'asc' or 'desc' when lowercased
	 */
	public function order( $order ) {
		$this->order = strtolower( $order );
	}
	
	/**
	 * Sort the full list of items
	 * 
	 * When the items are not loaded by WP_Query() then we need to put them in the required order manually
	 * using the defined sort sequence. 
	 *
	 * Note: WP_List_Table doesn't cater for sorting on multiple columns, so we don't either
	 */
	public function sort() {
		usort( $this->objects, array( $this, "sort_objects_by_code" ) );
		return( $this->objects );
	}
	
	/**
	 * Helper method to provide a new array of objects to be sorted
	 */
	public function sortby( $objects, $orderby=null, $order="asc" ) {
		echo "Sorting a: " . count( $objects ) . PHP_EOL;
		$this->populate( $objects );
		
		echo "Sorting b: " . count( $this->objects ) . PHP_EOL;
		$sorted = $this->resort( $orderby, $order );
		return( $sorted );
	}
	
	/**
	 * Helper method to request a different sort sequence
	 */
	public function resort( $orderby=null, $order="asc" ) {
		$this->orderby( $orderby );
		$this->order( $order );
		//$this->populate_orderby_field();
		$sorted = $this->sort();
		return( $sorted );
	}
	
	 
	/**
	 * Populate the array of objects to sort
	 *
	 * We assume the array is homogeneous
	 * 
	 * @param array $objects array of objects
	 */
	public function populate( $objects ) {
		$this->objects = $objects;
	}
	
	/**
	 * Populate values for the field we're sorting on
	 *
	 * Here we need to populate the field on which were going to perform the orderby
	 * It might be better done outside of the sort
	 * since we're doing a callback to the method the same name as the orderby in the object type we're sorting
	 * 
	 */
	public function populate_orderby_field() {
		$orderby = $this->orderby;
		foreach ( $this->items as $item => $code ) {
			if ( !$code->{$orderby} ) {
				$code->{$orderby}();
			}
		}
	}
	
	/**
	 * Return a subset of items
	 *
	 * @param integer $limit - use null to return all the items
	 * @return array the chosed result set
	 */
	public function results( $limit=null ) {
		echo "Full set: " . count( $this->objects ) . PHP_EOL;
		if ( $limit ) {
			$result_set = array_slice( $this->objects, 0, $limit );
		} else {
			$result_set = $this->objects;
		}
		return( $result_set );
	}
	
	/**
	 * Sort objects
	 *
	 * This is the function that does the business. It's a fairly generic routine.
	 *
	 * @TODO Should we concern ourselves about case sensitivity?
	 * 
	 * See {link http://davidwalsh.name/sort-objects}
	 * See notes on usort() producing warnings - which will happen if we trace the parameters
	 *
	 * `
	  [code] => admin
            [function] => 
            [status] => 
            [total_references] => 1
            [comments_refs] => 
            [commentmeta_refs] => 
            [links_refs] => 
            [options_refs] => Array
                (
                    [widget_text] => widget_text
                )

            [postmeta_refs] => 
            [posts_refs] => 
            [sitemeta_refs] => 
            [term_meta_refs] => 
            [term_taxonomy_refs] => 
            [terms_refs] => 
            [usermeta_refs] => 
            [users_refs] => 
            [widget_refs] => 
            [table_refs] => 
            [php_refs] => 
			`
	 * @param object $a - first item to be sorted
	 * @param object $b - second item to be sorted
	 * @return integer -1 if a to be before b, 0 if equal, 1 if a to be after b
	 */
	function sort_objects_by_code( $a, $b ) {
		$property_name = $this->orderby;
		if ( $a->{$property_name} == $b->{$property_name} )  { 
			$result = 0; 
		} elseif ( $a->{$property_name} < $b->{$property_name} ) {
			$result = -1;
		} else {
			$result = 1;
		}
		if ( $this->order == "desc" ) {
			$result = -$result;
		}
		return( $result );
	}

}
