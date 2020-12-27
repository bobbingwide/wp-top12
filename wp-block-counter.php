<?php

/**
 * @package wp-top12
 * @copyright (C) Copyright Bobbing Wide 2019,2020
 */

class wp_block_counter {

	public $plugins = null;

	public $slug = null;

	public $blocks = null;

	public $domdoc = null;

	public $narrator = null;
	/**
	 * set up logic
	 *
	 * - ensure any database updates are rolled back
	 */
	function __construct() {
		//parent::setUp();
		oik_require_lib( "class-oik-remote");
		$this->narrator = Narrator::instance();
	}

	/**
	 * Loads the plugins in the filtered file.
	 * Plugins which are considered to be Gutenberg have a first column value of 1
	 */
	function load_filtered_file( $plugins_file='block_plugins.csv') {
		//$plugins_file = 'block_plugins-20191021-filtered.csv';
		//$plugins_file = 'block_plugins.csv';
		$plugins = file( $plugins_file );
		echo count( $plugins );
		echo PHP_EOL;

		echo "Plugin,Description,#blocks,Blocks" . PHP_EOL;
		return $plugins;
	}

	function set_plugins( $plugins ) {
		$this->plugins = $plugins;
	}

	/**
	 * Loads the plugin page from wordpress.org
	 * @param $slug
	 *
	 * @return decoded|null
	 */
	function load_plugin_page( $slug ) {
		$this->narrator->narrate( "Fetching", $slug );
		if ( 0 !== strpos( $slug, 'https://') ) {
			$url='https://wordpress.org/plugins/';
			$url.=$slug;
		} else {
			$url = $slug;
		}
		$response = oik_remote::bw_remote_get( $url, false );
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
	 *
	 * It's just a simple list now!
	 * <div id="blocks" class="plugin-blocks section">
	<h2 id="blocks-header">Blocks</h2>

	<p>This plugin provides 1 block.</p>
	<ul class="plugin-blocks-list">
	<li class="plugin-blocks-list-item has-description">
	<span class="block-icon dashicons dashicons-list-view"></span>
	<span class="block-title">Children</span>
	<span class="block-description">List children of the current content as links.
	</span></li>
	</ul>
	</div>
	 *
	 * @param $plugin_page
	 *
	 * @return string|null
	 */


	function extract_blocks_v1( $plugin_page ) {
		$between = $this->fetch_between( $plugin_page, '<div id="blocks" class="plugin-blocks section">', '</div>' );
		//echo $between;
		$terms = [];
		if ( $between ) {
			//gob();
			$between = $this->fetch_between( $between, '<dl>', '</dl>' );
			//oik_require_lib( 'hexdump');
			if ( !function_exists( "oik_hexdump") ) {
				oik_require( "libs/hexdump.php", "oik-batch" );
			}
			$between = str_replace(  ["\r", "\t" ], '', $between );
			//echo oik_hexdump( $between );

			$thelines = explode( "\n", $between );
			//print_r(  $thelines );
			//echo PHP_EOL;
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
		$blocks .= implode( ',', $terms );
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
	function count_plugins_blocks() {
		$this->index = 0;
		foreach ( $this->plugins as $slug => $description ) {
			$blocks = '';
			$this->index++;
			$this->set_slug( $slug );
			$plugin_page = $this->load_plugin_page( $slug );
			if ( $plugin_page ) {
				$this->extract_blocks( $slug, $plugin_page );
			}
			//echo "$index,$slug,$blocks" . PHP_EOL;

		}
	}

	/**
	 * Sets the plugin slug.
	 *
	 * @param $slug
	 */

	function set_slug( $slug ) {
		$this->slug = basename( $slug );
	}

	function loadHTML( $content ) {
		//print_r( $content );
		$this->domdoc=new DOMDocument();
		libxml_use_internal_errors( true );
		$this->domdoc->loadHTML( $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_use_internal_errors( false );
	}


	/**
	 * Extracts the blocks listed.
	 *
	 * @param $plugin
	 */
	function extract_blocks( $slug, $plugin_page ) {
		$this->loadHTML( $plugin_page );

		// Find the id=blocks section
		// Extract each list item
	//		<div id="blocks" class="plugin-blocks section">
		$list=$this->domdoc->getElementsByTagName( 'ul' );
		$found_blocks = false;
		if ( $list ) {
			foreach ( $list as $listitem ) {
				$class=$listitem->getAttribute( 'class' );
				//$this->narrator->narrate( 'class', $class );
				if ( $class === 'plugin-blocks-list' ) {
					$found_blocks = true;
					$blocks=$listitem->getElementsByTagName( 'li' );

					foreach ( $blocks as $block ) {
						$class = $block->getAttribute( 'class' );
						//$this->narrator->narrate( 'class', $class );
						if ( false !== strpos( $class, 'plugin-blocks-list-item' ) ) {
							$this->getBlockInfo( $block );

						}
					}
				}
			}
		}

		/**
		 * If we didn't find any blocks for the plugin produce a dummy line
		 * where YGIAGAM means Your Guess Is As Good As Mine.
		 */
		if ( !$found_blocks ) {
			$this->narrator->narrate( $slug, 0 );
			$this->add_block( ',,YGIAGAM' );
		}


	}

	/**
	 * Gets the block's information
	 *
	 * extract the block's icon, name and description
	 *
	 * <div id="blocks" class="plugin-blocks section">
	<h2 id="blocks-header">Blocks</h2>

	<p>This plugin provides 1 block.</p>
	<ul class="plugin-blocks-list">
	<li class="plugin-blocks-list-item has-description">
	<span class="block-icon dashicons dashicons-list-view"></span>
	<span class="block-title">Children</span>
	<span class="block-description">List children of the current content as links.</dd>
	</li>
	</ul>
	</div>
	 */
	function getblockinfo( $block ) {
		$spans = $block->getElementsByTagName( 'span' );
		$info = [];
		foreach ( $spans as $span ) {
			if ( $span->nodeValue ) {
				$value = trim( $span->nodeValue );
				$info[] = $value;
			} else {
				$class =  $span->getAttribute( 'class');
				$class = str_replace( 'block-icon dashicons ', '', $class );
				$class = str_replace( 'dashicons-', '', $class );
				$info[] = $class;
			}
		}
		$blockinfo = implode( ',', $info);
		$this->narrator->narrate( "block", $blockinfo);
		$this->add_block( $blockinfo );
	}

	function add_block( $blockinfo) {
		$line = $this->slug;
		$line .= ',';
		$line .= $blockinfo;
		$this->blocks[] = $line;
		//print_r( $this->blocks );

	}

	function write_csv( ) {
		//print_r( $this->blocks );
		$contents = 'Plugin,Icon,Title,Description';
		$contents .= PHP_EOL;
		$contents .= implode( PHP_EOL, $this->blocks);
		file_put_contents( 'wp-plugins-blocks.csv', $contents );
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


