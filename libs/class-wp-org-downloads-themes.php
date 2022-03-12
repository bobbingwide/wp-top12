<?php

/**
 * Information about all themes on WordPress.org.
 *
 * @copyright (C) Copyright Bobbing Wide 2021
 * @package wp-top12
 */


/**
 * Class WP_org_downloads_themes obtains information about all the themes on WordPress.org
 * in order to make some sense of it for top-10-wp-themes.com
 *
 * The information gets summarised into wporg_themes.csv for offline post-processing.
 * It should only be necessary to download the full list once a month.
 *
 *
 */
class WP_org_downloads_themes {

	public $response;

	/**
	 * Array of theme objects?
	 * @var
	 */
	public $themes;

	public $fse_themes;

	public $csv;

	public $downloaded;

	public $preselection;

	public $content; /* Output for the top-10-wp-themes blog post */

	/**
	 * File name of saved themes excluding the file extension, which is a number
	 */
	public $wporg_saved_themes;

	/**
	 * File name of saved themes v2
	 *
	 */
	public $wporg_saved_themes_v2;

	private $theme_info;

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
		$this->wporg_saved_themes();
		$this->wporg_saved_themes_v2();
	}

	/**
	 * Get information for a specific theme
	 *
	 * A long time ago the logic in this method used the WordPress API v1.0
	 * It now uses the REST API to obtain similar information.
	 * The data returned is a subset of what was available before.
	 *
	 * Store the results in $this->response
	 *
	 * Using https://api.wordpress.org/themes/info/1.0/oik.info
	 * gets the information as serialized data which we convert into an object
	 *
	 * Using https://api.wordpress.org/themes/info/1.0/oik.json
	 * gets the information as JSON
	 *
	 * Then it stopped working. I got told that the above information was incorrect.
	 *
	 * Essentially, you're using an endpoint which is not officially supported (and one which I've never actually seen before).
	 * If you eliminate that weird .info thing, then you'll get the 1.0 endpoint proper, which gives serialized PHP data.
	 * If you want json, then the correct endpoint would be more like this:
	 * https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]=oik
	 *
	 * The .info is likely some kind of side-effect that was never intended to be used as any form of official endpoint.
	 *
	 * Here's the link to the API: https://codex.wordpress.org/WordPress.org_API
	 *
	 * there's a 1.2 request as well
	 *
	 * https://api.wordpress.org/themes/info/1.2/?action=query_themes&request
	 *
	 * If the theme does not exist the response is an empty array []
	 *
	 *
	 *
	 * @param string $theme_slug
	 *
	 */
	function get_download( $theme_slug ) {
		oik_require( 'includes/themes_api_v10.php', 'wp-top12');
		/*
		$url  = 'https://wordpress.org/themes/wp-json/wp/v2/theme/';
		$url .= '?slug=';
		$url .= $theme_slug;
		*/
		//$response = oik_remote::bw_remote_get( $url );
		$args = ['slug' => $theme_slug ];
		$response = themes_api_v10( "theme_information", $args );
		//print_r( $this->response );
		return $response;
	}

	/**
	 * Get the download count for the selected theme
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
	 * perhaps we use get_theme_stats() ?
	 */
	function get_installs() {
	}

	/**
	 * query information about a
	 *
	 * We work our way backwards through the list
	 * having first requested page 1 to find out how many pages there should be
	 * If the number has changed from last time then what do we know?
	 *
	 *
	 */
	function query_all_themes() {

		$page = 1;
		$this->query_themes( $page );
		$this->get_themes_info();
		$this->save_themes_v2( $page );

		$pages = $this->response->info['pages'];

		$page = $pages;

		while ( $page > 1 ) {
			echo "Processing page: $page" . PHP_EOL;
			$this->query_themes( $page );
			$this->get_themes_info();
			$this->save_themes_v2( $page );
			$page--;
		}
	}

	function query_bundled_themes() {

		$themes = [ 'twentyten', 'twentyeleven', 'twentytwelve', 'twentythirteen', 'twentyfourteen', 'twentyfifteen',
			'twentysixteen', 'twentyseventeen', /* 'twentyeighteen', */ 'twentynineteen', 'twentytwenty', 'twentytwentyone' /* , 'twentytwentytwo' */ ];
		foreach ( $themes as $key => $theme ) {
			$this->theme_info = $this->get_download( $theme );
			//print_r( $this->theme_info );
			//gob();
			$this->save_theme_info( $theme, $this->theme_info);
		}

	}

	function get_themes_info() {
		foreach ( $this->response->themes as $key => $theme ) {
			$this->theme_info = $this->get_download( $theme->slug );
			//print_r( $this->theme_info );
			//gob();
			$this->save_theme_info( $theme->slug, $this->theme_info);
		}
	}

	/**

	please don’t hit it too hard otherwise we’ll have to block access entirely.
	https://wordpress.org/themes/wp-json/wp/v2/theme/
	https://wordpress.org/themes/wp-json/wp/v2/theme/110913

	https://wordpress.org/themes/wp-json/wp/v2/theme/?page=2
	 */
	function query_all_themes_v2( $page=1, $per_page=100) {
		$this->query_themes_v2( $page, $per_page );

		//if ( 1 === $page) {
		$headers    = wp_remote_retrieve_headers( $this->response[0] );
		$headers    = new Requests_Response_Headers( $headers->getAll() );
		$x_wp_total = $headers->getValues( 'x-wp-total' );
		echo "Total themes: ";
		echo $x_wp_total[0];
		echo PHP_EOL;
		$x_wp_totalpages = $headers->getValues( 'x-wp-totalpages' );
		$total_pages     = $x_wp_totalpages[0];
		echo "Total pages: ";
		echo $total_pages;
		echo PHP_EOL;
		//}

		$this->save_themes_v2( $page );

		for ( $page = 2; $page <= $total_pages; $page++  ) {
			$this->query_themes_v2( $page, $per_page );
			$this->save_themes_v2( $page );
		}
	}

	function query_themes_v2( $page, $per_page ) {
		$url      = 'https://wordpress.org/themes/wp-json/wp/v2/theme/?page=';
		$url      .= $page;
		$url      .= '&per_page=';
		$url      .= $per_page;
		$this->response = oik_remote::bw_remote_geth( $url );
		//print_r( $response );
		$this->themes = $this->response[1];

	}

	function save_themes_v2( $page ) {
		$string = json_encode( $this->response->themes );
		//print_r( $this->themes);
		$file = $this->wporg_saved_themes_v2 . $page . '.json';
		$saved = file_put_contents( $file, $string );
		$this->reset();
		bw_trace2();
	}

	function save_theme_info( $slug, $info ) {
		$string = json_encode( $info );
		//print_r( $this->themes);
		$file = $this->wporg_saved_themes_v2 . $slug . '.json';
		$path = oik_path( $file, 'wp-top12');
		$saved = file_put_contents( $path, $string );

	}

	function wporg_saved_themes( $wporg_saved_themes="cache/wporg_saved.themes." ) {
		$this->wporg_saved_themes = $wporg_saved_themes;
	}
	function wporg_saved_themes_v2( $wporg_saved_themes="cache_t2/wporg_saved.themes." ) {
		$this->wporg_saved_themes_v2 = $wporg_saved_themes;
	}
	/**
	 * Load the information from a local cache
	 *
	 */
	function load() {
		gob();
		$themes = file_get_contents( "wporg_saved.themes" );
		$saved = file( "wporg_saved.themes" );

		echo "Count: " . count( $themes ) . PHP_EOL;
		echo "Count saved:" . count( $saved ) . PHP_EOL;
		//$this->themes = file( "wporg_load.themes" );
	}

	/**
	 *
	 */
	function load_themes( $file ) {
		$parts = explode( '.', $file);
		//print_r( $parts );
		if ( is_numeric( $parts[ 2] )) {
			return;
		}
		echo "Loading file: $file " . PHP_EOL;
		$loaded = file_exists( $file );
		if ( $loaded ) {
			$themes_string = file_get_contents( $file );
			//print_r( $themes_string );
			if ( false === $themes_string ) {
				$loaded = false;
			} else {
				$theme = json_decode( $themes_string );
				if ( null === $theme ) {
					echo "Eh?" . strlen( $themes_string);
				}
				//print_r( $themes );
				//$loaded = count( $themes );
				//echo "Count: " . $loaded . PHP_EOL;
				$this->add_theme( $theme );
				//$this->themes = $themes;
			}
		}
		return( $loaded );
	}

	/**
	 * Loads all the themes we're interested in.
	 *
	 * Unlike plugins we can't get the total downloads on the query API call.
	 * So we have to be more selective.
	 * Here I'm limiting the files to:
	 * - FSE themes
	 * - WordPress default / bundled themes ie TwentySomething themes.
	 */
	function load_all_themes() {
		$path = oik_path( $this->wporg_saved_themes_v2 . '*', 'wp-top12');
		$files = glob( $path );
		//echo "Loaded files for : " . $this->wporg_saved_themes_v2;
		//print_r( $files );

		foreach ( $files as $file ) {
			$loaded = $this->load_themes( $file );
		}
	}

	/**
	 * Adds the current theme to the total list.
	 */
	function add_theme( $theme ) {
		//print_r( $theme );
		if ( $theme && property_exists( $theme, 'slug') ){
			$this->themes[ $theme->slug ]=$theme;
		} else {
			print_r( $theme);

		}
	}


	/**
	 * Save the information to a local cache
	 * - Note different file name while developing
	 */
	function save() {
		gob();
		foreach ( $this->themes as $theme => $data ) {
			echo "Saving: $theme" ;
			$string = serialize( $data );
			//print_r( $string );
			echo "Length:" .  strlen( $string );
			$saved = file_put_contents( "wporg_saved.themes", $string, FILE_APPEND );
			echo "Written: $saved" ;
			echo PHP_EOL;

		}
	}

	/**
	 * Save the themes array
	 *
	 */
	function save_themes( $page ) {
		bw_trace2();
		$string = serialize( $this->themes );
		$file = $this->wporg_saved_themes . $page;
		$saved = file_put_contents( $file, $string );
		$this->reset();
		bw_trace2();
	}

	function reset() {
		unset( $this->themes );
		unset( $this->fse_themes );
		$this->themes = array();
		$this->fse_themes = array();
	}

	/**
	 * Query themes on WordPress.org
	 *
	 *
	 * The result is expected to be a stdClass Object of two arrays: info and themes
	 *
	 * - 'info' contains 'page', 'pages' and 'results'
	 * - 'themes' contains an array of theme Objects
	 *
	 * `stdClass Object
	 * (
	 *	 [info] => Array
	 *			 (
	 * 				 [page] => 1
	 *				 [pages] => 1
	 *				 [results] => 31
	 *			 )
	 *
	 *  [themes] => Array
	 *
	 * `
	 *
	 * The themes are returned on most recently updated order
	 *
	 */
	function query_themes( $page=1 ) {
		$fields = array( 'description' => false
		, 'sections' => false
		, 'tested' => true
		, 'requires' => true
		, 'rating' => true
		, 'downloaded' => true
		, 'downloadlink' => false
		, 'last_updated' => true
		, 'homepage' => true
		, 'tags' => true
		);

		// Using browse=updated will return all 9061 themes
		// but there's no downloaded figures regardless of the API version we try to use: 1.0, 1.1 or 1.2 ( latest since WordPress 5.0 )
		// So the best we can do is to concentrate on FSE themes... of which there are currently 31. ( 13 Dec 2021 ).
		$args = array( "per_page" => 60
		, "page" => $page
		//, "fields" => serialize( $fields )
		//, "browse" => "updated"
		, "tag" => "full-site-editing"
		);
		echo "Requesting: " . $page . PHP_EOL;
		//require_once ABSPATH . "wp-admin/includes/theme.php";
		oik_require( 'includes/themes_api_v10.php', 'wp-top12');
		$this->response = themes_api_v10( "query_themes", $args );
		//print_r( $this->response );
		//gob();
		//$this->store_themes();

	}



	/**
	 * Store the results
	 *
	 * @TODO Handle the result which could be a WP_Error object
	 *
	 */
	function store_themes() {
		$this->report_info();
		$themes = $this->response->themes;
		foreach ( $themes as $key => $theme ) {
			$this->store( $theme );
		}
	}

	/**
	 * Store a single result
	 *
	 * $param theme this is now an array. We used to treat is as an object!
	 */
	function store( array $theme ) {
		//print_r( $theme );

		static $count = 0;
		$slug = $theme['slug'];
		//gob();
		$this->themes[ $slug ] = $theme;
		$count++;
		//echo "Storing: $count $slug" . PHP_EOL;
	}

	/**
	 * Report on the total themes info
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
	 * Returns the cached theme info for the chosen theme.
	 *
	 * @param string $theme The theme's slug eg twentytwentytwo
	 * @return mixed|null
	 */
	function get_theme( $theme ) {
		//print_r( $this->themes );
		$theme_info = bw_array_get( $this->themes, $theme, null );
		//print_r( $theme_info );
		return $theme_info;
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
	[rendered] => https://wordpress.org/themes/vestorly-contact-form-7-integration/
	)

	[modified] => 2019-11-05T15:48:06
	[modified_gmt] => 2019-11-05T15:48:06
	[slug] => vestorly-contact-form-7-integration
	[status] => publish
	[type] => theme
	[link] => https://wordpress.org/themes/vestorly-contact-form-7-integration/
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
	[header_theme_uri] =>
	[header_author] => Vestorly
	[header_author_uri] => https://www.vestorly.com
	[header_description] => A theme to integrate Vestorly with Contact Form 7
	[assets_banners_color] =>
	[support_threads] => 0
	[support_threads_resolved] => 0
	[spay_email] =>
	)

	[banners] =>
	[icons] => stdClass Object
	(
	[svg] =>
	[icon] => https://s.w.org/themes/geopattern-icon/vestorly-contact-form-7-integration.svg
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
	[href] => https://wordpress.org/themes/wp-json/wp/v2/theme/111839
	)

	)

	[collection] => Array
	(
	[0] => stdClass Object
	(
	[href] => https://wordpress.org/themes/wp-json/wp/v2/theme
	)

	)

	[about] => Array
	(
	[0] => stdClass Object
	(
	[href] => https://wordpress.org/themes/wp-json/wp/v2/types/theme
	)

	)

	[author] => Array
	(
	[0] => stdClass Object
	(
	[embeddable] => 1
	[href] => https://wordpress.org/themes/wp-json/wp/v2/users/17634286
	)

	)

	[replies] => Array
	(
	[0] => stdClass Object
	(
	[embeddable] => 1
	[href] => https://wordpress.org/themes/wp-json/wp/v2/comments?post=111839
	)

	)

	[wp:attachment] => Array
	(
	[0] => stdClass Object
	(
	[href] => https://wordpress.org/themes/wp-json/wp/v2/media?parent=111839
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
	function display( $theme ) {
		//print_r( $theme );
		$slug = $theme->slug;
		$name = str_replace( ",", "",  $theme->meta->header_name );

		$rating = $theme->rating;
		$downloaded = $theme->meta->downloads;
		$installed = $theme->meta->active_installs;

		if ( isset( $theme->meta->tested ) ) {
			$tested = $theme->meta->tested;
		} else {
			$tested = " (null)";
			$theme->meta->tested = null;
		}
		if ( isset( $theme->meta->requires ) ) {
			$requires = $theme->meta->requires;
		} else {
			$requires = " (null)";
			$theme->meta->requires = null;
		}

		$last_update = $theme->modified_gmt;

		// Don't include description as it's not up to date and contains HTML
		//$description = $theme->meta->header_description;

		$this->csv .= "$slug,$name,$rating,$downloaded,$installed,$tested,$requires,$last_update" . PHP_EOL;
		return $downloaded ;
	}

	/**
	 * Creates wporg_themes.csv
	 *
	 * Assumes that $this->themes contains the full list of themes, ordered by downloads descending.
	 */
	function summarise( $file="wporg_themes.csv" ) {
		$this->csv = "Slug,Name,Rating,Downloaded,Installed,Tested,Requires,LastUpdate" . PHP_EOL;

		foreach ( $this->themes as $slug => $theme ) {

			$theme = ( object ) $theme;
			//print_r( $theme );
			//gob();
			$downloaded = $this->display( $theme );
			$this->downloaded( $downloaded );
		}
		file_put_contents( $file, $this->csv );
		echo "Total downloaded: " . $this->downloaded . PHP_EOL;
		$this->block_writer( 'heading', null, '<h2>Total downloads: ' . number_format_i18n( $this->downloaded ) . '</h2>' );
		$this->block_writer( 'paragraph', null, '<p>themes: ' . count( $this->themes ) . '</p>' );
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

		$this->fse_themes = $this->get_fse_themes();

		echo "Grouping: " . count( $this->fse_themes ) . PHP_EOL;
		$grouper->populate( $this->fse_themes );

		$this->block_writer( 'heading', null, '<h2>Total downloads: ' . number_format_i18n( $this->downloaded ) . '</h2>' );
		$this->block_writer( 'paragraph', null, '<p>FSE themes: ' . count( $this->fse_themes ) . '</p>' );

		//$this->report_grouped_by_total_downloads();
		$this->top1000( null );
		$this->report_top1000( 100 );

		$grouper->reset();
		$grouper->groupby( "downloaded", array( $this, "tentothe" ) );
		$grouper->ksort();
		//$grouper->report_groups();

		$this->block_writer( 'heading', null, '<h2>Grouped by total downloads</h2>');

		$this->chart_groups( $grouper, 'bar', 'Downloads,Count');
		//$this->report_groups( $grouper );

		//$this->echo_content();

		$grouper->subset( array( $this, "month" ) );

		$grouper->reset();
		$grouper->groupby( "creation_time" );
		$grouper->ksort();
		$grouper->report_groups();

		$merger = new CSV_merger();
		$merger->append( $grouper->groups );
		$merger->accum();
		//$merger->report_accum();

		//$accum = $merger->accum;
		//$grouper->reset();
		//$grouper->populate( $accum );

		$this->block_writer( 'heading', null, '<h2>FSE theme count</h2>');
		$this->chart_groups( $merger, 'bar', 'Month,Total', 'Visualizer', true );

		$grouper->reset();
		$grouper->groupby( "last_updated" );
		$grouper->ksort();
		$grouper->report_groups();
		$merger->append( $grouper->groups);


		$this->block_writer( 'heading', null, '<h2>Last updated / created</h2>');
		$this->chart_groups( $merger, 'bar', 'Month,Created,Updated', 'Visualizer' );

		$this->echo_content();

		echo "Ending here" . PHP_EOL;
		return;
		$grouper->reset();
		$grouper->groupby( "requires", array( $this, "versionify" ) );
		$grouper->ksort();
		$grouper->report_groups();

		$merger = new CSV_merger();
		$merger->append( $grouper->groups );
		$grouper->reset();
		$grouper->groupby( "tested", array( $this, "versionify" ) );
		$grouper->ksort();
		$grouper->report_groups();


		$merger->append( $grouper->groups);

		$this->block_writer( 'heading', null, '<h2>WordPress version compatibility</h2>');
		//echo "Merged report:" . PHP_EOL;
		$this->chart_groups( $merger, 'bar', 'Version,Requires,Tested');
		//	$this->report_groups( $merger );


		$grouper->reset();
		$grouper->groupby( "rating", array( $this, "stars" ) );
		$grouper->krsort();
		$grouper->report_groups();
		$this->block_writer( 'heading', null, '<h2>Star ratings</h2>');
		//	$this->report_groups( $grouper );
		$this->chart_groups( $grouper, 'pie', 'Stars,# themes' );

		$grouper->reset();
		$grouper->groupby( "name", array( $this, "firstletter" ) );
		$grouper->ksort();
		$grouper->report_groups();

		//$grouper->groupby( "slug", array( $this, "firstletter" ) );
		//$grouper->ksort();
		//$grouper->report_groups();
		$grouper->reset();
		$grouper->groupby( "name", array( $this, "words" ) );
		$grouper->arsort();
		$grouper->report_groups();

		$this->echo_content();


	}

	/**
	 * Filters FSE themes.
	 *
	 * @return array
	 */
	function get_fse_themes() {
		$fse_themes = array();
		foreach ( $this->themes as $key => $theme ) {
			if ( property_exists( $theme->tags, 'full-site-editing')) {
				$fse_themes[ $key ] = $theme;
				$this->downloaded( $theme->downloaded );
			}
		}
		return $fse_themes;
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
			} elseif ( $ver3 > 5.9 )	{
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

	function month( $date ) {
		$year = substr( $date, 0, 4 );
		if ( $year < 2021) {
			return( "2020-12");
		}
		return( substr( $date, 0, 7 ) );
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
			$limit = count( $this->fse_themes );

		}
		echo "Sorting: " . count( $this->fse_themes ) . PHP_EOL;
		$sorted = $sorter->sortby( $this->fse_themes, "downloaded", "desc" );

		$top1000 = $sorter->results( $limit );
		return $top1000;

	}

	/**
	 * Produce a table of the top 1000 (or so )
	 *
	 * Here we need to sort the $themes array using our own sort method
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

		// List every single theme sorted by theme slug
		// $top1000 = $sorter->resort( "slug", "asc" );
		// $this->report_top1000( $top1000 );

		$this->fse_themes = $top1000;

	}

	/**
	 * Produce a simple report of the selected items.
	 *
	 * @TODO Allow selection of the fields to be shown using
	 * a fields string/array
	 *
	 * And automatically create the column headings based on the given field names
	 *
	 */
	function report_top1000( $limit=1000, $topn=50 ) {
		echo 'Displaying: ' . $limit . ' of ' . count( $this->fse_themes ) . PHP_EOL;
		$limit = min( $limit, count( $this->fse_themes ) );
		$top12 = "Position|Theme|Total downloads|Created\n";
		$top12 = null;
		$top12chart = null;

		for ( $index = 0; $index < $limit ; $index++ ) {
			$theme = $this->fse_themes[ $index ];
			$theme = (object) $theme;
			$line   = $index + 1;
			$line  .= '|';
			$line  .= '<a href=https://wordpress.org/themes/';
			$line  .= $theme->slug;
			$line  .= '>';
			$line  .= $theme->slug;
			$line  .= '</a>';
			$line  .= '|';
			$line  .= number_format_i18n( $theme->downloaded );
			$line  .= '|';
			$line  .= substr( $theme->creation_time, 0, 7 );
			$line  .= "\n";
			echo $line;
			if ( $index < $topn ) {
				$top12 = $line . $top12;
			}

			if ( $index < $topn ) {
				$line = "\n";
				$line .=$theme->slug;
				$line .=',';
				$line .=$theme->downloaded / 1000;
				$top12chart.=$line;
			}
		}
		$this->block_writer( 'heading', null, "<h2>Top $topn themes - total downloads</h2>" );

		$top12chart = "Theme,Downloads (K)" . $top12chart;
		$this->chart_writer( 'bar', $top12chart, 'Visualizer' );
		$top12 = "Position|Theme|Total downloads|Created\n" . $top12;
		$atts = [ 'content' => $top12 ];
		$this->block_writer( 'oik-bbw/csv', $atts, null );
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
	 * {
	"name": "Twenty Twenty-Two",
	"slug": "twentytwentytwo",
	"version": "1.0",
	"preview_url": "https:\/\/wp-themes.com\/twentytwentytwo\/",
	"author": {
	"user_nicename": "wordpressdotorg",
	"profile": "https:\/\/profiles.wordpress.org\/wordpressdotorg\/",
	"avatar": "https:\/\/secure.gravatar.com\/avatar\/61ee2579b8905e62b4b4045bdc92c11a?s=96&d=monsterid&r=g",
	"display_name": "WordPress.org",
	"author": "the WordPress team",
	"author_url": "https:\/\/wordpress.org\/"
	},
	"screenshot_url": "\/\/ts.w.org\/wp-content\/themes\/twentytwentytwo\/screenshot.png?ver=1.0",
	"rating": 82,
	"num_ratings": 8,
	"reviews_url": "https:\/\/wordpress.org\/support\/theme\/twentytwentytwo\/reviews\/",
	"downloaded": 5318,
	"last_updated": "2022-01-25",
	"last_updated_time": "2022-01-25 22:25:39",
	"creation_time": "2022-01-25 22:25:39",
	"homepage": "https:\/\/wordpress.org\/themes\/twentytwentytwo\/",
	"sections": {
	"description": "Built on a solidly designed foundation, Twenty Twenty-Two embraces the idea that everyone deserves a truly unique website. The theme\u2019s subtle styles are inspired by the diversity and versatility of birds: its typography is lightweight yet strong, its color palette is drawn from nature, and its layout elements sit gently on the page. The true richness of Twenty Twenty-Two lies in its opportunity for customization. The theme is built to take advantage of the Full Site Editing features introduced in WordPress 5.9, which means that colors, typography, and the layout of every single page on your site can be customized to suit your vision. It also includes dozens of block patterns, opening the door to a wide range of professionally designed layouts in just a few clicks. Whether you\u2019re building a single-page website, a blog, a business website, or a portfolio, Twenty Twenty-Two will help you create a site that is uniquely yours."
	},
	"download_link": "https:\/\/downloads.wordpress.org\/theme\/twentytwentytwo.1.0.zip",
	"tags": {
	"block-patterns": "Block Editor Patterns",
	"custom-colors": "Custom Colors",
	"custom-logo": "Custom Logo",
	"custom-menu": "Custom Menu",
	"editor-style": "Editor Style",
	"featured-images": "Featured Images",
	"full-site-editing": "Full Site Editing",
	"one-column": "One Column",
	"rtl-language-support": "RTL Language Support",
	"sticky-post": "Sticky Post",
	"threaded-comments": "Threaded Comments"
	},
	"requires": "5.9",
	"requires_php": "5.6"
	}

	 */

	function block_writer( $block_type_name, $atts = null, $content = null ) {
		$attributes = \oik\oik_blocks\oik_blocks_atts_encode( $atts );
		$this->content .= \oik\oik_blocks\oik_blocks_generate_block( $block_type_name, $attributes, $content );
		//echo $this->content;
	}

	/**
	 * Writes the output as a Chart block.
	 *
	 * @param $type
	 * @param $content - CSV content containing the heading
	 * @param null $theme
	 */

	function chart_writer( $type, $content, $theme=null ) {
		static $ID = 0;
		$ID++;
		$myChartID = 'myChart-' . $ID;
		$atts = [ 'type' => $type, 'content' => $content, 'myChartId' => $myChartID];
		if ( $theme ) {
			$atts['theme'] = $theme;
		}
		$html = '<div class="wp-block-oik-sb-chart chartjs">';
		$html .= '<canvas id="' . $myChartID . '"></canvas></div>';
		$this->block_writer( 'oik-sb/chart', $atts, $html);

	}

	function report_groups( $grouper ) {
		ob_start();
		$grouper->report_groups();
		$output = ob_get_clean();
		$atts = [ 'content' => $output ];
		$this->block_writer( 'oik-bbw/csv', $atts, null );
	}

	/**
	 * Outputs the group to a chart.
	 *
	 * @param $grouper
	 * @param $type
	 * @param $heading
	 * @param null $theme
	 * @param false $accum
	 */

	function chart_groups( $grouper, $type, $heading, $theme=null, $accum=false ) {
		ob_start();
		$grouper->report_groups( $accum );
		$output = ob_get_clean();
		//$atts = [ 'content' => $output ];
		$output = rtrim( $output );
		$output = $heading . "\n" . $output;
		$this->chart_writer( $type, $output, $theme );
	}



	function echo_content() {
		echo $this->content;
		$this->content = null;
	}

	function fse_theme_reports() {
		$this->load_all_themes();
		$this->report_downloaded();
		//$this->report_created();

	}

	function report_downloaded() {
		foreach ( $this->fse_themes as $key => $theme ) {
			echo $theme->slug;
			echo ',';
			echo $theme->downloaded;
			echo ',';
			echo $theme->creation_time;
			echo PHP_EOL;
		}
	}

	function report_created() {
		foreach ( $this->themes as $key => $theme ) {

		}
	}


}

/*
 * stdClass Object
(
    [name] => Zoologist
    [slug] => zoologist
    [version] => 1.0.20
    [preview_url] => https://wp-themes.com/zoologist/
    [author] => stdClass Object
        (
            [user_nicename] => automattic
            [profile] => https://profiles.wordpress.org/automattic
            [avatar] => https://secure.gravatar.com/avatar/687b3bf96c41800814e3b93766444283?s=96&d=monsterid&r=g
            [display_name] => Automattic
            [author] => Automattic
            [author_url] => https://automattic.com/
        )

    [screenshot_url] => //ts.w.org/wp-content/themes/zoologist/screenshot.png?ver=1.0.20
    [rating] => 0
    [num_ratings] => 0
    [reviews_url] => https://wordpress.org/support/theme/zoologist/reviews/
    [downloaded] => 1558
    [last_updated] => 2021-12-09
    [last_updated_time] => 2021-12-09 17:05:43
    [creation_time] => 2021-11-01 05:15:40
    [homepage] => https://wordpress.org/themes/zoologist/
    [sections] => stdClass Object
        (
            [description] => Zoologist is a simple blogging theme that supports full-site editing.
        )

    [download_link] => https://downloads.wordpress.org/theme/zoologist.1.0.20.zip
    [tags] => stdClass Object
        (
            [custom-colors] => Custom Colors
            [custom-logo] => Custom Logo
            [custom-menu] => Custom Menu
            [editor-style] => Editor Style
            [featured-images] => Featured Images
            [full-site-editing] => Full Site Editing
            [one-column] => One Column
            [rtl-language-support] => RTL Language Support
            [theme-options] => Theme Options
            [threaded-comments] => Threaded Comments
            [translation-ready] => Translation Ready
            [wide-blocks] => Wide Blocks
        )

    [template] => blockbase
    [parent] => stdClass Object
        (
            [slug] => blockbase
            [name] => Blockbase
            [homepage] => https://wordpress.org/themes/blockbase/
        )

    [requires] => 5.8
    [requires_php] => 5.7
)
 */


