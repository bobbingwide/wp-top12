=== wp-top12 ===
Contributors: bobbingwide, vsgloik
Donate link: http://www.oik-plugins.com/oik/oik-donate/
Tags: shortcodes, smart, lazy
Requires at least: 4.4
Tested up to: 4.6-beta4
Stable tag: 0.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Measuring effect on server response of the Top 12 WordPress plugins


== Installation ==
1. Upload the contents of the wp-top12 plugin to the `/wp-content/plugins/wp-top12' directory
1. Activate the wp-top12 plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= What is this for? = 

wp-top12 provides mechanisms to post process daily trace summary report files

* download for analysis and comparison
* produce summary reports
* use as input to drive performance tests

= What is provided? = 

In version 0.0.1 there are 5 routines:

vt.php -
vt-stats.php -
vt-top12.php - 
vt-driver.php -
vt-ip.php - Summarises requests by IP address 

Note: vt comes from the bwtrace.vt.mmdd filename which is so named since it records
value text pairs ( see bw_trace_vt() ).




= What else do I need? =

* oik-bwtrace to produce the files in the first place
* oik-batch ( an alternative to WP-cli ) to drive the routines
* oik-lib, oik and other libraries used by wp-top12
* a charting routine such as visualizer

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
= 0.0.1 =
Now supports initial analysis by IP address

= 0.0.0 =
New sample plugin, available from GitHub

== Changelog ==
= 0.0.1 = 
* Added: vt-ip.php to summarise requests by IP, showing total requests and elapsed time for high using IP addresses

= 0.0.0 =
* Added: New plugin

