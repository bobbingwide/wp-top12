<?php

/**
 * @copyright (C) Copyright Bobbing Wide 2024
 * @package wp-top12
 */

class WP_top12_block_data_renderer {

	private $_total_downloads = 12000000001;
	private $_previous_total_downloads = 12000000000;
	private $_plugins = 60000;
	private $_previous_plugins = 59999;

	private $posts;

	private $plugins_chart_content;
	private $total_downloads_chart_content;

	// private $cached_data =

	/**
	 * Constructs the top12 block data renderer class.
	 *
	 * Data is loaded from the most recent posts inf the top 10 category ( ID 3).
	 * We assume that the required fields have been populated.
	 *
	 */
	function __construct() {
		$this->posts = get_posts( ['category' => 3, 'numberposts' => 12 ]);
		$this->_total_downloads = get_post_meta( $this->posts[0]->ID, '_total_downloads', true);
		$this->_plugins = get_post_meta(  $this->posts[0]->ID, '_plugins', true );

		$this->_previous_total_downloads = get_post_meta( $this->posts[1]->ID, '_total_downloads', true);
		$this->_previous_plugins = get_post_meta(  $this->posts[1]->ID, '_plugins', true );

		$this->build_dynamic_chart_content();
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


	function get_block_field_methods() {
		$block_field_methods = [];
		$block_field_methods['roelmagdaleno/wp-countup-js']['_plugins'] = 'countup_plugins' ;
		$block_field_methods['roelmagdaleno/wp-countup-js']['_total_downloads'] = 'countup_total_downloads' ;
		$block_field_methods['oik-sb/chart']['_plugins'] = 'chart_plugins';
		$block_field_methods['oik-sb/chart']['_total_downloads'] = 'chart_total_downloads';
		return $block_field_methods;
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
		$block_field_methods = $this->get_block_field_methods();

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
	function wp_top12_fiddle_parsed_block( $parsed_block, $new_data_end, $new_data_start ) {

		$data_end = $parsed_block['attrs']['end'];
		$data_start = $parsed_block['attrs']['start'];
		$parsed_block['innerHTML'] = str_replace( $data_end, $new_data_end, $parsed_block['innerHTML'] );
		$parsed_block['innerHTML'] = str_replace( $data_start, $new_data_start, $parsed_block['innerHTML'] );
		$parsed_block['innerContent'][0] = $parsed_block['innerHTML'];
		$parsed_block['attrs']['end'] = $new_data_end;
		$parsed_block['attrs']['start'] = $new_data_start;
		//bw_trace2();
		return $parsed_block;
	}

	function countup_plugins( $parsed_block ) {
		$new_data_end = $this->_plugins;
		$new_data_start = $this->_previous_plugins;
		$parsed_block = $this->wp_top12_fiddle_parsed_block( $parsed_block, $new_data_end, $new_data_start );
		return $parsed_block;
	}

	function countup_total_downloads( $parsed_block ) {
		$new_data_end = $this->_total_downloads;
		$new_data_start = $this->_previous_total_downloads;
		$parsed_block = $this->wp_top12_fiddle_parsed_block( $parsed_block, $new_data_end, $new_data_start );
		return $parsed_block;

	}

	function chart_plugins( $parsed_block ) {
		$parsed_block['attrs']['content'] = $this->plugins_chart_content;
		bw_trace2();
		return $parsed_block;
	}

	function chart_total_downloads( $parsed_block ) {
		$parsed_block['attrs']['content'] = $this->total_downloads_chart_content;
		bw_trace2();
		return $parsed_block;
	}

	/**

<!-- wp:oik-sb/chart {"content":"Date,Total downloads (M)\n2024-02-07,9951\n2024-02-08,9961\n2024-02-10,9970\n2024-02-12,9976\n2024-02-14,9988\n2024-02-15,10000","myChartId":"myChart-0","height":200,"time":true,"timeunit":"quarter"} -->
<div class="wp-block-oik-sb-chart"><div class="chartjs" style="height:200px"><canvas id="myChart-0"></canvas></div></div>
<!-- /wp:oik-sb/chart -->
	 */
	function build_dynamic_chart_content() {
		$this->plugins_chart_content = 'Date,#plugins';
		$this->total_downloads_chart_content = 'Date,Total downloads (M)';
		$plugins_array = [];
		$total_downloads_array = [];
		foreach ( $this->posts as $post ) {
			$date = substr( $post->post_date, 0, 10 );
			$_plugins = get_post_meta( $post->ID, '_plugins', true );
			$_total_downloads = get_post_meta( $post->ID, '_total_downloads', true );
			//echo $_total_downloads . '.';
			if ( $_total_downloads ) {
				$millions               =round( $_total_downloads / 1000000 );
				$total_downloads_array[]="$date,$millions";
			}
			$plugins_array[] = "$date,$_plugins";

		}
		$plugins_array[] = 'Date,#plugins';
		arsort( $plugins_array );
		$this->plugins_chart_content = implode( "\n", $plugins_array );
		$total_downloads_array[] = 'Date,Total downloads (M)';
		arsort( $total_downloads_array );
		$this->total_downloads_chart_content = implode( "\n", $total_downloads_array );
		//print_r( $plugins_array );
		//arsort( $total_downloads_array );

}

}
