=== wp-top12 ===
Contributors: bobbingwide, vsgloik
Donate link: https://www.oik-plugins.com/oik/oik-donate/
Tags: shortcodes, smart, lazy
Requires at least: 5.2
Tested up to: 5.6
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Display selected plugins by most downloaded from WordPress.org


== Installation ==
1. Upload the contents of the wp-top12 plugin to the `/wp-content/plugins/wp-top12' directory
1. Activate the wp-top12 plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==


= What is provided? = 
Version 1.1.0 provides the wp-top12/wp-top12 block.

Version 1.0.0 provides the [wp-top12] shortcode in a new main plugin routine ( wp-top12.php ).

The downloads.php routine has been changed to use the WordPress REST API.

Other batch routines associated with performance analysis will be transferred to slog-bloat. 


= What was this plugin's original use? = 

Back in 2012 this plugin provided mechanisms to post process daily trace summary report files

* download for analysis and comparison
* produce summary reports
* use as input to drive performance tests

It's been a long time since I did this.

In version 0.0.1 there were 5 routines:

- vt.php -
- vt-stats.php - Count the requests over a period of time ( from 2015/10/01 to ... )
- vt-top12.php - Generate summary report comparing different test runs
- vt-driver.php - Run a set of sample requests to a website
- vt-ip.php - Summarises requests by IP address


Note: vt originally came from the bwtrace.vt.mmdd filename which is so named since it records
value text pairs ( see bw_trace_vt() ).

Other routines:

merger.php - Merge two simple CSV files into one
reducer.php - Routine to help find queries that result on more than one server transaction
downloads.php - Extracts information about plugins from wordpress.org



= What else do I need? =

* oik-bwtrace to produce the files in the first place
* oik-batch ( an alternative to WP-cli ) to drive the routines
* oik-lib, oik and other libraries used by wp-top12
* sb-chart-block 

= How has it been used? =

Originally developed in Herb Miller's play area to help compare performance of different hosting solutions
it was extended at the end of 2015 during the "12 days of Christmas" to analyse the effect of the top 12 
WordPress plugins on server execution time. 

wp-top12 contains the routines specifically used against local copies of the website in question.

= What is the slog plugin? =

The slog plugin is intended to be the generic solution to enable analysis of server response in other situations.
The source code was cloned from the wp-top12 plugin in early January 2016.

slog will be implemented initially using oik-batch but will eventually be compatible with WP-cli.
There may be an admin interface, but unlikely to be a front-end.


== Screenshots ==
1. wp-top12 in action - no not really

== Upgrade Notice ==
= 1.1.0 = 
Upgrade for the Top12 plugins block ( wp-top12/wp-top12 ).

= 1.0.0 = 
Update for the latest figures - 18 Nov 2020

= 1.0.0-beta-20191226 =
Update for latest figures - Boxing Day 2019 

= 1.0.0-alpha-20191216 = 
Updated to make it easier to generate the Top 12 blog post

= 1.0.0-alpha-20191213 = 
Needs admin/oik-activation.php in order to report dependency on oik

= 1.0.0-alpha-20191119 = 
Updated extract file. Improvements to the wp-top12 shortcode for top-10-wp-plugins.com

= 1.0.0-alpha-20191107 =
Added downloads.php as a revamp for 2019.
Added wp-top12.php to tabulate selected plugins by Total downloads

= 0.0.1 =
Now supports initial analysis by IP address

= 0.0.0 =
New sample plugin, available from GitHub

== Changelog ==
= 1.1.0 = 
* Changed: Updated wporg_plugins.csv quite a few times... as total downloads approached 5 billion
* Added: Top 12 plugins block ( wp-top12/wp-top12 ),[github bobbingwide wp-top12 issues 8]
* Added: lots more


= 1.0.0 = 
* Changed: Updated wporg_plugins.csv a few times: Nov 18, Oct 21, Sep 07, Aug 19, Jul xx, May 15, Apr 15, Feb 08
* Changed: Caters for WordPress versions up to 5.9
* Fixed: Change order of parameter for implode. glue first,[github bobbingwide wp-top12 issues 5]
* Fixed: Cater for WordPress 5.5,[github bobbingwide wp-top12 issues 11]
* Fixed: Cater for WordPress 5.4
* Fixed: Keep going until there are no more files. Well... up to 1000
* Tested: With WordPress 5.6-beta4
* Tested: With PHP 7.4

= 1.0.0-beta-20191226 = 
* Changed: Generatated new version of wporg_plugins.csv from 2019/12/26. 55062 plugins
* Tested: With WordPress 5.3.2 

= 1.0.0-alpha-20191216 = 
* Changed: More improvements to reporting,[github bobbingwide wp-top12 issues 6]
* Changed: Generated new version of wporg_plugins.csv from 2019/12/15. 54924 plugins
* Tested: With WordPress 5.3.1 and WordPress Multi Site
* Tested: With PHP 7.3 and PHP 7.4

= 1.0.0-alpha-20191213 = 
* Fixed: Copied admin/oik-activation.php

= 1.0.0-alpha-20191119 = 
* Changed: Latest extract 2019-11-19. 54672 plugins,[github bobbingwide wp-top12 issues 6]
* Changed: Improve wp-top12 shortcode,[github bobbingwide wp-top12 issues 7]
* Fixed: Cater for plugin not being present on wordpress.org
* Tested: With WordPress 5.3 and WordPress Multi Site

= 1.0.0-alpha-20191107 = 
* Added: wp-top12.php for [wp-top12] shortcode,[github bobbingwide wp-top12 issues 7]
* Added: downloads.php incl. class-wp-org-downloads.php,[github bobbingwide wp-top12 issues 6]
* Changed: Moved a lot of files to working, so that they don't get zipped
* Changed: Downloads.php is no longer dependent upon play or wp-downloads
* Tested: With WordPress 5.2.4 and WordPress Multi Site
* Tested: With WordPress 5.3-RC4
* Tested: With PHP 7.3

= 0.0.1 = 
* Added: vt-ip.php to summarise requests by IP, showing total requests and elapsed time for high using IP addresses

= 0.0.0 =
* Added: New plugin

