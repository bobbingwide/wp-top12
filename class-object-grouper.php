<?php // (C) Copyright Bobbing Wide 2015-2017

/**
 * Class: Object_Grouper
 *
 * Groups objects by key values
 *
 *
 * Count all the things we want to count by grouping on key values. 
 * 
 * If we need to convert the actual value of a field into a subset for the grouping
 * then this should either be done prior to calling the object grouper or it 
 * can be done using a callback function set using subset() ( var $subset ).
 * 
 * Examples below
 * 
 * 
 * Key        | Subset | What this shows
 * ---------- | ------ | ---------------------------------------
 * downloaded | 10**n  | Grouped by total downloads range
 * updated    | year   | Grouped by last updated year
 * slug / name | A-Z   | Grouped by first letter
 * 
 * 
 */
class Object_Grouper extends Object_base {

	public $groups;
	
	public $key;
	
	public $subset;
	
	/**
	 * Name of the field that holds the elapsed time to accumulate by group
	 * Leave null if you don't want this accumulated
	 */									 
	public $time_field; 
	
	/**
	 * Array of elapsed time by group
	 */
	public $elapsed;
	
	/**
	 * Total number of things counted
	 */							
	public $total;
	
	/**
	 * Total time - when counting time
	 */
	public $total_time;
	
	/**
	 * Array of percentages rather than counts
	 *
	 */
	public $percentages;
	
	/**
	 * Filter function to perform 'where' like logic
	 *
	 * returns true if the object is to be grouped
	 */							
	public $where;
	
	public function __construct() {
		//$this->objects = null;
		parent::__construct();
		$this->key();
		$this->subset();
		$this->where();
		$this->having();
		$this->time_field(); 
		$this->reset();
	}
	
	
	public function key( $key=null ) {
		$this->key = $key;
	}
	
	public function subset( $subset=null ) {
		$this->subset = $subset;
	}
	
	public function where( $where=null ) {
		$this->where = $where; 
	}
	
	public function having( $having=null ) {
		$this->having = $having;
	}

	
	/**
	 * Set the field used for determining elapsed time
	 */
	public function time_field( $time_field = null ) {
		$this->time_field = $time_field;
		if ( $this->time_field ) {
			//echo $this->time_field;
		}
	}
	
	
	public function reset() {
		unset( $this->groups );
		unset( $this->percentages );
		unset( $this->elapsed );
		$this->groups = null;
		$this->percentages = null;
		$this->elapsed = null;
		$this->total = 0;
		$this->total_time = 0;
	}
	
	/**
	 * 
	 */
	public function groupby( $key, $subset=null ) {
		$this->reset();
		$this->key( $key );
		if ( $subset ) {
			$this->subset( $subset );
		}
		foreach ( $this->objects as $slug => $object ) {
			$object = ( object ) $object;
			if ( $this->where ) {
				$group = call_user_func( $this->where, $object ); 
			} else {
				$group = true;
			}
			if ( $group ) {
				$this->group( $object );
			}
		}
	}
	
	/**
	 * Group this object by its group key value
	 *
	 * Allow for there being multiple values returned from the subset method
	 */
	public function group( $object ) {
		//bw_trace2( null, null, true, BW_TRACE_DEBUG ); 
		if ( isset( $object->{$this->key} ) ) { 
			$this->field_value = $object->{$this->key};
		} else {
			$this->field_value = " ";
		}
		if ( $this->subset ) {
			$this->field_value = call_user_func( $this->subset, $this->field_value );
		}
		
		if ( is_array( $this->field_value ) ) {
			$this->count_values();
		} else {
			$this->count_value();
		}
		
		if ( $this->time_field ) {
			$this->accumulate_time( $object );
		}  
		
	}
	
	/**
	 * Report the total values for this group
	 */
	
	public function report_total() {
		echo "Total: " . $this->total . PHP_EOL;
		if ( $this->time_field ) {
			echo "Total time: " . $this->total_time . PHP_EOL;
			if ( $this->total ) {
				echo "Average: " . $this->total_time / $this->total . PHP_EOL;
			}
		}
	}
	
	
		
		
	/**
	 * Count this instance
	 * 
	 */
	function count_value() {
		//print_r( $this->field_value );
		if ( !isset( $this->groups[ $this->field_value ] ) ) {
			$this->groups[ $this->field_value ] = 1;
		} else {
			$this->groups[ $this->field_value ]++;
		}
		$this->total++;
	}
	
