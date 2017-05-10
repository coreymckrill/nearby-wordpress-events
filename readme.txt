=== Nearby WordPress Events ===
Contributors:      afercia, andreamiddleton, azaozz, camikaos, coreymckrill, chanthaboune, courtneypk, dd32, iandunn, iseulde, mapk, mayukojpn, melchoyce, nao, obenland, pento, samuelsidler, stephdau, tellyworth
Donate link:       https://eff.org
Tags:              meetup, wordcamp, events, dashboard widget
Requires at least: 4.7
Tested up to:      4.7.4
Stable tag:        0.8
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

The plugin sends each user's timezone, locale, and IP address to `api.wordpress.org`, in order to determine their location, so that they can be shown events that are close to that location. If the user requests events near a specific city, then that is also sent. The data is not stored permanently, not used for any other purpose, and not shared with anyone outside of WordPress.org, with the exception of any conditions covered in the [WordPress.org privacy policy](https://wordpress.org/about/privacy/).



== Screenshots ==

1. The new combined Events and News widget when a location and events are available
2. The widget when no location is available
3. The widget when a location is available, but there are no upcoming events nearby


== Installation ==

For help installing this (or any other) WordPress plugin, please read the [Managing Plugins](http://codex.wordpress.org/Managing_Plugins) article on the Codex.


== Changelog ==
= 0.8 (2017-05-10) =
* [FIX] Update criteria plugin uses to detect if the functionality has been merged into Core.
* [FIX] Bring back the Cancel button on the city search form.
* [FIX] Minor UI tweaks and semantic code changes.

= 0.7 (2017-05-03) =
* [NEW] Dynamic content changes are announced to screenreaders.
* [NEW] Log API responses to aid with troubleshooting.
* [FIX] Minimize re-rendering of dynamic content to aid screenreaders.

= 0.6 (2017-04-24) =
* [FIX] Fixed fatal conflict with asynchronous uploads by restricting the bootstrap process to only the contexts where it's necessary.
* [FIX] Restore the behavior that automatically focuses the input on the city field when toggling the location form.

= 0.5 (2017-04-21) =
* [SECURITY] Harden the city display name against a theoretical cross-site scripting attack.
* [FIX] Add a label to the city input field, instead of relying on the placeholder.
* [FIX] Handled AJAX error more gracefully
* [FIX] Events older than 24 hours are no longer shown
* [NEW] The location icon can now be clicked on to close the location form
* [NEW] The plugin will disable itself if it detects that the functionality has been merged into Core

= 0.4 (2017-04-11) =
* [FIX] Improved the layout on mobile devices.
* [FIX] Added styles for right-to-left languages.
* [NEW] Added the event's time and day of the week, so that users don't have to open the event link to see if it fits their schedule.

= 0.3 (2017-03-31) =
* [SECURITY] Harden the error message handling against a theoretical cross-site scripting attack.
* [FIX] Locations are now saved network-wide in Multisite installs, so you no longer have to set your location on each site. Unfortunately, you may need to re-save your location the first time you visit wp-admin because of this.
* [FIX] Events are now cached network-wide in Multisite installs, to improve performance.
* [FIX] Events are now shown on the Network Dashboard in Multisite installs.

= 0.2 (2017-03-24) =
* [FIX] Fix a bug that prevented events from being cached. The widget loads much faster now.
* [FIX] Fix a bug that prevented debugging info from being added to AJAX responses.

= 0.1 (2017-03-20) =
* First version


== Upgrade Notice ==

= 0.8 =
This version updates the check that disables the plugin if its functionality has been merged into Core.

= 0.7 =
This version makes several improvements for screenreaders and fixes minor bugs.

= 0.6 =
This version fixes a critical bug in 0.5 that caused file uploads to break in certain situations.

= 0.5 =
This version fixes several bugs and accessibility issues, and protects against a theoretical security vulnerability.

= 0.4 =
This version displays the event time and day of the week, and fixes a few small bugs.

= 0.3 =
This version fixes a few bugs in Multisite installs, and protects against a theoretical security vulnerability.

= 0.2 =
This version has a few minor bugs fixes and user-experience improvements.
