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
	private $total_downloads;

	function __construct() {
		$this->file_or_url = 'wporg_plugins.csv';
		$this->limit = 12;
		$this->includes = null;
		$this->excludes = null;
		$this->both = false;
		$this->total_downloads = 0;
		//$this->plugin_info_v2( "oik" );

	}

	function atts( $atts ) {
		$this->limit = bw_array_get_from( $atts, 'limit,0', null );
		$includes = bw_array_get( $atts, 'includes', null );
		$this->includes = bw_as_array( $includes );
		$excludes = bw_array_get( $atts, 'excludes', null );
		$this->excludes = bw_as_array( $excludes );
		if ( null === $includes && null === $excludes && null === $this->limit ) {
			$this->limit = 12;
		}
		if ( null === $this->limit ) {
			$this->limit = 1000;
		}
		$both = bw_array_get( $atts, 'both', 'N' );
		$this->both = bw_validate_torf( $both );
	}

	function full_file_or_url() {
		$file = oik_path( $this->file_or_url, 'wp-top12');
		if ( !file_exists( $file ))
			echo "You need to get the file";
		return $file;
	}

	function load_plugins() {
		$this->plugins = file( $this->full_file_or_url() );
		//unset( $this->plugins[0] );
		//array_shift( $this->plugins );
	}

	function count_plugins() {
		$count = count( $this->plugins );
		$count--;
		return $count;
	}

	function plugin_info_v2( ) {
		$this->load_plugins();
		$total = $this->count_plugins();
		p( "Total plugins: " . $total );
		$this->filter();
		$this->report();
		if ( $this->both ) {
			$this->included = $this->excluded;
			$this->report();
		}
	}

	function filter() {
		$this->included = [];
		$this->excluded = [];
		$count          = 0;
		foreach ( $this->plugins as $key => $info ) {
			if ( $key === 0 ) {
				continue;
			}

			if ( $this->include( $key, $info ) ) {
				if ( $this->exclude( $key, $info ) ) {
					$this->excluded[] = $this->build_include_or_exclude( $key, $info );
				} else {
					$this->included[] = $this->build_include_or_exclude( $key, $info );
					$count++;
					if ( $count >= $this->limit ) {
						//e( 'Limit reached');
						break;

					}
				}
			}
		}
	}

	function include( $key, $info ) {
		if ( 0 === count( $this->includes ) ) {
			return true;
		}
		$included = false;
		foreach ( $this->includes as $plugin ) {
			$pos = stripos( $info, $plugin );
			if ( false !== $pos ) {
				$included = true;
			}
		}
		return $included;

	}

	function exclude( $key, $info ) {
		if ( 0 === count( $this->excludes ) ) {
			return false;
		}
		$excluded = false;
		foreach ( $this->excludes as $plugin ) {
			$pos = stripos( $info, $plugin );
			if ( false !== $pos ) {
				$excluded = true;
			}
		}
		return $excluded;
	}

	function build_include_or_exclude( $key, $info ) {
		$included = $key;
		$included .= ',';
		$info = str_replace( " ", '&nbsp;', $info );
		$included .= $info;
		return $included;

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
		stag( "table", "wp-top12" );
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

		bw_tablerow( [ count( $this->included ), null, $this->total_downloads ]);
		etag( "table");

	}

	/** Mapping of  key,info to fields *
	 * index | contains
	 * 0 |  key = position in table
	 * 1 | Slug
	 * 2 | Name
	 * 3 | Rating
	 * 4 | Downloaded
	 * 5 | Installed = active
	 * 6 | Tested
	 * 7 | Requires
	 * 8 | LastUpdate
	 */

	function plugin_link( $plugin ) {
		$url = 'https://wordpress.org/plugins/';
		$url .= $plugin[1];
		return retlink( null, $url, $plugin[2]);
	}

	function plugin_total_downloads( $plugin ) {
		$this->total_downloads += $plugin[4];
		return $plugin[4];
	}
	function plugin_active( $plugin ) {
		return $plugin[5];
	}
	function plugin_rating( $plugin ) {
		return $plugin[3];
	}
	function plugin_tested_up_to( $plugin ) {
		return $plugin[6];
	}

}