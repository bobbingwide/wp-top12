<?php
/**
 * @copyright (C) Copyright Bobbing Wide 2019
 * @package wp-top12
 *
 */

class WP_org_plugins {

	private $plugins; // Loaded file_or_url
	private $limit; // Maximum rows to return ( not incl. header )
	private $file_or_url; // Source of information
	private $includes; // CSV string of words to search for
	private $excludes; // CSV string of words to exclude

	private $included;
	private $excluded;

	function __construct() {
		$this->file_or_url = 'wporg_plugins.csv';
		$this->limit = 12;
		$this->includes = null;
		$this->excludes = null;
		$this->plugin_info_v2( "oik" );

	}

	function full_file_or_url() {
		$file = oik_path( $this->file_or_url, 'wp-top12');
		if ( !file_exists( $file ))
			echo "You need to get the file";
		return $file;
	}

	function load_plugins() {
		$this->plugins = file( $this->full_file_or_url() );
	}

	function count_plugins() {
		$count = count( $this->plugins );
		$count--;
		return $count;
	}

	function plugin_info_v2( $plugin ) {
		$this->load_plugins();
		$total = $this->count_plugins();
		p( "Total plugins: " . $total );
		$this->filter( $plugin );
		$this->report();
	}

	function filter( $plugin=null ) {
		$this->included = [];
		$this->excluded = [];

		foreach ( $this->plugins as $key => $info ) {

			$pos = stripos( $info, $plugin );
			if ( false !== $pos ) {
				//echo "$key,";
				//echo $info;
				$included = $key;
				$included .= ',';
				$info = str_replace( " ", '&nbsp;', $info );
				$included .= $info;
				$this->included[] = $included;
				//echo PHP_EOL;

			}
		}
	}

	/**
	 * Report the headings for the table
	 * These may need moving around
	 */

	function report_header() {
		$th = [ "Position","Plugin","Total downloads","Active","Star Rating","Tested up to" ];
		bw_tablerow( $th, "tr", "th"  );
	}


	function report() {
		stag( "table" );
		$this->report_header();
		//bw_tablerow( "Posn,Plugin,");
		foreach ( $this->included as $key => $included ) {
			$plugin = bw_as_array( $included );
			$row = [];
			$row[] = $plugin[0];
			$row[] = $this->plugin_link( $plugin );
			$row[] = $this->plugin_total_downloads( $plugin );
			$row[] = $this->plugin_active( $plugin );
			$row[] = $this->plugin_rating( $plugin );
			$row[] = $this->plugin_tested_up_to( $plugin );
			bw_tablerow( $row  );
		}
		etag( "table");

	}

	function plugin_link( $plugin ) {
		$url = 'https://wordpress.org/plugins/';
		$url .= $plugin[1];
		return retlink( null, $url, $plugin[2]);
	}

	function plugin_total_downloads( $plugin ) {
		return $plugin[4];
	}
	function plugin_active( $plugin ) {
		return $plugin[6];
	}
	function plugin_rating( $plugin ) {
		return $plugin[3];
	}
	function plugin_tested_up_to( $plugin ) {
		return $plugin[5];
	}

}