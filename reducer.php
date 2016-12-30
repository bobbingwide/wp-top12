<?php // (C) 

$gts = file( "gt100-2016.csv" );
$outputs = file( "20161224/vanilla-wp47-1.csv" );

echo count( $gts );
echo PHP_EOL;
echo count( $outputs );
echo PHP_EOL;

$cgts = count( $gts);

$gtsx2 = $cgts * 2;
echo $gtsx2;
echo PHP_EOL;

if ( $gts != count( $outputs) ) {
	echo "More work to do" . PHP_EOL;
}

$g = 0;
$o = 0;

for ( $g = 0; $g < $cgts; $g++ ) {
	$pg = explode( ",", $gts[ $g ] );
	$po = explode( ",", $outputs[ $o ] );
	
	$pi =  "/oikcom" . $pg[0];
	
	$po = $po[0]; 
	echo $g . PHP_EOL;
	
	echo $pi;
	echo PHP_EOL;
	echo $po;
	
  echo PHP_EOL;
	if ( $pi != $po ) {
		$o++;
		echo "Mismatch " . PHP_EOL;
		
		$po = explode( ",", $outputs[ $o ] );
		$po = $po[0]; 
		if ( $pi != $po ) {
			gob();
		} else {
			echo $po;
			echo PHP_EOL;
			echo "Realigned";
			echo PHP_EOL;
			
		}
		
	} 
	$o++;
}
