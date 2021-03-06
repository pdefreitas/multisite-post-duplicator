=== Multisite Post Duplicator ===

Contributors: MagicStick, sergiambel
Tags: multisite, multi site, duplicate, copy, post, page, meta, individual, clone
Requires at least: 3.0.1
Tested up to: 4.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Duplicate any individual page, post or custom post type from one site on your multisite network to another.

== Description ==

Duplicate any individual page, post or custom post type from one site on your multisite network to another.

Features

*   Copies all custom fields
*   Copies all related post meta
*   Includes any custom post type on your network as long as the post type exists in your destination site
*   Automatically copy your post/page/custom post type from one site to another from within your workflow 
*   Copies any featured image (Can be turned on or off in Settings)
*   Copies all image media within post content to the new site's media library for exclusive use in the destination site (Can be turned on or off in Settings)
*   Copies associated tags (Can be turned on or off in Settings)
*	Batch Duplication
*	Settings page to customise the default behaviour
*	Restrict functionality to only certain sites on your network
*	Restrict functionality to users of certain roles
*   Clean and friendly User Interface
*   Select what status you want your new copy of post to be i.e Published, Draft etc
*   Specify a prefix for the new post to avoid confusion
*   Works with Contact Form 7
*   Works with Advanced Custom Fields
*	Create your own addons! Multisite Post Duplicator is now fully extendable. Create your own functionality. Check out the API [documentation](http://www.wpmaz.uk/mpddocs/). Check out a list of hooks you can use (http://www.wpmaz.uk/multisite-post-duplicator-actions-and-filters/).

== Installation ==

1. Upload `multisite-post-duplicator` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. In WordPress admin on any of your sites within your multisite network go to Tools > Multisite Post Duplicator
4. Customise the default behaviour by going to Setting > Multisite Post Duplicator Settings

== Frequently Asked Questions ==

= Does this only work on a multisite network? =

Yes.

= Can I copy custom post types? =

Yes.

= What about post meta? Will these be copied over? =

Yes.

= Does this work with advanced custom fields? =

Yes. However, you have to have the same custom fields defined in each of the sites on your network. To help keep the fields in sync try: https://wordpress.org/plugins/acf-multisite-sync/

== Screenshots ==

1. User Interface
2. Meta Box
3. Setting Page
4. Batch Duplication

== Changelog ==

= 0.7.1 =
* NEW: Restrict access to this plugin's functionality to users with certain roles.

= 0.6.1 =
* Fixed issue with excerpts not copying
* Improved access to 'mpd_show_settings_page' filter

= 0.6 =
* NEW: Create your own addons! Multisite Post Duplicator is now fully extendable. Create your own functionality. Check out the API [documentation](http://www.wpmaz.uk/mpddocs/). And here is a list of hooks you can use: (http://www.wpmaz.uk/multisite-post-duplicator-actions-and-filters/). We've even created a couple of core addons if you want have nosey at how they are hooked in.
* NEW CORE ADDON: Batch Duplication! You can now duplicate several pages at a time from the post/page list screen.
* NEW CORE ADDON: Restrict MPD! You can now restrict the ability to duplicate from certain sites on your network.
* FIXED: Issue with generated destination URL of attached media from root site.

= 0.5.2 =
* Fixed issues with attached media files when duplicating a page that has aleady been duplicated
* Fixed issues with featured images when duplicating a page that has aleady been duplicated

= 0.5.1 =
* Fixed site path showing incorrectly in new image url (cosmetic change to avoid confusion, both resolve to same location)

= 0.5 =
* NEW: Now copies featured images from posts (can be turned off in Settings page)
* NEW: Now copies any image media within the post content to the destination site (can be turned off in Settings page).
* NEW: Now copies post tags (can be turned off in Settings page)
* NEW: Admin notice on success of duplication. Has a link to go straight to the new post
* NEW: Submit button on WordPress post edit page will now update to show that a duplication has been requested
* FIXED: Fixed User prefix having unintentional double space in some scenarios
* Settings page now global for all sites in the multisite network
* Cleaned up Settings page and added tooltips
* Prepared plugin for localisation (any translators welcome to contact me please!)
* Cleaned code for improved efficiency

= 0.4.1 =
* Now handles custom post statuses.

= 0.4 =
* Added Meta Box within the post type so you can now duplicate your post to another site on your network as you work (Thanks to Sergi Ambel!).
* Added Settings page to customise default behaviour.

= 0.3.1 =
* Fixed Activation errors.

= 0.3 =
* You can now filter by 'any' post type.

= 0.2.1 =
* Fixed bug where all posts were not appearing in certain scenarios

= 0.2 =
* Added support for Contact Form 7
* Duplication now correctly deals with serialised post meta

= 0.1 =
* Initial Release

== Upgrade Notice ==

= 0.1 =
Initial release
