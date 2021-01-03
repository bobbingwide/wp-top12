<?php // (C) Copyright Bobbing Wide 2015, 2016

/**
 * oikwp merger.php file-1.csv file-2.csv
 *
 * @TODO if only file is specified and it's not a full file name then append -2015.csv for the first and -2016.csv for the second
 * 
 * Both files are expected to be simple CSV files with a key and a value
 * e.g.
 * ``` 
 * a,fred
 * b,blogs
 * ```
 */

oik_require( "libs/class-csv-merger.php", "wp-top12" );

$file1 = "words-2015.csv";
$file2 = "words-2016.csv";


$merger = new CSV_merger();

//$fred = array( "a" => "fred", "b" => "blogs" );
//$hilma = array( "a" => "hilma", "b" => "blogs2" );

//$fred = file( "letters-2015.csv" );

//$hilma = array_map( 'str_getcsv', file( "letters-2016.csv" ));

//$fred = array_map('str_getcsv', file('letters-2016.csv'));

/**
 * Convert simple CSV to associative array
 * 
 * @param array $csvs - as returned by file
 * @return array the associative array
 */
function associate( $csvs ) {
	$result = array();
	foreach ( $csvs as $csv ) {
		$parts = str_getcsv( $csv );
		$result[ $parts[0] ] = $parts[1];
		
	}
	return( $result );
}

$fred = associate( file( $file1 ) );
$hilma = associate( file( $file2 ) );


//print_r( $fred );

//gob();
$merger->append( $fred );
$merger->append( $hilma );
$merger->sort();
$merger->report();
