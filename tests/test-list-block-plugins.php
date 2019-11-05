<?php // (C) Copyright Bobbing Wide 2019


class Tests_list_block_plugins extends BW_UnitTestCase {

	public $plugins = null;
	/** 
	 * set up logic
	 * 
	 * - ensure any database updates are rolled back
	 */
	function setUp(): void {
		parent::setUp();
		
		
	}
	
	/**
	 * We want to test in en_GB since we want translations to be performed
	 * The trouble is, in en_GB null translates to "0" ?
	 * I've raised #41257 against this problem.
	 */
	
	function load_dions_file() {
		$plugins_file = 'W.org plugins @ 2019-10-21 - Sheet1.csv';
		$plugins = file( $plugins_file );
		echo count( $plugins );
		return $plugins;

	}

	/**
	 * E532580,slug,name,downloads,last_updated,requires_wp,tested_wp,active_installs,tags
	 */

	function dont_test_for_blocks() {
		$plugins = $this->load_dions_file();
		$this->block_plugins = [];
		echo "Is it?,Slug,Title,Downloads,Last update,Required,Tested,Active,Keywords" . PHP_EOL;
		foreach ( $plugins as $index => $plugin ) {

			$this->is_it_a_block_plugin( $plugin );

		}
		echo count( $this->block_plugins );
	}

	function is_it_a_block_plugin( $plugin) {
		$tomatch = [ 'block', 'gutenberg'];
		$matches = [];
		foreach ( $tomatch as $match ) {
			if ( false !== strpos( $plugin, $match ) ) {
				$matches[] = $match;

			}
		}
		if ( count( $matches ) ) {
			echo '"' . implode( ',', $matches ) . '",';
			echo $plugin;
		}
	}

	function test_something_then() {
		$this->assertTrue( true  );
	}
	

		
		


}
