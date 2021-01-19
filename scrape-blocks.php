<?php

/**
 * @package wp-top12
 * @copyright (C) Copyright Bobbing Wide 2020, 2021
 *
 * Scrapes the block plugins listed on https://wordpress.org/plugins/browse/blocks/page/n
 * where n goes up to 25 or so, with 20 block plugins per page.
 *
 * - Produces a CSV file of block plugins ( block_plugins.csv )
 * - Which is used extract the blocks for each plugin
 * - into wp-plugins-blocks.csv
 *
 * Note: The Plugin Extractor uses the most recent block_plugins.csv file if it's less than 1 day old.
 * The Block Counter's extraction logic is not optimised.
 */

oik_require( 'libs/class-narrator.php', 'oik-i18n');
oik_require( 'class-plugin-extractor.php', 'wp-top12');
oik_require( 'wp-block-counter.php', 'wp-top12');

$narrator = Narrator::instance();
$narrator->narrate( "scrape-blocks", 'starting');
$extractor = new Plugin_Extractor();
$narrator->narrate( 'Listing plugins', '');
$extractor->list_plugins();
$plugins = $extractor->get_plugins();
$extractor->write_plugins_csv();
$narrator->narrate( 'Finding blocks', '');
$wp_block_counter = new wp_block_counter();
$wp_block_counter->set_plugins( $plugins );
$wp_block_counter->count_plugins_blocks();
$wp_block_counter->write_csv();
