=== Nearby WordPress Events ===
Contributors:      andreamiddleton, azaozz, camikaos, coreymckrill, chanthaboune, courtneypk, dd32, iandunn, iseulde, mapk, mayukojpn, obenland, pento, samuelsidler, stephdau, tellyworth
Donate link:       https://eff.org
Tags:              meetup, wordcamp, events, dashboard widget
Requires at least: 4.7
Tested up to:      4.7
Stable tag:        0.2
License:           GPL2
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Shows you upcoming local WordPress events in your wp-admin Dashboard


== Description ==

The plugin updates the existing WordPress News dashboard widget to also include upcoming meetup events and WordCamps near the current user's location. If you have multiple users on your site, each one will be shown the events that are close to their individual location. The dashboard widget will try to automatically detect their location, but they'll also be able to enter any city they like.


### Why?

The community that has been created around WordPress is one of its best features, and one of the primary reasons for its success, but many users are still unaware that it exists, and aren't taking advantage of all of the resources that it makes available to them.

Inviting more people to join the community will help to increase its overall health, diversity, and effectiveness, which in turn helps to ensure that WordPress will continue to thrive in the years to come.

wp-admin is the perfect place to display these events, because that’s the place where almost all WordPress users are visiting already. Instead of expecting them to come to us, we can bring the relevant information directly to them.


== Frequently Asked Questions ==

= What information is collected, and what is it used for? =

The plugin sends each user's timezone, locale, and IP address to `api.wordpress.org`, in order to determine their location, so that they can be shown events that are close to that location. If the user requests events near a specific city, then that is also sent. The data is not stored permenantly, not used for any other purpose, and not shared with anyone outside of WordPress.org, with the exception of any conditions covered in the [WordPress.org privacy policy](https://wordpress.org/about/privacy/).



== Screenshots ==

1. The new combined Events and News widget when a location and events are available
2. The widget when no location is available
3. The widget when a location is available, but there are no upcoming events nearby


== Installation ==

For help installing this (or any other) WordPress plugin, please read the [Managing Plugins](http://codex.wordpress.org/Managing_Plugins) article on the Codex.


== Changelog ==

= 0.2 (2017-03-24) =
* [FIX] Fix a bug that prevented events from being cached. The widget loads much faster now.
* [FIX] Fix a bug that prevented debugging info from being added to AJAX responses.

= 0.1 (2017-03-20) =
* First version


== Upgrade Notice ==

= 0.2 =
This version has a few minor bugs fixes and user-experience improvements.