	/**
	 * Count these instances
	 */
	function count_values() {
		$values = $this->field_value;
		foreach ( $values as $key => $value ) {
			$this->field_value = $value;
			$this->count_value();
		}
	}
	
	/**
	 * Accumulate the elapsed time for the group
	 *
	 * If there is more than one value in the field_value then we accumulate the first value only
	 * We assume that the time field is set for the $object 
	 * 
	 * @param object $object object which must contain the $time_field property
	 */
	function accumulate_time( $object ) {
	
		if ( is_array( $this->field_value ) ) {
			$field_value = $this->field_value[0];
    } else {
			$field_value = $this->field_value;
		}
		
		$time = $object->{$this->time_field};
		if ( !isset( $this->elapsed[ $field_value ] ) ) {
			$this->elapsed[ $field_value ] = $time;
		} else {
			$this->elapsed[ $field_value] += $time;
		}
		$this->total_time += $time;
	
	}
	
	
	/**
	 * Report the grouped counts
	 *
	 * 
	 */
	function report_groups() {
		//$this->start_report();
		//print_r( $this->groups );
		echo "Groups:," . $this->key . PHP_EOL;
		foreach ( $this->groups as $key => $field ) {
			if ( $this->having ) { 
				$having = call_user_func( $this->having, $key, $field );
			} else {
				$having = true;
			}
			if ( $having ) {
				$this->report_field( $key, $field );
			}
		}
		//$this->end_report();
	}
	
	function start_report() {
		oik_require( "shortcodes/oik-list.php" );
		$this->uo = bw_sl( $this->atts );
	}
	
	function end_report() {
		bw_el( $this->uo );
	
	}
	
	function report_field( $key, $field ) {
		//print_r( $field );
		if ( is_array( $field ) ) {
			
			$item = implode( " ", $field );
		} else {
			$item = $field;
		}
		
		//li( "$key $item" );
		echo "$key,$item";
		if ( $this->time_field ) {
			$elapsed = $this->elapsed[ $key ] ;
			$average = $elapsed / $item;
			//echo ",$elapsed";
			echo ",$average"; 
		} else {
			echo "";
		}
		echo PHP_EOL;
	}
	
	
	/**
	 * Report the grouped percentages
	 */
	function report_percentages() {
		if ( !$this->percentages ) {
			$this->percentages();
		}
		
		//$this->start_report();
		//print_r( $this->groups );
		echo "Groups: " . $this->key . PHP_EOL;
		foreach ( $this->percentages as $key => $field ) {
			
			if ( $this->having ) { 
				$having = call_user_func( $this->having, $key, $field );
			} else {
				$having = true;
			}
			if ( $having ) {
				//$this->report_field( $key, $field );
				$this->report_percentage( $key, $field );
			}
		}
		//$this->end_report();
	}
	
	
	function report_percentage( $key, $field ) {
		
		//li( "$key $item" );
		echo "$key,$field";
		if ( $this->time_field ) {
			$item = $this->groups[ $key ];
			$elapsed = $this->elapsed[ $key ] ;
			$average = $elapsed / $item;
			//echo ",$elapsed";
			echo ",$average"; 
		} else {
			echo "";
		}
		echo PHP_EOL;
	}
	
	
	/**
	 * Create a group of percentages
	 */
	function percentages() {
		echo "Building percentages: " . $this->key . PHP_EOL;
		foreach ( $this->groups as $key => $field ) {
			$this->percentage( $key, $field );
		}
	}
	
	/**
	 * Calculate a single percentage
	 */
	function percentage( $key, $field ) {
		if ( is_array( $field ) ) {
			gob(); // Not catered for
		} else {
			$percentage = ( $field * 100) / $this->total;
		}
		$this->percentages[ $key ] = $percentage;
	}
		
	/**
	 * Sort the groups array
	 *
	 * Sort function | Purpose
	 * ------------- | -----------------
	 * krsort        | by key descending e.g. 4.4, 4.3, 4.2		or 2016, 2015, 2014
	 * ksort         | by key ascending e.g. A - Z
	 * arsort        | by value descending for popularity
	 * asort         | by value ascending
	 */
	function krsort() {
		krsort( $this->groups );
	}
	
	function ksort() {
		ksort( $this->groups );
	}
	
	function arsort() {
		arsort( $this->groups );
	}


}
 
