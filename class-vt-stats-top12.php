<?php // (C) Copyright Bobbing Wide 2015, 2016

/**
 * VT_stats_top12 
 */
 
class VT_stats_top12 extends VT_stats {

	/**
	 * Populate rows for the given plugin
	 */
	function load_file( $plugin, $host="2016" ) {
		$file = "$host/$plugin.csv";
		echo $file;
		$contents = file( $file );
		echo "Plugin: $plugin Count: " . count( $contents ) . PHP_EOL;
		foreach ( $contents as $line ) {
			$this->rows[] = new VT_row( $line );
		}
		unset( $contents );
	}
	
	/**
	 * 
	 */
	function count_things() {
		$grouper = new Object_Grouper();
		echo "Grouping: " . count( $this->rows ) . PHP_EOL;
		$grouper->populate( $this->rows );
		$grouper->time_field( "final" );
		$grouper->where( array( $this, "lt6secs" ) );
		$grouper->groupby( "final", array( $this, "elapsed" ) );
		
		$this->having = 6.0;
		$grouper->having( array( $this, "having_filter_value_le" ) );
		$grouper->percentages();
		return( $grouper );
	}
	
	function lt6secs( $object ) {
		$group = $object->final <= 6.0;
		if ( !$group ) {
			echo $object->uri . ":" . $object->final . PHP_EOL;
			$group = true;
		}
		return( $group );
	}
	
	
	/**
	 * Round depending on elapsed time
	 * 
	 * Experience has shown that we get more in the 0.3 to 0.6 range
	 * so let's break that down into two decimal places
	 * Anything either side we accumulate less granularly.
	 * 
	 * @param string $elapsed  elapsed time in seconds.microseconds
	 * @return string grouping to use for this elapsed time
	 */ 
	function elapsed( $elapsed ) {
		$elapsed = $elapsed * 1.0;
		$elapsed_range = number_format( $elapsed, 1 );
		if ( $elapsed_range > 6.0 ) {
			$elapsed_range = ">>>";
		}   elseif ( $elapsed_range > 2.5 ) {
			$elapsed_range = "2.6+";
		}
		
		/* 
		if ( $elapsed_range < 0.30 ) {
			$elapsed_range = "<" . number_format( $elapsed, 1, ".", "" );
		} elseif ( $elapsed_range <= 0.60 ) {
			//$elapsed_range = number_format( $elapsed, 2, ".", "" );
			$elapsed_range = "<" . $elapsed_range;
		} elseif ( $elapsed_range <= 0.90 ) {
			$elapsed_range = "<" . number_format( $elapsed, 1, ".", "" );
		
		} elseif ( $elapsed <= 5.00 ) {
			$elapsed_range = "<=".number_format( $elapsed, 0 );
		} else { 
			$elapsed_range = ">5";
		}
		//echo "Elapsed: $elapsed $elapsed_range ";
		//gob();
		*/
		return( $elapsed_range );
	}





}
