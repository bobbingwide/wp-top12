<?php // (C) Copyright Bobbing Wide 2015-2017, 2019


/**
 * Class WP_org_downloads obtains information about all the plugins on WordPress.org
 * in order to make some sense of it for top-10-wp-plugins.com
 *
 *
 */
class WP_org_downloads {

	public $response;

	public $plugins;

	public $csv;

	public $downloaded;

	public $preselection;

	/**
	 * Constructor for the WP_org_downloads class
	 *
	 * The constructor doesn't really need to do anything except load the required files?
	 */
	function __construct( ) {
		// No need to do anything really
		$this->reset();
		$this->response = null;
		$this->downloaded = 0;
	}

	/**
	 * Get information for a specific plugin
	 *
	 * Store the results in $this->response
	 *
	 * Using https://api.wordpress.org/plugins/info/1.0/oik.info
	 * gets the information as serialized data which we convert into an object
	 *
	 * Using https://api.wordpress.org/plugins/info/1.0/oik.json
	 * gets the information as JSON

	 * Turns out that the above information was incorrect


	Essentially, you're using an endpoint which is not officially supported (and one which I've never actually seen before).
	If you eliminate that weird .info thing, then you'll get the 1.0 endpoint proper, which gives serialized PHP data.

	If you want json, then the correct endpoint would be more like this:
	https://api.wordpress.org/plugins/info/1.1/?action=plugin_information&request[slug]=oik

	The .info is likely some kind of side-effect that was never intended to be used as any form of official endpoint.
	 *
	 * Here's the link to the API: https://codex.wordpress.org/WordPress.org_API
	 *
	 * there's a 1.2 request as well
	 *
	 * https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request
	 *
	 *
	 * @param string $plugin_slug
	 *
	 */
	function get_download( $plugin_slug ) {
		$request_url = "http://api.wordpress.org/plugins/info/1.0/$plugin_slug";
		$this->response = bw_remote_get2( $request_url ); //, null );
		//print_r( $response_xml );

		//$try_again = unserialize( $this->response );
		//print_r( $try_again );
	}

	/**
	 * Get the download count for the selected plugin
	 *
	 * @return integer the total downloaded
	 */
	function get_download_count() {
		//print_r( $this->response );
		$count = $this->response->downloaded;
		//gob();
		return( $count );
	}

	/**
	 * Get installs
	 *
	 * Not sure how WP.org does this yet
	 *
	 * perhaps we use get_plugin_stats() ?
	 */
	function get_installs() {
	}

	/**
	 * query information about all 5x,xxx plugins
	 *
	 * We work our way backwards through the list
	 * having first requested page 1 to find out how many pages there should be
	 * If the number has changed from last time then what do we know?
	 *
	 *
	 */
	function query_all_plugins() {

		$page = 1;
		$this->query_plugins( $page );
		$this->save_plugins( $page );

		$pages = $this->response->info['pages'];

		$page = $pages;

		while ( $page > 1 ) {
			echo "Processing page: $page" . PHP_EOL;
			$this->query_plugins( $page );
			$this->save_plugins( $page );
			$page--;
		}
	}

	/**
	 * Load the information from a local cache
	 *
	 */
	function load() {
		gob();
		$plugins = file_get_contents( "wporg_saved.plugins" );
		$saved = file( "wporg_saved.plugins" );

		echo "Count: " . count( $plugins ) . PHP_EOL;
		echo "Count saved:" . count( $saved ) . PHP_EOL;
		//$this->plugins = file( "wporg_load.plugins" );
	}

	/**
	 * Load the information from a local cache - serialized version
	 * Note the each plugin is now stored as an array not an object.
	 */
	function load_plugins( $page ) {
		$loaded = file_exists( "wporg_saved.plugins.$page" );
		if ( $loaded ) {
			$plugins_string = file_get_contents( "wporg_saved.plugins.$page" );
			if ( false === $plugins_string ) {
				$loaded = false;
			} else {
				$plugins = unserialize( $plugins_string );
				$loaded = count( $plugins );
				echo "Count: " . $loaded . PHP_EOL;
				$this->add_plugins( $plugins );
				//$this->plugins = $plugins;
			}
		}
		return( $loaded );
	}

	/**
	 * Load all the plugins from the serialized results
	 *
	 * @TODO Will do 55,000 plugins
	 */
	function load_all_plugins() {
		$max_pages=2;
		$max_pages = 620;
		for ( $page = 1; $page <= $max_pages; $page++ ) {
			echo "Loading page: $page " . PHP_EOL;
			$loaded = $this->load_plugins( $page );
		}
	}

