<?php // (C) Copyright Bobbing Wide 2017

/**
 * Measure the effect of counting hooks in oik-bwtrace
 * And compare WordPress 4.6 performance vs 4.7
 * 
 */
 
function count_hooks_loaded() {

	wp_filter_tag( "all" );
	remove_all_actions( "all" ); // , "genesistant_all", 10, 2 );
	wp_filter_tag( "all" );
	wp_filter_tag( "chl" );
	save_globals();
	
	echo "Start: " . get_hooks_count() . PHP_EOL;
	
	run_many_tests(); 
	
	restore_globals();
	
	wp_filter_tag( "all" );
	
	add_action( "all", "bw_trace_count_all", 10, 2 );
	wp_filter_tag( "all" );
	echo "End: " . get_hooks_count() . PHP_EOL;
}

/**
 * Run a load of performance tests
 * 
 * Here we try to see if the number hooks affects the performance of do_action
 * when the hook is not registered.
 * 
 * Then we add "chl" ( which stands for count hooks loaded ) and try again
 * 
 */
function run_many_tests() {
	run_tests();
	
	$hook_counts = array( 10, 100, 1000, 10000 );
	$priorities_ranges = array( array( 10 )	
													, array( 10, 11 )
													, array( 10, 11, 12 )
													);
	foreach ( $hook_counts as $hook_count ) {
		foreach ( $priorities_ranges as $priorities ) {
			reset_globals( $hook_count, $priorities );
		}
		run_tests();
	}	
 
	//bw_trace_off();
	//run_tests();
 	 
	add_action( "chl", "chl_out" );
	add_filter46( "chl", "chl_out" );
	run_tests();
 
}

/**
 * Run some performance tests
 * 
 * In WordPress 4.6, for some reason the results were different on the first run.
 * 
 * 
 */
 

function run_tests() {
	remove_action( "all", "bw_trace_count_all", 10, 2 );
	remove_action( "all", "bw_trace_count_only", 10, 2 );
	time_actions( "No count", 100000 );
	
	add_action( "all", "bw_trace_count_only", 10, 2 );
	time_actions( "Count only", 100000 );
	
	remove_action( "all", "bw_trace_count_only", 10, 2 );
	add_action( "all", "bw_trace_count_all", 10, 2 );
	time_actions( "Count_all", 100000 );
	
	remove_action( "all", "bw_trace_count_all", 10, 2 );
	remove_action( "all", "bw_trace_count_only", 10, 2 );
	
	time_actions( "No count again", 100000 );
	time_actions( "do_action 4.6", 100000, "do_action46" );
	time_actions( "chl_out", 100000, "chl_out" );
	time_actions( "bw_trace_count_only", 100000, "bw_trace_count_only" );
	
}

function reset_globals( $hooks=1000, $priorities ) {

	global $wp_filter, $wp_actions;
	global $wp_filter46, $wp_actions46;
	echo "filters:" . count( $wp_filter ) . PHP_EOL;
	
	//unset( $wp_filter );
	//unset( $wp_actions );
	$wp_filter = null;
	$wp_filter46 = null;
	
	
	//$cfil = count( $wp_filter );
	for ( $cfil = count( $wp_filter)  ; $cfil < $hooks ; $cfil++ ) {
		foreach ( $priorities as $priority ) {
			add_action( "cfil_$cfil", "chl_out", $priority );
			add_filter46( "cfil_$cfil", "chl_out", $priority );
		}
	}
	// $wp_actions46 = $wp_actions;
	
	echo "filters:" . count( $wp_filter ) . PHP_EOL;
	echo "filters46:" . count( $wp_filter46 ) . PHP_EOL;
	
	
}

function save_globals() {
	global $wp_filter_saved, $wp_filter;
	$wp_filter_saved = $wp_filter;
}

function restore_globals() { 
	global $wp_filter_saved, $wp_filter;
	$wp_filter = $wp_filter_saved;
}
	

/**
 * A dummy action hook / filter function
 */
function chl_out() {
	//static $chl = 0;
	//$chl++;
	return( null );
}



