=== EVE ShipInfo ===
Contributors: AeonOfTime
Tags: EVE Online
Requires at least: 3.5
Tested up to: 4.7.4
Stable tag: 2.6
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.txt

Puts an EVE Online ships and ship fittings database in your WordPress website, along with high quality screenshots and specialized shortcodes. 

== Description ==

Using shortcodes, you can link EVE Online ship names in your posts and show inline information about EVE Online ships, including custom ship screenshots independent from the EVE Online website. Each ship also gets its own, fully detailed page in your blog with virtual pages. All EVE Online ships are bundled, including the skinned variants (Abaddon Tash-Murkon Edition) and special edition ships (Mimir) or even those you cannot fly (Immovable Enigma). With the EFT fittings management, import your fits from EFT and showcase your favorite fits with shortcodes.

= Features =

*   Portable EVE Online ships database with all 471 ships
*   942 high quality custom ship screenshots (front/side), separate download
*   Integrated themes for light and dark layouts
*   Link ship names to info popups or virtual ship pages within your blog
*   Extremely customizable ship lists shortcode
*   Ship galleries shortcode
*   Switchable themes for the ship info popups
*   Fittings importer and management: show your fits alongside ships or individually using shortcodes
*   Full integrated shortcode reference
*   Entirely translation-ready, including all ship attribute labels
*   For developers: easy object-oriented access to the ships database
*   Self-contained: no dependencies

= Ship screenshots pack =

Due to the size of the ship screenshots gallery, they are available as a separate download. Including
it would have made it impossible to install the plugin on most shared hosting packs, so on wordpress.org's
recommendation I did not include them in the repository.

You can download the screenshots pack here:

[Screenshots pack download page](http://aeonoftime.com/EVE_Online_Tools/EVE-ShipInfo-WordPress-Plugin/download.php)

Note: to install the screenshots pack, you will need access to your wordpress's uploads folder, for 
example via FTP. You have to upload the "eve-shipinfo" folder containing the screenshots directly
into the uploads folder.

== Installation ==

1. Install from your WordPress plugin manager
1. Activate the plugin
1. Go to your permalink settings, and save the settings without changing anything (to refresh the permalinks)
1. From the dashboard of the plugin, install a data file
1. Optional: Install the [screenshots pack](http://aeonoftime.com/EVE_Online_Tools/EVE-ShipInfo-WordPress-Plugin/download.php) (separate download)


== Changelog ==

= 2.6 = 
* Removed the bundled data files, wordpress.org does not allow including ZIP files
* Modified the database version check to reflect the missing data files

= 2.5 = 
* Fixed: Missing minified javascript and stylesheet sources

= 2.4 =
* Fixed: CRF attack possibilities in the fittings list
* Fixed: Fittings not showing their modules correctly
* Fixed: missing styling for fittings when using the Sytek skin
* Updated: Data package upgraded to the latest version 
* Added: Automatic nonces for better form security
* Added: possibility to re-import the same data file
* Improved: Fitting dialog enhancements, added links to copy the fitting and edit it in the backend when logged in
* Improved: Page slugs are now handled centrally for pages and page tabs
* Improved: The current and futute data packages now exclude ships that have no attributes to avoid errors (Pacifier for ex)

= 2.3 =
* Fixed: A missing eft slot type was triggering an exception
* Updated: Fitting slots now internally use the database information
* Updated: A few small admin improvements for WP 4.7 compatibility
* Updated: Data package upgraded to the latest version

= 2.2 =
* Modified the linked CSS fonts to work with or without https (thanks Spec1al1st!)
* Added minified versions of all CSS and JS files (thanks for the idea Spec1a1st!)
* Added a configuration setting for turning minified files on and off

= 2.1 =
* Added the "mass" filter to the list shortcodes to limit the list by ship mass
* Added the "ships" filter to the list shortcodes to display specific ships by name or id
* Fixed an error when importing an EFT XML file
* Made the EFT fitting visibility toggle-able in the list by double-clicking the icon
* Improved the internal handling of Ajax calls for future features

= 2.0 =
* Modified the bundled data files to be smaller
* Fixed the memory issues by switching to custom database tables
* Added an update checker and updater for the data files
* Added support for ammo in EFT fittings
* Fixed a number of bugs
* Fixed options being set twice in the db
* Added a ship detail view in the database reference screen

= 1.11 =
* Fixed ship descriptions being the same in all popups
* Fixed an error message in the getTotalHitpoints ship method
* Updated data files for YC118 expansion

= 1.10 =
* Fixed some layout issues in the themes when not using the screenshots gallery
* Removed the hardwired dialog sizing to allow more control in themes
* Now calculating the estimated required memory to read the data files into memory to prevent out of memory errors
* Added a memory check in the dashboard
* Not suppressing any errors anymore to allow them to be displayed in WP_DEBUG mode for easier troubleshooting

= 1.9 = 
* Fixed an encoding bug that could cause popups for some ships not to work
* Fixed some consistency issues in the ship attributes API
* Fixed the screenshots gallery being deleted on each update of the plugin: The gallery must now be stored in the wp-content/uploads folder.
* Added support for theme substyles
* Added a new custom designed dark theme with color substyles

= 1.8 = 
* Updated data files for Parallax expansion
* Added the new Discovery mining frigate (called the Endurance ingame, not yet seeded)

= 1.7 =
* Updated data files for Vanguard expansion
* Added support for theme switching, currently with a light and a dark theme

= 1.6 =
* Updated data files for Galatea expansion
* Added the possibility to add EFT fittings manually, as well as to edit them
* Fixed the fittings list filter not allowing listing private fits only
* Added a default z-index for the shipinfo popups
* Now includes a ship modules database for a more intelligent fittings handling
* Revamped some EFT fitting internals to make the system more stable
* Fixed a number of EFT import issues
* Placed all the groundwork for upcoming frontend updates

= 1.5 =
* Updated data files for Carnyx expansion
* Added the EFT fittings management

= 1.4 = 
* Updated data files for Rhea with new ships

= 1.3 =
* Added more filtering options to the ships collection filtering API (Cargo bay size, Drone bandwidth, Drone bay size, Piloteable, Tech level, Turret slots, Launcher slots)
* Added the new filtering options to the list and gallery shortcodes
* Added more list columns
* Improved ordering, added secondary property ordering 
* Improved the layout of the shortcode help pages with collapsible boxes
* Added missing default values to the shortcodes reference
* Made attribute type labels more meaningful in the shortcodes reference
* Checked WordPress 4.1 compatibility
* Reordered readme.txt somewhat for a better description
* Fixed a few localizeable strings
* Fixed the launcher slots in the ship info popup

= 1.2 =
* Updated data files for Phoebe with the new ship skin variants

= 1.1 =
* Made the screenshots gallery optional
* Removed reference to wp-load

= 1.0 = 
* Initial release