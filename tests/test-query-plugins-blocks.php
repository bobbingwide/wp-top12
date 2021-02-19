<?php // (C) Copyright Bobbing Wide 2019


class Tests_query_plugins_blocks extends BW_UnitTestCase {

	public $plugins = null;

	public $blocks = null;
	/**
	 * set up logic
	 *
	 * - ensure any database updates are rolled back
	 */
	function setUp(): void {
		parent::setUp();
		oik_require_lib( "class-oik-remote");


	}

	/**
	 * Loads the plugins in the filtered file.
	 * Plugins which are considered to be Gutenberg have a first column value of 1
	 */
	function load_filtered_file() {
		$plugins_file = 'working/block_plugins-20191021-filtered.csv';
		$plugins = file( $plugins_file );
		echo count( $plugins );
		return $plugins;

	}

	function just_the_ones( $plugins ) {
		$index = 0;
		foreach ( $plugins as $key => $plugin ) {
			$slug = $this->is_it_a_block_plugin( $plugin );
			if ( $slug ) {
				$index ++;
				$blocks = '';
				$count = 0;
				echo "$index,$slug,$count,$blocks" . PHP_EOL;
				$plugin_page = $this->load_plugin_page( $slug );
				if ( $plugin_page ) {
					$blocks = $this->extract_blocks( $plugin_page );
				}


				//$count = count( $blocks );
				echo "$index,$slug,$count,$blocks" . PHP_EOL;

			} else {
				echo '?';
			}

		}
	}

	function load_plugin_page( $slug ) {
		echo "Fetching: $slug" . PHP_EOL;
		$url = 'https://wordpress.org/plugins/';
		$url .= $slug;
		//$response = oik_remote::bw_remote_get( $url, false );
		//echo $response;
		return $response;

	}
	/**
	 * Fetches the content between two strings with a known value
     *
     * @param string $string - input string which may contain $before and $after
     * @param string $before - substring of the before part
     * @param string $after - substring of the after part
     * @return null|string the string between before and after - not incl. the $before string
     */
	function fetch_between( $string, $before, $after ) {
		$between = null;
		$spos = strpos( $string, $before );
		if ( null !== $spos ) {
			$spos += strlen( $before );
			$rest = substr( $string, $spos );
			$epos = strpos( $rest, $after );
			if ( $epos ) {
				$between = substr( $rest, 0, $epos );
			}
		}
		return $between;
	}

	/**
	 *
	 *
	 */
	function get_test_string() {
		$string = '<div id="blocks" class="plugin-blocks section">
<h2 id="blocks-header">Blocks</h2>

<p>This plugin provides 2 blocks.</p>
<dl>
<dt>hmp/google-review-form-block</dt>
<dd></dd>
<dt>hmp/google-review-block</dt>
<dd></dd>
</dl>
</div>';
		return $string;
	}


	/**
	 * Extract the blocks from description list
	 * @param $plugin_page
	 *
	 * @return string|null
	 */


	function extract_blocks( $plugin_page ) {
		$between = $this->fetch_between( $plugin_page, '<div id="blocks" class="plugin-blocks section">', '</div>' );
		//echo $between;
		$terms = [];
		if ( $between ) {
			//gob();
			$between = $this->fetch_between( $between, '<dl>', '</dl>' );
			//echo $between;
			$thelines = explode( "\r\n", $between );
			//print_r(  $thelines );
			$count = 0;

			foreach ( $thelines as $line ) {
				$term = $this->fetch_between( $line, '<dt>', '</dt>');
				if (  $term ) {
					$saved = $term;
					$count++;
					$terms[] = $term;
				}

			}
		}

		$blocks = count( $terms );
		$blocks .= ',"';
		$blocks = implode( ',', $terms );
		$blocks .= '"';

		return $blocks;

	}


	function test_fetch_between( ) {
		$string = $this->get_test_string();
		$between = $this->extract_blocks( $string );
		$len = strlen( $between );
		$this->assertNotNull( $between );
		//$this->assertFalse( true );

	}


	/**
	 * Here we try to find the blocks that are associated with WordPress.org plugins
	 *
	 */
	function dont_test_for_plugins_blocks() {
		$plugins = $this->load_filtered_file();
		$test_plugins = [];
		$test_plugins[] = array_shift( $plugins );
		print_r( $test_plugins );
		$this->just_the_ones( $test_plugins );
	}

	function is_it_a_block_plugin( $plugin) {
		$slug = null;
		if ( 0 === strpos( $plugin, '1,' )) {
			$csv = str_getcsv( $plugin );

			$slug = $csv[2];

		}
		return $slug;
 	}






}