	/**
	 * Add the latest set to the total list
	 */
	function add_plugins( $plugins ) {
		//print_r( $this->plugins );
		$this->plugins += $plugins;
		$count = count( $this->plugins );
		echo "Loaded: " . $count . PHP_EOL;
	}


	/**
	 * Save the information to a local cache
	 * - Note different file name while developing
	 */
	function save() {
		gob();
		foreach ( $this->plugins as $plugin => $data ) {
			echo "Saving: $plugin" ;
			$string = serialize( $data );
			//print_r( $string );
			echo "Length:" .  strlen( $string );
			$saved = file_put_contents( "wporg_saved.plugins", $string, FILE_APPEND );
			echo "Written: $saved" ;
			echo PHP_EOL;

		}
	}

	/**
	 * Save the plugins array
	 *
	 */
	function save_plugins( $page ) {
		bw_trace2();
		$string = serialize( $this->plugins );
		$saved = file_put_contents( "wporg_saved.plugins.$page", $string );
		$this->reset();
		bw_trace2();
	}

	function reset() {
		unset( $this->plugins );
		$this->plugins = array();
	}

	/**
	 * Query plugins on WordPress.org
	 *
	 *
	 * The result is expected to be a stdClass Object of two arrays: info and plugins
	 *
	 * - 'info' contains 'page', 'pages' and 'results'
	 * - 'plugins' contains an array of plugin Objects
	 *
	 * `stdClass Object
	 * (
	 *	 [info] => Array
	 *			 (
	 * 				 [page] => 1
	 *				 [pages] => 418
	 *				 [results] => 41777
	 *			 )
	 *
	 *  [plugins] => Array
	 *
	 * Freaky. Today (17th October 2019) the results are:
	 * pages 618, results 61778
	 *
	 * `
	 *
	 * We can control the fields in the plugins array using the "fields" parameter.
	 * Don't yet know where on wp.org the code is to implement the back end.
	 *
	 *
	 * We know we can handle 100 plugins per page, and 1000 is too many for 15 seconds
	 * so lets stick with 100 for the time being.
	 *
	 * The plugins are returned on most recently updated order
	 *
	 */
	function query_plugins( $page=1 ) {
		$fields = array( 'description' => false
		, 'sections' => false
		, 'tested' => true
		, 'requires' => true
		, 'rating' => true
		, 'downloaded' => true
		, 'downloadlink' => false
		, 'last_updated' => true
		, 'homepage' => true
		, 'tags' => false
		);
		$args = array( "per_page" => 100
		, "page" => $page
		, "fields" => $fields
		);
		$this->response = plugins_api( "query_plugins", $args );
		$this->store_plugins();
	}

	/**
	 * Store the results
	 *
	 * @TODO Handle the result which could be a WP_Error object
	 *
	 */
	function store_plugins() {
		$this->report_info();
		$plugins = $this->response->plugins;
		foreach ( $plugins as $key => $plugin ) {
			$this->store( $plugin );
		}
	}

	/**
	 * Store a single result
	 *
	 * $param plugin this is now an array. We used to treat is as an object!
	 */
	function store( array $plugin ) {
		//print_r( $plugin );

		static $count = 0;
		$slug = $plugin['slug'];
		//gob();
		$this->plugins[ $slug ] = $plugin;
		$count++;
		//echo "Storing: $count $slug" . PHP_EOL;
	}

	/**
	 * Report on the total plugins info
	 */
	function report_info() {
		if ( $this->response->info ) {
			echo "Info: ";
			echo "Page: ";
			echo $this->response->info['page'];
			echo PHP_EOL;
			echo "Pages: ";
			echo $this->response->info['pages'];
			echo PHP_EOL;
			echo "Results: ";
			echo $this->response->info['results'];
			echo PHP_EOL;
		} else {
			echo "This response info not set";
			//print_r( $this );
		}
	}

	/**
	 *
	 */
	function display( $plugin ) {
		//print_r( $plugin );

		//print_r( $plugin );
		//gob();
		$slug = $plugin->slug;
		$name = str_replace( ",", "",  $plugin->name );
		$rating = $plugin->rating;
		$downloaded = $plugin->downloaded;
		if ( isset( $plugin->tested ) ) {
			$tested = $plugin->tested;
		} else {
			$tested = " (null)";
			$plugin->tested = null;
		}

		$this->csv .= "$slug,$name,$rating,$downloaded,$tested" . PHP_EOL;
		return( $downloaded );
	}

	/**
	 *
	 */
	function summarise( $file="wporg_plugins.csv" ) {
		$this->csv = "Slug,Name,Rating,Downloaded,Tested" . PHP_EOL;

		foreach ( $this->plugins as $slug => $plugin ) {

			$plugin = ( object ) $plugin;
			//print_r( $plugin );
			//gob();
			$downloaded = $this->display( $plugin );
			$this->downloaded( $downloaded );
		}
		file_put_contents( $file, $this->csv );
		echo "Total downloaded: " . $this->downloaded . PHP_EOL;

	}

