=== Magazine73 ===
Contributors: jonathan8312
Tags: magazine, flipbook, webp, digital publishing, page flip
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 0.1.8
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create, manage, and publish digital magazines with a responsive page-flip viewer.

== Description ==

Magazine73 helps WordPress site owners publish digital magazines using WebP page images, a public magazine URL, and a configurable page-flip viewer.

= Key features =

* Magazine administration inside WordPress.
* WebP pages from the Media Library or direct uploads.
* Automatic natural sort by filename.
* First page used as the cover.
* StPageFlip-powered responsive viewer.
* Two-page desktop view and one-page mobile view.
* Fullscreen, zoom, thumbnails, and keyboard navigation.
* Progressive page loading and local reading progress.
* Optional PDF download per magazine.
* Global viewer settings with per-magazine overrides.
* Optional Elementor widget (Elementor is not required).
* English source strings with Spanish translation included.

= Privacy =

Magazine73 does not collect telemetry or personal data. Reading progress is stored locally in the browser when available.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/magazine73`, or install the ZIP through the WordPress Plugins screen.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Open **Magazines** in the admin menu to create your first magazine.

== Frequently Asked Questions ==

= Does Magazine73 convert PDF files into pages? =

No. Magazines use WebP page images. PDF files are optional downloads only.

= Does Magazine73 require Elementor? =

No. Elementor is optional. When Elementor is active, Magazine73 registers a viewer widget. You can also embed magazines with the `[magazine73]` shortcode on any page.

= Are Media Library files deleted on uninstall? =

No. Uninstall cleanup removes Magazine73 posts, metadata, and plugin settings only when the **Delete plugin data on uninstall** option is enabled.

== Screenshots ==

1. Magazine editor with page management and viewer settings.
2. Public magazine viewer with page-flip controls.

== Changelog ==

= 0.1.8 =
* Add separate viewer colors for action icons, page counter, and button hover states.
* Improve Elementor widget color handling, global color resolution, and theme preset fallbacks.
* Fix Elementor editor preview initialization and asset cache busting after builds.

= 0.1.7 =
* Add optional Elementor viewer widget and shortcode color overrides.
* Fix Elementor editor preview so the page-flip viewer initializes correctly.

= 0.1.6 =
* Add WordPress color pickers for viewer admin colors.

= 0.1.5 =
* Prevent publishing magazines that do not have at least one WebP page.

= 0.1.4 =
* Restore Magazines → Settings access for administrators on upgraded installs.

= 0.1.3 =
* Fix header.php deprecation notice on block themes for the public magazine page.

= 0.1.2 =
* Fix fatal error on the public magazine page caused by unqualified class names in the template.

= 0.1.1 =
* Fix admin Media Library button and broken admin stylesheet URLs.

= 0.1.0 =
* Initial public MVP release.

== Upgrade Notice ==

= 0.1.8 =
Adds granular viewer color controls, Elementor style improvements, and fixes preview asset caching.

= 0.1.7 =
Adds an optional Elementor widget and fixes the Elementor editor preview for the viewer.

= 0.1.6 =
Adds WordPress color pickers for viewer admin colors.

= 0.1.5 =
Blocks publishing magazines without pages.

= 0.1.4 =
Restores Magazines → Settings for administrators after plugin upgrades.

= 0.1.3 =
Removes the block-theme header.php deprecation on public magazine pages.

= 0.1.2 =
Fixes the public magazine page fatal error.

= 0.1.1 =
Restores Add or Upload Pages in the magazine editor.

= 0.1.0 =
Initial MVP release of Magazine73.
