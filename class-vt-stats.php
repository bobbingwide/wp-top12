<?php // (C) Copyright Bobbing Wide 2015

/**
 * Statistics for ddmm.vt files
 *
 * Implements 
 
 * 
 */
 class VT_stats {

	 /**
	  * Array of VT_row objects loaded from mmdd.vt files
	  */
	 public $rows;


	 /**
	  * from date to load
	  */
	 public $from_date;

	 /**
	  *
	  */
	 public $to_date;

	 public $month;

	 public $having;

	 public $host;

	 public $grouper=null;

	 /**
	  * Construct the source information for VT_stats
	  */
	 function __construct() {
		 $this->from_date();
		 $this->to_date();
		 $this->rows=array();
		 //$this->populate();
		 $this->narrator=Narrator::instance();


	 }

	 function from_date( $from_date=null ) {
		 if ( null == $from_date ) {
			 $from_date=time() - 86400;
		 } else {
			 $from_date=strtotime( $from_date );
		 }
		 $this->from_date=$from_date;
	 }

	 function to_date( $to_date=null ) {
		 if ( null == $to_date ) {
			 $to_date=$this->from_date;
		 } else {
			 $to_date=strtotime( $to_date );
		 }
		 $this->to_date=$to_date;
	 }


	 /**
	  * Populate rows for selected date range
	  */
	 function populate() {
		 $dates    =[];
		 $startdate=$this->from_date;
		 $enddate  =$this->to_date;

		 //echo $this->from_date;
		 //echo $this->to_date;

		 for ( $thisdate=$startdate; $thisdate <= $enddate; $thisdate+=86400 ) {
			 $dates[]=date( "Ymd", $thisdate );

		 }
		 //print_r( $dates );

		 foreach ( $dates as $date ) {
			 $this->load_file( $date );
		 }

		 echo 'Count rows:' . count( $this->rows ) . PHP_EOL;
	 }

	 /**
	  * Sets the source of the trace file.
	  *
	  * @param string $host Source directory ( no trailing slash )
	  */
	 function set_host( $host ) {
		 $this->host=$host;
	 }

	 /**
	  * Populate rows for the given date
	  */
	 function load_file( $date ) {

		 $file    =$this->host . "/bwtrace.vt.$date";
		 $contents=file( $file );
		 echo "Date: $date Count: " . count( $contents ) . PHP_EOL;
		 foreach ( $contents as $line ) {
			 $this->rows[]=new VT_row_basic( $line );
		 }

		 unset( $contents );
	 }

	 /**
	  * Count all the things we want to count by grouping on key values.
	  *
	  * We may need to convert the actual value into a subset.
	  * For the grouping fields see class-vt-row.php
	  *
	  *
	  * Key        | Subset  | What this shows
	  * ---------- | -------  | ---------------------------------------
	  * plugins    | null    | Group by number of active plugins          = 41
	  * files      |              |    ranges from 446 to 556
	  * queries        |                  |    ranges from 16 to 1081 - with some large gaps
	  * elapsed    |    elapsed |
	  *
	  */

	 function count_things() {
		 //$grouper=new Object_Grouper();
		 //echo "Grouping: " . count( $this->rows ) . PHP_EOL;
		 //$grouper->populate( $this->rows );

		 $grouper = $this->populate_grouper();

		 $grouper->subset( null );
		 $grouper->groupby( "suri" ); // Stripped URI
		 $grouper->arsort();
		 $this->having=100;
		 $grouper->having( array( $this, "having_filter_value_ge" ) );
		 $grouper->report_groups();


		 // The 'tl' part of suritl stands for top level not term last!
		 $grouper->time_field( "final" );
		 $grouper->subset( null );
		 $grouper->groupby( 'suritl' ); // Stripped URI top level
		 // we can't sort and expect the elapsed total to be sorted too
		 // so in the mean time don't sort here
		 // $grouper->arsort();

		 //$grouper->report_percentages();
		 // Also there's a bug in report_percentages when mixed with time_field
		 // it calculates the average from the percentage figure not the count.
		 $grouper->having( array( $this, "having_filter_value_ge" ) );
		 $this->having=count( $this->rows ) / 100;
		 $grouper->report_groups();

		 $this->having=0.05;
		 $grouper->report_percentages();


		 /**
		  * $grouper->subset();
		  * $grouper->groupby( "files" );
		  * $grouper->ksort();
		  * $grouper->report_groups();
		  *
		  * $grouper->subset();
		  * $grouper->groupby( "queries" );
		  * $grouper->ksort();
		  * $grouper->report_groups();
		  *
		  * $grouper->subset();
		  * $grouper->groupby( "remote_IP" );
		  * $grouper->ksort();
		  * $grouper->report_groups();
		  */
		 $grouper->having();
		 $grouper->time_field();
		 $grouper->groupby( "elapsed", array( $this, "elapsed" ) );
		 $grouper->ksort();
		 $grouper->report_groups();


		 $grouper->time_field();
		 $grouper->groupby( "final", array( $this, "tenthsecond" ) );
		 $grouper->ksort();
		 $grouper->report_percentages();

		 $merger=new CSV_merger();
		 $merger->append( $grouper->groups );
		 $merger->append( $grouper->percentages );
		 echo "Merged report:" . PHP_EOL;
		 $merger->report();

		 /**
		  * Produce a chart comparing the execution times for each month.
		  * with the total count converted to percentages to enable easier visual comparison
		  *
		  * Only works when more than one month.
		  */
		 if ( false ) {
			 $grouper->where( array( $this, "month_filter" ) );
			 $merger=new CSV_merger();
			 for ( $this->month=10; $this->month <= 12; $this->month ++ ) {
				 $grouper->groupby( "final", array( $this, "elapsed" ) );
				 $grouper->percentages();
				 $merger->append( $grouper->percentages );
			 }
			 $merger->report();
		 }

	 }

	 /**
	  * Return true if the object is supposed to be processed
	  *
	  * yyyy-mm-ddThh:mm:ss
	  * 012345
	  */
	 function month_filter( $object ) {
		 if ( $this->month ) {
			 $isodate=$object->isodate;
			 $month  =substr( $isodate, 5, 2 );
			 $process=$this->month == $month;
			 if ( ! $process ) {
				 //echo $this->month . $month;
				 //gob();
			 }
		 } else {
			 $process=true;
		 }

		 return ( $process );
	 }

	 function having_filter_value_ge( $key, $value ) {
		 $having=$value >= $this->having;

		 return ( $having );
	 }

	 function having_filter_value_le( $key, $value ) {
		 $having=$value <= $this->having;

		 return ( $having );
	 }

	 /**
	  * Round depending on elapsed time
	  *
	  * Experience has shown that we get more in the 0.3 to 0.6 range
	  * so let's break that down into two decimal places
	  * Anything either side we accumulate less granularly.
	  *
	  * @param string $elapsed elapsed time in seconds.microseconds
	  *
	  * @return string grouping to use for this elapsed time
	  */
	 function elapsed( $elapsed ) {
		 $elapsed      =$elapsed * 1.0;
		 // Use two decimal places when you want accuravy to 100th of a second
		 // 1 when you want accuracy to a tenth of a second.

		 $elapsed_range=number_format( $elapsed, 1);
		 if ( $elapsed_range < 0.30 ) {
			 $elapsed_range="<" . number_format( $elapsed, 1, ".", "" );
		 } elseif ( $elapsed_range <= 0.60 ) {
			 //$elapsed_range = number_format( $elapsed, 2, ".", "" );
			 $elapsed_range="<" . $elapsed_range;
		 } elseif ( $elapsed_range <= 0.90 ) {
			 $elapsed_range="<" . number_format( $elapsed, 1, ".", "" );

		 } elseif ( $elapsed <= 5.00 ) {
			 $elapsed_range="<=" . number_format( $elapsed, 0 );
		 } else {
			 $elapsed_range=">5";
		 }
		 //echo "Elapsed: $elapsed $elapsed_range ";
		 //gob();
		 return ( $elapsed_range );
	 }

	 function nthsecond( $elapsed, $denominator=10 ) {
		 $elapsed_range=$this->roundToFraction( $elapsed, $denominator );
		 if ( $elapsed_range > 5) {
			 $elapsed_range = '>5';
		 } else {
			 $elapsed_range = '<' . $elapsed_range;
		 }
		 return $elapsed_range;
	 }

	 function tenthsecond( $elapsed ) {
		 $elapsed_range = $this->nthsecond( $elapsed, 10);
		 return $elapsed_range;
	 }

	 function fifthsecond( $elapsed ) {
		 return $this->nthsecond( $elapsed, 5 );
	 }


	 function roundToFraction($number, $denominator = 5)  {
		 $x = $number * $denominator;
		 $x = round($x);
		 $x = $x / $denominator;
		 return $x;
	 }

	 function count_request_types() {
		 //$grouper=new Object_Grouper();
		//
		 //echo "Grouping: " . count( $this->rows ) . PHP_EOL;
		 //$grouper->populate( $this->rows );

		 $grouper = $this->populate_grouper();

		 $grouper->subset( null );
		 $grouper->groupby( "request_type" ); // Stripped URI
		 $grouper->arsort();
		 //$this->having = 100;
		 //$grouper->having( array( $this, "having_filter_value_ge" ) );
		 echo "<h3>Categorised requests</h3>";
		 echo '[chart type=Bar]Type,Count' . PHP_EOL;
		 $grouper->report_groups();
		 echo '[/chart]' . PHP_EOL;
	 }

	 function time_request_types() {
		 $grouper = $this->populate_grouper();
		$grouper->time_field();
		 $grouper->subset( null );
		 $grouper->groupby( "request_type" ); // Stripped URI
		 $grouper->arsort();
		 //$this->having = 100;
		 //$grouper->having( array( $this, "having_filter_value_ge" ) );
		 echo ' ' . PHP_EOL;
		 echo "<h3>Categorised request time</h3>";
		 echo '[chart type=Bar]Type,Elapsed' . PHP_EOL;
		 $grouper->report_percentages();
		 echo '[/chart]' . PHP_EOL;
	 }

	 function populate_grouper() {
		 //$grouper=new Object_Grouper();
		 if ( !$this->grouper ) {
			 $this->grouper =new Object_Grouper();
		 }
		 if ( $this->grouper ) {
		    echo "Grouping: " . count( $this->rows ) . PHP_EOL;
		    $this->grouper->populate( $this->rows );
	     } else {
		 	echo "Populate_grouper broken" . PHP_EOL;
		 }
		 return $this->grouper;
    }







	 }