	function downloaded( $downloaded ) {
		$this->downloaded += $downloaded;
	}

	/**
	 * Count all the things we want to count by grouping
	 * on key values.
	 *
	 * We need to convert the actual value into a subset.
	 *
	 *
	 * Key        | Subset | What this shows
	 * ---------- | ------ | ---------------------------------------
	 * downloaded | 10**n  | Group by download range
	 *
	 *
	 */

	function count_things() {
		$grouper = new Object_Grouper();
		echo "Grouping: " . count( $this->plugins ) . PHP_EOL;
		$grouper->populate( $this->plugins );

		/**
		 * Graph, Chart, Backup, SEO, Security, Shortcode, Tooltip, User, Map, Slideshow, Audio, Pop, Chat, Contact, commerce, Ad, Learning
		 */
		$this->preselect( "Chart,Graph,Backup,SEO,Security,Shortcode,Tooltip,User,Map,Slideshow,Audio,Pop,Chat,Contact,Commerce,Advert,Learning" );

		$this->preselect( "block,SEO,shortcode,security,backup");
		$grouper->groupby( "name", array( $this, "preselected" ) );
		$grouper->ksort();
		$grouper->report_groups();

		$grouper->groupby( "requires", array( $this, "versionify" ) );
		$grouper->ksort();
		$grouper->report_groups();

		$grouper->groupby( "tested", array( $this, "versionify" ) );
		$grouper->krsort();
		$grouper->report_groups();

		$grouper->groupby( "rating", array( $this, "stars" ) );
		$grouper->krsort();
		$grouper->report_groups();

		$grouper->subset( array( $this, "year" ) );
		$grouper->groupby( "last_updated" );
		$grouper->krsort();
		$grouper->report_groups();

		$grouper->groupby( "downloaded", array( $this, "tentothe" ) );
		$grouper->ksort();
		$grouper->report_groups();



		$grouper->groupby( "name", array( $this, "firstletter" ) );
		$grouper->ksort();
		$grouper->report_groups();

		//$grouper->groupby( "slug", array( $this, "firstletter" ) );
		//$grouper->ksort();
		//$grouper->report_groups();
		$grouper->groupby( "name", array( $this, "words" ) );
		$grouper->arsort();
		$grouper->report_groups();


	}

	/**
	 * Select the items we're interested in
	 *
	 *
	 */
	function preselect( $things ) {
		$this->preselection = bw_as_array( $things );
	}

	/**
	 * Count things we're interested in
	 *
	 * The rest go in the null bucket
	 *
	 */
	function preselected( $value ) {
		$selected = null;
		$value = strtolower( $value );
		foreach ( $this->preselection as $key => $preselection ) {
			$preselection = strtolower( $preselection );
			if ( false !== strpos( $value, $preselection ) ) {
				$selected = $preselection;
			}
		}
		return( $selected );
	}

	/**
	 * There were nearly 1700 combinations for version
	 * we want it simpler
	 *
	 * Other
	 * 0.70 to 2.9
	 * 3.0 to 3.9
	 * 4.0
	 * 4.1
	 * 4.2
	 * 4.3
	 * 4.4
	 */
	function versionify( $version ) {
		$ver3 = $this->npointm( $version );
		if ( is_numeric( $ver3 ) ) {
			if ( $ver3 <= 3.0 ) {
				$ver = "0.70 to 2.9";
			} elseif ( $ver3 <= 3.9 ) {
				$ver = "3.0 to 3.9";
			} elseif ( $ver3 > 5.3 )	{
				$ver = "Other+" ;
			} else {
				$ver = $ver3;
			}

		} else {
			$ver = "Other";
		}
		return( $ver );


	}

	/**
	 * Reduce the rating to star value
	 *
	 *
	 *
	 * Value  | Rating
	 * ------ | ------
	 * 0			| Unrated
	 * 1-10%	|
	 * 11-20%	|						1 star
	 * 21-30%	|
	 * 31-40% | 					2 stars
	 * 41-50% |
	 * 51-60% |           3 stars
	 * 91-99% |
	 * 100%   | Perfect!	5 stars
	 */
	function stars( $rating ) {
		//echo $rating;
		//echo "£";

		$rating = $rating / 20;
		$rating = number_format( $rating, 0 );
		return( $rating );

	}

	function year( $date ) {
		return( substr( $date, 0, 4 ) );
	}


	function tentothe( $value ) {
		$value = strlen( $value );
		$value = pow( 10, $value-1 ) . "->" . pow( 10, $value );
		return( $value );
	}

