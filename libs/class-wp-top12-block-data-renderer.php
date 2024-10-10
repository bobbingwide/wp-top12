<?php

/**
 * @copyright (C) Copyright Bobbing Wide 2024
 * @package wp-top12
 */

class WP_top12_block_data_renderer {

	private $_total_downloads = 12000000001;
	private $_plugins = 60000;

	function __construct() {

	}

	/**
	 * Implements 'render_block_data' for selected blocks.
	 *
	 * @param $parsed_block
	 * @param $source_block
	 * @param $parent_block
	 *
	 * @return mixed
	 */
	function render_block_data( $parsed_block, $source_block, $parent_block ) {
		$render_method = $this->get_render_method( $parsed_block );
		if ( $render_method ) {
			$parsed_block=$this->$render_method( $parsed_block );
		}
		return $parsed_block;
	}


	/**
	 * Returns the method to render the block data.
	 *
	 * @param $parsed_block
	 *
	 * @return null
	 */
	function get_render_method( $parsed_block ) {
		bw_trace2();
		$className = bw_array_get( $parsed_block['attrs'], 'className', '' );
		$render_method = null;
		$block_field_methods = [];
		$block_field_methods['roelmagdaleno/wp-countup-js']['_plugins'] = 'countup_plugins' ;
		$block_field_methods['roelmagdaleno/wp-countup-js']['_total_downloads'] = 'countup_total_downloads' ;
		foreach ( $block_field_methods as $block => $field_methods ) {
			if ( $block === $parsed_block['blockName']) {
				foreach ( $field_methods as $field => $method ) {
					//if ( str_contains( $parsed_block))
					$match = str_contains( $className, $field );
					if ( $match ) {
						  $render_method = $method;
					}
					//echo "$block => $field = $method", PHP_EOL;
				}
			}
		}
		return $render_method;
	}


	/**
	 * Updates the parsed block with the required values.
	 *
	 * Note: For the wp-countup-js block we need to hook into the post rendering filter as well.
	 * @TODO Find out why this is necessary.
	 *
	 * @param $parsed_block
	 *
	 * @return mixed
	 */
	function wp_top12_fiddle_parsed_block( $parsed_block, $new_data_end ) {

		$data_end = $parsed_block['attrs']['end'];
		$parsed_block['innerHTML'] = str_replace( $data_end, $new_data_end, $parsed_block['innerHTML'] );
		$parsed_block['innerContent'][0] = $parsed_block['innerHTML'];
		$parsed_block['attrs']['end'] = $new_data_end;
		//bw_trace2();
		return $parsed_block;
	}

	function countup_plugins( $parsed_block ) {
		$new_data_end = $this->_plugins;
		$parsed_block = $this->wp_top12_fiddle_parsed_block( $parsed_block, $new_data_end );
		return $parsed_block;
	}

	function countup_total_downloads( $parsed_block ) {
		$new_data_end = $this->_total_downloads;
		$parsed_block = $this->wp_top12_fiddle_parsed_block( $parsed_block, $new_data_end );
		return $parsed_block;

	}

}
