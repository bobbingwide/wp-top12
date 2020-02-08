<?php
/**
 * Information about all plugins on WordPress.org.
 *
 * @copyright (C) Copyright Bobbing Wide 2015-2017, 2019
 * @package wp-top12
 */


/**
 * Class WP_org_downloads obtains information about all the plugins on WordPress.org
 * in order to make some sense of it for top-10-wp-plugins.com
 *
 * The information gets summarised into wporg_plugins.csv for offline post-processing.
 * It should only be necessary to download the full list once a month.
 *
 *
 */
class WP_org_downloads {

	public $response;

	public $plugins;

	public $csv;

	public $downloaded;

	public $preselection;

	public $content; /* Output for the top-10-wp-plugins blog post */

	/**
	 * File name of saved plugins excluding the file extension, which is a number
	 */
	public $wporg_saved_plugins;

	/**
	 * File name of saved plugins v2
	 *
	 */
	public $wporg_saved_plugins_v2;
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
		$this->wporg_saved_plugins();
		$this->wporg_saved_plugins_v2();
	}

	/**
	 * Get information for a specific plugin
	 *
	 * A long time ago the logic in this method used the WordPress API v1.0
	 * It now uses the REST API to obtain similar information.
	 * The data returned is a subset of what was available before.
	 *
	 * Store the results in $this->response
	 *
	 * Using https://api.wordpress.org/plugins/info/1.0/oik.info
	 * gets the information as serialized data which we convert into an object
	 *
	 * Using https://api.wordpress.org/plugins/info/1.0/oik.json
	 * gets the information as JSON
	 *
	 * Then it stopped working. I got told that the above information was incorrect.
	 *
	 * Essentially, you're using an endpoint which is not officially supported (and one which I've never actually seen before).
	 * If you eliminate that weird .info thing, then you'll get the 1.0 endpoint proper, which gives serialized PHP data.
	 * If you want json, then the correct endpoint would be more like this:
	 * https://api.wordpress.org/plugins/info/1.1/?action=plugin_information&request[slug]=oik
	 *
	 * The .info is likely some kind of side-effect that was never intended to be used as any form of official endpoint.
	 *
	 * Here's the link to the API: https://codex.wordpress.org/WordPress.org_API
	 *
	 * there's a 1.2 request as well
	 *
	 * https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request
	 *
	 * If the plugin does not exist the response is an empty array []
	 *
	 *
	 *
	 * @param string $plugin_slug
	 *
	 */
	function get_download( $plugin_slug ) {
		$url  = 'https://wordpress.org/plugins/wp-json/wp/v2/plugin/';
		$url .= '?slug=';
		$url .= $plugin_slug;
		$response = oik_remote::bw_remote_get( $url );
		//print_r( $this->response );
		if ( $response ) {
			$this->response = $response[0];
		} else {
			//echo "Nothing returned";
			return false;
		}
		return true;
	}

	/**
	 * Get the download count for the selected plugin
	 *
	 * @return integer the total downloaded
	 */
	function get_download_count() {
		$count = $this->response->meta->downloads;
		return $count;
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

	please don’t hit it too hard otherwise we’ll have to block access entirely.
	https://wordpress.org/plugins/wp-json/wp/v2/plugin/
	https://wordpress.org/plugins/wp-json/wp/v2/plugin/110913

	https://wordpress.org/plugins/wp-json/wp/v2/plugin/?page=2
	 */
	function query_all_plugins_v2( $page=1, $per_page=100) {
		$this->query_plugins_v2( $page, $per_page );

		//if ( 1 === $page) {
			$headers    = wp_remote_retrieve_headers( $this->response[0] );
			$headers    = new Requests_Response_Headers( $headers->getAll() );
			$x_wp_total = $headers->getValues( 'x-wp-total' );
			echo "Total plugins: ";
			echo $x_wp_total[0];
			echo PHP_EOL;
			$x_wp_totalpages = $headers->getValues( 'x-wp-totalpages' );
			$total_pages     = $x_wp_totalpages[0];
			echo "Total pages: ";
			echo $total_pages;
			echo PHP_EOL;
		//}

		$this->save_plugins_v2( $page );

		for ( $page = 2; $page <= $total_pages; $page++  ) {
			$this->query_plugins_v2( $page, $per_page );
			$this->save_plugins_v2( $page );
		}
	}

	function query_plugins_v2( $page, $per_page ) {
		$url      = 'https://wordpress.org/plugins/wp-json/wp/v2/plugin/?page=';
		$url      .= $page;
		$url      .= '&per_page=';
		$url      .= $per_page;
		$this->response = oik_remote::bw_remote_geth( $url );
		//print_r( $response );
		$this->plugins = $this->response[1];

	}

	function save_plugins_v2( $page ) {
		$string = json_encode( $this->plugins );
		//print_r( $this->plugins);
		$file = $this->wporg_saved_plugins_v2 . $page . '.json';
		$saved = file_put_contents( $file, $string );
		$this->reset();
		bw_trace2();
	}

	function wporg_saved_plugins( $wporg_saved_plugins="cache/wporg_saved.plugins." ) {
		$this->wporg_saved_plugins = $wporg_saved_plugins;
	}
	function wporg_saved_plugins_v2( $wporg_saved_plugins="cache_v2/wporg_saved.plugins." ) {
		$this->wporg_saved_plugins_v2 = $wporg_saved_plugins;
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
		$file = $this->wporg_saved_plugins_v2 . $page . '.json';
		//echo "Loading file: $file " . PHP_EOL;
		$loaded = file_exists( $file );
		if ( $loaded ) {
			$plugins_string = file_get_contents( $file );
			if ( false === $plugins_string ) {
				$loaded = false;
			} else {
				$plugins = json_decode( $plugins_string );
				$loaded = count( $plugins );
				//echo "Count: " . $loaded . PHP_EOL;
				$this->add_plugins( $plugins );
				//$this->plugins = $plugins;
			}
		}
		return( $loaded );
	}

	/**
	 * Load all the plugins from the serialized results
	 *
	 * @TODO Will do 55,500 plugins.
	 * Need 545 pages for 54,500 - currently 54,498
	 *
	 * - 26 Dec 2019 - 50062
	 * - 30 Jan 2020 - 55481
	 *
	 * @TODO Load the plugins from the wporg_plugins.csv file if it's more recent than the downloads!
	 *
	 */
	function load_all_plugins() {
		$start = 1;
		$max_pages = 1000;
		for ( $page = $start ; $page <= $max_pages; $page++ ) {
			//echo "Loading page: $page " . PHP_EOL;
			$loaded = $this->load_plugins( $page );
			if ( !$loaded) {
				break;
			}
		}
	}

	/**
	 * Add the latest set to the total list
	 */
	function add_plugins( $plugins ) {
		//print_r( $this->plugins );

		//echo count( $plugins );
		//echo ' ';
		//echo count( $this->plugins );
		foreach ( $plugins as $key => $plugin ) {
			$plugin->name = $plugin->meta->header_name;
			$plugin->downloads = $plugin->meta->downloads;
			$plugin->tested = $plugin->meta->tested;
			$plugin->requires = $plugin->meta->requires;
			$plugin->last_updated = $plugin->modified_gmt;

			$this->plugins[] = $plugin;
		}

		//$this->plugins += $plugins;
		//$count = count( $this->plugins );
		//echo "Loaded: " . $count . PHP_EOL;
		//gob();
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
		$file = $this->wporg_saved_plugins . $page;
		$saved = file_put_contents( $file, $string );
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
	 * OK. There was a problem with 100, we were'nt getting 100 returned
	 * But it we ask for fewer than 60 something and we're limited to 999 pages
	 * then we don't get all of them.
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
		$args = array( "per_page" => 60
		, "page" => $page
		, "fields" => serialize( $fields )
		);
		echo "Requesting: " . $page . PHP_EOL;
		$this->response = plugins_api( "query_plugins", $args );
		print_r( $this->response );
		gob();
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

	function get_plugin( $plugin ) {
		$plugin = bw_array_get( $this->plugins, $plugin, null );
		print_r( $plugin );
		gob();
		return $plugin;
	}

	/**
	 *
	 * stdClass Object
	(
	[id] => 111839
	[date] => 2019-11-05T15:48:06
	[date_gmt] => 2019-11-05T15:48:06
	[guid] => stdClass Object
	(
	[rendered] => https://wordpress.org/plugins/vestorly-contact-form-7-integration/
	)

	[modified] => 2019-11-05T15:48:06
	[modified_gmt] => 2019-11-05T15:48:06
	[slug] => vestorly-contact-form-7-integration
	[status] => publish
	[type] => plugin
	[link] => https://wordpress.org/plugins/vestorly-contact-form-7-integration/
	[author] => 17634286
	[comment_status] => closed
	[ping_status] => closed
	[template] =>
	[meta] => stdClass Object
	(
	[rating] => 0
	[active_installs] => 0
	[downloads] => 0
	[tested] => 5.2.4
	[requires] => 4.9
	[requires_php] => 5.6.20
	[stable_tag] => trunk
	[donate_link] =>
	[version] => 0.1.0
	[header_name] => Vestorly Contact Form 7 Integration
	[header_plugin_uri] =>
	[header_author] => Vestorly
	[header_author_uri] => https://www.vestorly.com
	[header_description] => A plugin to integrate Vestorly with Contact Form 7
	[assets_banners_color] =>
	[support_threads] => 0
	[support_threads_resolved] => 0
	[spay_email] =>
	)

	[banners] =>
	[icons] => stdClass Object
	(
	[svg] =>
	[icon] => https://s.w.org/plugins/geopattern-icon/vestorly-contact-form-7-integration.svg
	[icon_2x] =>
	[generated] => 1
	)

	[rating] => 0
	[ratings] => Array
	(
	)

	[screenshots] => Array
	(
	)

	[_links] => stdClass Object
	(
	[self] => Array
	(
	[0] => stdClass Object
	(
	[href] => https://wordpress.org/plugins/wp-json/wp/v2/plugin/111839
	)

	)

	[collection] => Array
	(
	[0] => stdClass Object
	(
	[href] => https://wordpress.org/plugins/wp-json/wp/v2/plugin
	)

	)

	[about] => Array
	(
	[0] => stdClass Object
	(
	[href] => https://wordpress.org/plugins/wp-json/wp/v2/types/plugin
	)

	)

	[author] => Array
	(
	[0] => stdClass Object
	(
	[embeddable] => 1
	[href] => https://wordpress.org/plugins/wp-json/wp/v2/users/17634286
	)

	)

	[replies] => Array
	(
	[0] => stdClass Object
	(
	[embeddable] => 1
	[href] => https://wordpress.org/plugins/wp-json/wp/v2/comments?post=111839
	)

	)

	[wp:attachment] => Array
	(
	[0] => stdClass Object
	(
	[href] => https://wordpress.org/plugins/wp-json/wp/v2/media?parent=111839
	)

	)

	[curies] => Array
	(
	[0] => stdClass Object
	(
	[name] => wp
	[href] => https://api.w.org/{rel}
	[templated] => 1
	)

	)

	)

	)
	 *
	 * $this->csv = "Slug,Name,Rating,Downloads,Installed,Tested,Requires,LastUpdate" . PHP_EOL;
	 */
	function display( $plugin ) {
		//print_r( $plugin );
		$slug = $plugin->slug;
		$name = str_replace( ",", "",  $plugin->meta->header_name );

		$rating = $plugin->rating;
		$downloaded = $plugin->meta->downloads;
		$installed = $plugin->meta->active_installs;

		if ( isset( $plugin->meta->tested ) ) {
			$tested = $plugin->meta->tested;
		} else {
			$tested = " (null)";
			$plugin->meta->tested = null;
		}
		if ( isset( $plugin->meta->requires ) ) {
			$requires = $plugin->meta->requires;
		} else {
			$requires = " (null)";
			$plugin->meta->requires = null;
		}

		$last_update = $plugin->modified_gmt;

		// Don't include description as it's not up to date and contains HTML
		//$description = $plugin->meta->header_description;

		$this->csv .= "$slug,$name,$rating,$downloaded,$installed,$tested,$requires,$last_update" . PHP_EOL;
		return $downloaded ;
	}

	/**
	 * Creates wporg_plugins.csv
	 *
	 * Assumes that $this->plugins contains the full list of plugins, ordered by downloads descending.
	 */
	function summarise( $file="wporg_plugins.csv" ) {
		$this->csv = "Slug,Name,Rating,Downloaded,Installed,Tested,Requires,LastUpdate" . PHP_EOL;

		foreach ( $this->plugins as $slug => $plugin ) {

			$plugin = ( object ) $plugin;
			//print_r( $plugin );
			//gob();
			$downloaded = $this->display( $plugin );
			$this->downloaded( $downloaded );
		}
		file_put_contents( $file, $this->csv );
		echo "Total downloaded: " . $this->downloaded . PHP_EOL;
		$this->block_writer( 'heading', null, '<h2>Total downloads: ' . number_format_i18n( $this->downloaded ) . '</h2>' );

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
		$grouper->groupby( "downloads", array( $this, "tentothe" ) );
		$grouper->ksort();
		//$grouper->report_groups();
		$this->block_writer( 'heading', null, '<h2>Grouped by total downloads</h2>');
		$this->report_groups( $grouper );

		$grouper->subset( array( $this, "year" ) );
		$grouper->groupby( "last_updated" );
		$grouper->krsort();
		$grouper->report_groups();
		$this->block_writer( 'heading', null, '<h2>Last updated</h2>');
		$this->report_groups( $grouper );

		$grouper->groupby( "requires", array( $this, "versionify" ) );
		$grouper->ksort();
		$grouper->report_groups();

		$merger = new CSV_merger();
		$merger->append( $grouper->groups );

		$grouper->groupby( "tested", array( $this, "versionify" ) );
		$grouper->ksort();
		$grouper->report_groups();


		$merger->append( $grouper->groups);

		$this->block_writer( 'heading', null, '<h2>WordPress version compatibility</h2>');
		//echo "Merged report:" . PHP_EOL;
		$this->report_groups( $merger );


		$grouper->groupby( "rating", array( $this, "stars" ) );
		$grouper->krsort();
		$grouper->report_groups();
		$this->block_writer( 'heading', null, '<h2>Star ratings</h2>');
		$this->report_groups( $grouper );


		$grouper->groupby( "name", array( $this, "firstletter" ) );
		$grouper->ksort();
		$grouper->report_groups();

		//$grouper->groupby( "slug", array( $this, "firstletter" ) );
		//$grouper->ksort();
		//$grouper->report_groups();
		$grouper->groupby( "name", array( $this, "words" ) );
		$grouper->arsort();
		$grouper->report_groups();

		$this->echo_content();


	}

	function list_block_plugins() {
		$block_plugins = array();
		foreach ( $this->plugins as $key => $plugin ) {
			echo $key . PHP_EOL;
			print_r( $plugin );
			//if ( $plugin->keyword)
			gob();

		}

		$grouper = new Object_Grouper();
		echo "Grouping: " . count( $this->plugins ) . PHP_EOL;
		$grouper->populate( $this->plugins );
		$this->preselect( "block,blocks");
		$grouper->groupby( "keyword", array( $this, "preselected_keyword" ) );
		$grouper->report_groups();
	}

	function preselected_keyword( $value ) {
		print_r( $value );
		gob();
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
			} elseif ( $ver3 <= 4.9 ) {
				$ver = "4.0 to 4.9";
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

		$rating = $rating;
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

	function sort_by_most_downloaded( $limit=1000) {
		$sorter = new Object_Sorter();
		if ( null == $limit) {
			$limit = count( $this->plugins );

		}
		echo "Sorting: " . count( $this->plugins ) . PHP_EOL;
		$sorted = $sorter->sortby( $this->plugins, "downloads", "desc" );

		$top1000 = $sorter->results( $limit );
		return $top1000;

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
	function top1000( $limit=null ) {
		$top1000 = $this->sort_by_most_downloaded( $limit );
		//echo $this->csv;
		//$this->report_top1000( $top1000 );

		// List every single plugin sorted by plugin slug
		// $top1000 = $sorter->resort( "slug", "asc" );
		// $this->report_top1000( $top1000 );

		$this->plugins = $top1000;

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
	function report_top1000( $limit=1000 ) {
		echo 'Displaying: ' . $limit . ' of ' . count( $this->plugins ) . PHP_EOL;
		$limit = min( $limit, count( $this->plugins ) );
		$top12 = "Position|Plugin|Total downloads\n";
		$top12 = null;
		for ( $index = 0; $index < $limit ; $index++ ) {
			$plugin = $this->plugins[ $index ];
			$plugin = (object) $plugin;
			$line   = $index + 1;
			$line  .= '|';
			$line  .= '<a href=https://wordpress.org/plugins/';
			$line  .= $plugin->slug;
			$line  .= '>';
			$line  .= $plugin->slug;
			$line  .= '</a>';
			$line  .= '|';
			$line  .= number_format_i18n( $plugin->meta->downloads );
			$line  .= "\n";
			echo $line;
			if ( $index < 12 ) {
				$top12 = $line . $top12;
			}
		}
		$top12 = "Position|Plugin|Total downloads\n" . $top12;
		$atts = [ 'content' => $top12 ];
		$this->block_writer( 'oik-block/csv', $atts, null );
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

	function block_writer( $block_type_name, $atts = null, $content = null ) {
		$attributes = \oik\oik_blocks\oik_blocks_atts_encode( $atts );
		$this->content .= \oik\oik_blocks\oik_blocks_generate_block( $block_type_name, $attributes, $content );
		//echo $this->content;
	}

	function report_groups( $grouper ) {
		ob_start();
		$grouper->report_groups();
		$output = ob_get_clean();
		$atts = [ 'content' => $output ];
		$this->block_writer( 'oik-block/csv', $atts, null );
	}

	function echo_content() {
		echo $this->content;
	}


}