	/**
	 * Return the uppercase first letter or digit
	 * which is _, digit or alphabetic
	 *
	 *
	 *
	 *
	 *
	 */
	function firstletter( $value ) {
		$val = null;
		$matched = preg_match( '/(_|[0-9a-zA-Z])/', $value, $matches );
		if ( $matched ) {
			$val = $matches[0];
			$val = strtoupper( $val );
		}
		return( $val );
	}

	/**
	 * Reduce to n.m
	 *
	 * which may go a bit duff for null values
	 *
	 * @param string the given value for requires or tested
	 * @return string what we think they meant
	 */
	function npointm( $value ) {
		$ver = null;
		$matched = preg_match( '/[0-9].[0-9]/', $value, $matches );
		if ( $matched ) {
			$ver = $matches[0];
		}
		return( $ver );
	}

	function words( $value ) {
		$value = strtolower( $value );
		$matched = preg_match_all( "/[a-z]+/", $value, $matches );
		if ( $matched ) {
			$values = $matches[0];
		} else {
			$values = array( null );
		}
		return( $values );
	}

	/**
	 * Produce a table of the top 1000 (or so )
	 *
	 * Here we need to sort the $plugins array using our own sort method
	 *
	 * Results to determine:
	 * Top 100 by total downloads
	 * Top 100 by rating
	 * Top 1000 by Install base - don't know about this.
	 *
	 */
	function top1000() {
		$sorter = new Object_Sorter();
		echo "Sorting: " . count( $this->plugins ) . PHP_EOL;
		$sorted = $sorter->sortby( $this->plugins, "downloaded", "desc" );

		$top1000 = $sorter->results( 50 );
		//echo $this->csv;
		$this->report_top1000( $top1000 );

		// List every single plugin sorted by plugin slug
		// $top1000 = $sorter->resort( "slug", "asc" );
		// $this->report_top1000( $top1000 );

	}

	/**
	 * Produce a simple report of the selected items
	 *
	 * @TODO Allow selection of the fields to be shown using
	 * a fields string/array
	 *
	 * And automatically create the column headings based on the given field names
	 *
	 */
	function report_top1000( $top1000 ) {
		echo "Displaying: " . count( $top1000 ) . PHP_EOL;
		foreach ( $top1000 as $plugin ) {
			$plugin = (object) $plugin;
			echo $plugin->slug;
			echo ",";
			echo $plugin->downloaded;
			echo PHP_EOL;
		}
	}


	/**
	 * Sort the full list of items
	 *
	 * When the items are not loaded by WP_Query() then we need to put them in the required order manually
	 * using the defined sort sequence.
	 *
	 * Note: WP_List_Table doesn't cater for sorting on multiple columns, so we don't either
	 */
	function sort_items() {
		$this->orderby();
		$this->order();
		$this->populate_orderby_field();
		usort( $this->items, array( $this, "sort_objects_by_code" ) );
	}

	/**
	 * Populate values for the field we're sorting on
	 */
	function populate_orderby_field() {
		$orderby = $this->orderby;
		foreach ( $this->items as $item => $code ) {
			if ( !$code->{$orderby} ) {
				$code->{$orderby}();
			}
		}
	}



	/**
	 * This is what a plugin Object contains
	C:\apache\htdocs\wordpress\wp-content\plugins\play\class-object-grouper.php(69:32) Object_Grouper::group(1) 378 2015-12-11T11:55:15+00:00 2.697747 0.149402 cf! 10 0 51883376/52066064 F=450 1 stdClass Object
	(
	[name] => Responsive WordPress Slider - Soliloquy Lite
	[slug] => soliloquy-lite
	[version] => 2.4.0.5
	[author] => <a href="http://thomasgriffinmedia.com">Thomas Griffin</a>
	[author_profile] => //profiles.wordpress.org/griffinjt
	[contributors] => Array
	(
	[griffinjt] => //profiles.wordpress.org/griffinjt
	)

	[requires] => 3.5.1
	[tested] => 4.3.1
	[compatibility] => Array
	(
	[4.3.1] => Array
	(
	[2.4.0.4] => Array
	(
	[0] => 100
	[1] => 1
	[2] => 1
	)

	)

	)

	[rating] => 70
	[num_ratings] => 104
	[ratings] => Array
	(
	[5] => 60
	[4] => 2
	[3] => 7
	[2] => 5
	[1] => 30
	)

	[downloaded] => 788949
	[last_updated] => 2015-12-10 7:39pm GMT
	[homepage] => http://soliloquywp.com
	[short_description] => The best responsive WordPress slider plugin. Made lite and free.
	)


	 */


}
