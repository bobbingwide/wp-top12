<?php // (C) Copyright Bobbing Wide 2015-2017

/**
 * VT_stats_top12
 * 
 * Compare performance when each of the top 12 WordPress plugins have been activated
 * Having produced an oik-bwtrace summary log for a whole range of transactions
 * we group them by the final time counting the number of results in the selected
 * range and outputting the information in a CSV file that can be shown visually.
 * 
 * For some plugins this clearly shows that the whole shooting match takes longer.
 * For the really bad ones it's pretty obvious that it's taking longer.
 * But these results alone don't indicate what's causing the problem.
 
 * For others plugins the results might appear that the system is running faster.
 * My gut feel is that this is just background variation.
 * 
 */
 
class VT_stats_top12 extends VT_stats {

	/**
	 * Populuate rows for a given group of files 
	 */
	function load_group( $group, $host="2016" ) {
		$files = bw_as_array( $group );
		foreach ( $files as $file ) {
			$this->load_file( $file, $host );
		}	
	}

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
	 * Count by grouping on the final time
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
	
	/**
	 * Ignore results exceeding 6 seconds
	 */
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
		if ( $elapsed < 0.1 ) {
			$elapsed_range = number_format( $elapsed, 1 );
		} elseif ( $elapsed < 0.8 ) { 
			$elapsed_range = number_format( $elapsed, 2 );
		} elseif ( $elapsed < 2.6 ) {
			$elapsed_range = number_format( $elapsed, 1 );
		} elseif ( $elapsed < 6.0 ) {
			$elapsed_range = "2.6+";
		} else {
			$elapsed_range = ">>>";
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
	
	
	
	/**
	 * Count by grouping on the suritl 
	 * 
	 */
	function count_things_differently() {
		$grouper = new Object_Grouper();
		echo "Grouping: " . count( $this->rows ) . PHP_EOL;
		$grouper->populate( $this->rows );
		$grouper->time_field( "final" );
		$grouper->where( array( $this, "lt6secs" ) );
		$grouper->groupby( "surisl", array( $this, "surisl" ) );
		
		$this->having = 6.0;
		$grouper->having( array( $this, "having_filter_value_le" ) );
		//$grouper->percentages();
		//$grouper->averages();
		return( $grouper );
	}
	
	
	
	/**
	 * Count by grouping on the number of queries
	 * 
	 */
	function count_things_by_queries() {
		$grouper = new Object_Grouper();
		echo "Grouping: " . count( $this->rows ) . PHP_EOL;
		$grouper->populate( $this->rows );
		$grouper->time_field( "final" );
		$grouper->where( array( $this, "lt6secs" ) );
		$grouper->groupby( "queries", array( $this, "queries" ) );
		
		$this->having = 6.0;
		$grouper->having( array( $this, "having_filter_value_le" ) );
		//$grouper->percentages();
		return( $grouper );
	}
	
	function surisl( $surisl ) {
		//echo $surisl . PHP_EOL;
		return( $surisl );
	
	}
	
	/**
	 * 
	 */
	
	function queries( $queries ) {
		$qs = $queries;
		if ( $queries < 100 ) 
			return( "<100" );
		if ( $queries < 200 ) 
			return( "<200" );
		return( "<max" );
		return( $queries );
	}





}
