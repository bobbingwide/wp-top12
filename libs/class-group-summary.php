<?php // (C) Copyright Bobbing Wide 2016


/**
 * Group_Summary
 * 
 * Used to summarise information from a set of groups
 * 
 * in order to produce a table showing the affect of something against the baseline
 
 * Name  | Count |  Total | Average | +/-   | %age
 * -------- | ----- | ------ | ------- | ----- | ----- 
 * baseline | 1000  |  500   | 0.500   | 0.000 | 
 * double   | 1000  | 1000   | 1.000   | 0.500 | + 100%
 * triple   | 1000  | 1500   | 1.500   | 1.000 | + 200%
  
 * 
 * 
 */
 
class Group_Summary {
 
	/**
	 * Array containing some or all of the above fields
	 */
	public $groups;
	
	
 
	
	function __construct() {
	} 
	 
	function add_group( $name, $total_count, $total_time ) {
		$average =  $this->average( $total_count, $total_time );
		
		$group = array( "name" => $name
									, "count" => $total_count
									, "total" => $total_time
									, "average" => $average
									);
		
		$group = $this->plusorminus( $group );
		$group_object = ( object ) $group;
		$this->groups[] = $group_object;
		
		
	}
	
	function report( $header=true ) {
		echo "Summary report: " . PHP_EOL;
		//print_r( $this->groups );
		
		foreach ( $this->groups as $group ) {
			if ( $header ) {
				$line = implode( array_keys( (array) $group ), "," );
				echo $line . PHP_EOL;
        $header = false;
			}
			$line = implode( array_values( (array) $group ), "," );
			echo $line . PHP_EOL;
		}
	}
	
	function average( $total_count, $total_time ) {
		if ( $total_count ) {
			$average = $total_time / $total_count;
			
		}	else {
			$average = 0;
		}
		return( $average );
	}
	
	function plusorminus( $group ) {
		if ( isset( $this->groups[0] ) ) {
			$base = $this->groups[0]->average;
			$plusorminus = $group[ "average"] - $base;
			$percentage = $plusorminus / $base * 100;
		} else {
			$plusorminus = 0;
			$percentage = 0;
		}
		$group["plusorminus"] = $plusorminus;
		$group["percentage" ] = $percentage; 
		return( $group );
	}
		
	
	
 
 
 
 
}
 