function wp_filter_tag( $tag ) {
	global $wp_filter;
	if ( isset( $wp_filter[ $tag ] ) ) {
		print_r( $wp_filter[ $tag ] );
	} else { 
	  echo "Tag $tag not defined" . PHP_EOL;
	}
}


function time_actions( $text, $limit, $func="do_action" ) {
	//$elapsed = bw_trace_elapsed();
	//echo "$text $elapsed" . PHP_EOL;
	$start = bw_trace_timer_stop();
	//echo "$text $start" . PHP_EOL;
	for ( $i = 0; $i < $limit; $i++) {
		$func( "chl" );
		//$j = apply_filters( "chl", $i );
	}
	
	$stop = bw_trace_timer_stop();
	$elapsed = $stop - $start;
	$elapsed = number_format( $elapsed, 7 );
	if ( $limit ) {
		$average = $elapsed / $limit;
	} else {
		$average = 0;
	}
	$average = number_format( $average, 7 );
	echo "$stop $start $elapsed $limit $average $text" . PHP_EOL;

}

function get_hooks_count() {
	global $bw_total_actions;
	return( $bw_total_actions ); 
}
 

count_hooks_loaded();

/**
 * Copy of WordPress 4.6 add_filter 
 */
function add_filter46( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
	global $wp_filter46, $merged_filters;

	$idx = _wp_filter_build_unique_id($tag, $function_to_add, $priority);
	$wp_filter46[$tag][$priority][$idx] = array('function' => $function_to_add, 'accepted_args' => $accepted_args);
	unset( $merged_filters[ $tag ] );
	return true;
}

/**
 * Copy of WordPress 4.6 do_action
 */
function do_action46($tag, $arg = '') {
	global $wp_filter46, $wp_actions46,  $wp_current_filter;

	
	if ( ! isset($wp_actions46[$tag]) )
		$wp_actions46[$tag] = 1;
	else
		++$wp_actions46[$tag];
	
	// Do 'all' actions first
	if ( isset($wp_filter46['all']) ) {
		$wp_current_filter[] = $tag;
		$all_args = func_get_args();
		_wp_call_all_hook46($all_args);
	}

	if ( !isset($wp_filter46[$tag]) ) {
		if ( isset($wp_filter46['all']) )
			array_pop($wp_current_filter);
		return;
	}

	if ( !isset($wp_filter46['all']) )
		$wp_current_filter[] = $tag;

	$args = array();
	if ( is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0]) ) // array(&$this)
		$args[] =& $arg[0];
	else
		$args[] = $arg;
	for ( $a = 2, $num = func_num_args(); $a < $num; $a++ )
		$args[] = func_get_arg($a);

	// Sort
	global $merged_filters;
	if ( !isset( $merged_filters[ $tag ] ) ) {
		ksort($wp_filter46[$tag]);
		$merged_filters[ $tag ] = true;
	}
	
	//if ( false ) {

	reset( $wp_filter46[ $tag ] );

	do {
		foreach ( (array) current($wp_filter46[$tag]) as $the_ )
			if ( !is_null($the_['function']) )
				call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));

	} while ( next($wp_filter46[$tag]) !== false );
	//}
	array_pop($wp_current_filter);
}



/**
 * Copy of WordPress 4.6 _wp_call_all_hook() 
 * 
 * Call the 'all' hook, which will process the functions hooked into it.
 *
 * The 'all' hook passes all of the arguments or parameters that were used for
 * the hook, which this function was called for.
 *
 * This function is used internally for apply_filters(), do_action(), and
 * do_action_ref_array() and is not meant to be used from outside those
 * functions. This function does not check for the existence of the all hook, so
 * it will fail unless the all hook exists prior to this function call.
 *
 * @since 2.5.0
 * @access private
 *
 * @global array $wp_filter  Stores all of the filters
 *
 * @param array $args The collected parameters from the hook that was called.
 */
function _wp_call_all_hook46($args) {
	global $wp_filter46;

	reset( $wp_filter46['all'] );
	do {
		foreach ( (array) current($wp_filter46['all']) as $the_ )
			if ( !is_null($the_['function']) )
				call_user_func_array($the_['function'], $args);

	} while ( next($wp_filter46['all']) !== false );
}
