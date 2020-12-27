<?php



class Plugin_Extractor {

	private $domdoc;

	private $plugins=[];

	private $csv_file = 'block_plugins.csv';

	function __construct() {
		$this->narrator=Narrator::instance();
	}

	/**
	 * Returns true if the file was last modified some time in the last n days.
	 *
	 * @param $filename
	 * @return bool
	 */
	function is_file_recent( $filename, $days=1 ) {
		$time = filemtime( $filename );
		$today = ( time() - $time ) <  ( $days * 86400 ) ;
		return $today;
	}

	function list_plugins() {
		if ( file_exists( $this->csv_file ) ) {
			if ( $this->is_file_recent( $this->csv_file ) ) {
				$this->load_csv_file( $this->csv_file );
				return;
			}
		}
		$this->fetch_plugin_list();
	}

	function load_csv_file() {
		$file = file( $this->csv_file, FILE_IGNORE_NEW_LINES );
		$count = count( $file );
		for ( $i=1; $i<$count; $i++ ) {
			list( $plugin, $description) = explode( ',', $file[ $i ] );
			$this->add_plugin( $plugin, $description );
		}
	}

	function fetch_plugin_list() {
		$base_query='https://wordpress.org/plugins/browse/blocks';
		$content   =file_get_contents( $base_query );
		$this->loadHTML( $content );
		$this->extract_articles();
		for ( $page=2; $page < 35; $page ++ ) {
			$query  =$base_query . '/page/' . $page;
			$content=file_get_contents( $query );
			$this->loadHTML( $content );
			$processed = $this->extract_articles();
			if ( $processed < 20 ) {
				continue;
			}
		}
	}

	function loadHTML( $content ) {
		//print_r( $content );
		$this->domdoc=new DOMDocument();
		libxml_use_internal_errors( true );
		$this->domdoc->loadHTML( $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_use_internal_errors( false );
	}

	/**
	 * Returns the array of plugins.
	 *
	 * @return array
	 */
	function get_plugins() {
		return $this->plugins;
	}

	/**
	 * Extracts the plugins from the articles.
	 *
	 * Finds each article and extracts the plugin information.
	 */
	function extract_articles() {
		$start = count( $this->plugins );
		$articles=$this->domdoc->getElementsByTagName( 'article' );
		foreach ( $articles as $article ) {
			//echo $article->nodeValue;
			$this->extract_article( $article );
		}
		$end = count( $this->plugins );
		$processed = $end = $start;
		return $processed;
	}

	function extract_article( $article ) {
		$h3=$article->getElementsByTagName( 'h3' );
		foreach ( $h3 as $node ) {
			$entry_title = $node->nodeValue;
			$plugin_slug = $this->get_plugin_slug( $node );
			$this->add_plugin( $plugin_slug, $entry_title );
		}
	}

	function get_plugin_slug( $node ) {
		$link = $node->getElementsByTagName( 'a');
		$plugin_slug =  $link[0]->getAttribute( 'href');
		$this->narrator->narrate( 'Plugin', $plugin_slug );
		return $plugin_slug;
	}


	/**
	 * "GB? 1=Yes, 0=No",Is it?,Slug,Title,Downloads,Last update,Required,Tested,Active,Keywords
	 * 0,block,tolero-spam-filter,Tolero spam filter,990,21/03/2007 14:03,1.5.1.2,2.1.2,20,"antispam,spam,spam filter,spamblocker"
	 * 0,block,jc-iprestrictions,JC-IPRestrictions,3546,16/06/2007 04:41,2.0.4,2.2,30,"block,ip,access,restrict,password"
	 * 0,block,wp-blockyou,WP-BlockYou,4186,29/04/2008 15:08,2.0.0,2.5.1,40,"admin,comments,spam,block,ban"
	 */
	function add_plugin( $plugin, $entry_title ) {
		$this->plugins[ $plugin ] = $entry_title;
	}

	function write_plugins_csv() {
		$this->csv = 'Plugin,Description' . PHP_EOL;
		foreach ( $this->plugins as $plugin => $description ) {
			$this->plugin_line( $plugin, $description );
		}
		file_put_contents( $this->csv_file, $this->csv );
	}

	function plugin_line( $plugin, $description ) {
		$fields = [ $plugin, $description];
		$line = implode( ',', $fields );
		$this->csv .= $line . PHP_EOL;
	}

}


/**

<article class="plugin-card post-159 plugin type-plugin status-publish hentry plugin_section-blocks plugin_category-seo-and-marketing plugin_category-social-and-sharing plugin_category-taxonomy plugin_contributors-atimmer plugin_contributors-jipmoors plugin_contributors-joostdevalk plugin_contributors-omarreiss plugin_contributors-tacoverdo plugin_contributors-yoast plugin_committers-ireneyoast plugin_committers-jipmoors plugin_committers-joostdevalk plugin_committers-omarreiss plugin_committers-yoast plugin_support_reps-amboutwe plugin_support_reps-awesomesaurus plugin_support_reps-benvaassen plugin_support_reps-devnihil plugin_support_reps-djennez plugin_support_reps-jeroenrotty plugin_support_reps-jerparx plugin_support_reps-marcanor plugin_support_reps-martijnvaneeghem plugin_support_reps-mayadaibrahim plugin_support_reps-maybellyne plugin_support_reps-mazedulislamkhan plugin_support_reps-michielatyoast plugin_support_reps-mikes41720 plugin_support_reps-monbauza plugin_support_reps-onlyincebu plugin_support_reps-pcosta88 plugin_support_reps-priscillamc plugin_support_reps-suascat_wp plugin_support_reps-tacoverdo plugin_support_reps-tdevalk plugin_tags-content-analysis plugin_tags-readability plugin_tags-schema plugin_tags-seo plugin_tags-xml-sitemap">
<div class="entry-thumbnail">
<a href="https://wordpress.org/plugins/wordpress-seo/" rel="bookmark">
<img class='plugin-icon' srcset='https://ps.w.org/wordpress-seo/assets/icon.svg?rev=2363699, https://ps.w.org/wordpress-seo/assets/icon-256x256.png?rev=2363699 2x' src='https://ps.w.org/wordpress-seo/assets/icon-256x256.png?rev=2363699'>		</a>
</div><div class="entry">
<header class="entry-header">
<h3 class="entry-title"><a href="https://wordpress.org/plugins/wordpress-seo/" rel="bookmark">Yoast SEO</a></h3>		</header><!-- .entry-header -->

<div class="plugin-rating"><div class="wporg-ratings" aria-label="5 out of 5 stars" data-title-template="%s out of 5 stars" data-rating="5" style="color:#ffb900"><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span></div><span class="rating-count">(<a href="https://wordpress.org/support/plugin/wordpress-seo/reviews/">27,293<span class="screen-reader-text"> total ratings</span></a>)</span></div>
<div class="entry-excerpt">
<p>Improve your WordPress SEO: Write better content and have a fully optimized WordPress site using&hellip;</p>
</div><!-- .entry-excerpt -->
</div>
<hr>
<footer>
<span class="plugin-author">
<i class="dashicons dashicons-admin-users"></i> Team Yoast		</span>
<span class="active-installs">
<i class="dashicons dashicons-chart-area"></i>
5+ million active installations		</span>
<span class="tested-with">
<i class="dashicons dashicons-wordpress-alt"></i>
Tested with 5.6			</span>
<span class="last-updated">
<i class="dashicons dashicons-calendar"></i>
Updated 1 week ago		</span>
</footer>
</article>
 */




