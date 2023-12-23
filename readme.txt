=== wp-top12 ===
Contributors: bobbingwide, vsgloik
Donate link: https://www.oik-plugins.com/oik/oik-donate/
Tags: shortcodes, smart, lazy
Requires at least: 5.2
Tested up to: 6.4.2
Stable tag: 1.4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Display selected plugins by most downloaded from WordPress.org

Similar for FSE themes.


== Installation ==
1. Upload the contents of the wp-top12 plugin to the `/wp-content/plugins/wp-top12' directory
1. Activate the wp-top12 plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==


= What is provided? = 
Version 1.3.0 improves the support for cataloguing FSE themes.

Version 1.2.0 provides support for oik-update's oik-themer routine to automatically update FSE themes.

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
= 1.4.2 = 
Upgrade to enable better styling of the plugin table.

== Changelog ==
= 1.4.2 =
* Changed: Update wporg_plugins.csv to latest extract ( 22 Dec 2023 )
* Changed: Replace &nbsp; by blank when producing table.
* Changed: Show count of plugin versions
* Tested: With WordPress 6.4.2 and WordPress Multisite
* Tested: With PHP 8.1, PHP 8.2 and PHP 8.3
* Tested: With PHPUnit 9.6