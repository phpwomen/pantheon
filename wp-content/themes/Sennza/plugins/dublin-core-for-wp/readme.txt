=== Dublin Core for WP ===
Contributors: phyzome, jjunyent
Tags: metadata, Dublin Core, automatic
Requires at least: 2.1
Tested up to: 3.0
Stable tag: 0.7.2

Implements some Dublin Core metadata elements for WordPress posts.

== Description ==

Adds the following Dublin Core metadata elements to posts and pages:

* Site name as DC.publisher
* Site URL as DC.publisher.url
* Post title as DC.title
* Permalink as DC.identifier
* Date created as DC.date.created
* Author (last name, first name) as DC.creator.name
* Categories and tags as DC.subject (repeated <meta> element, excludes default category)
* If WordPress 2.1 or higher, DC.language
* If license is set, DC.rights.license
* Author name as DC.rights.rightsHolder

An admin screen under Settings->DC4WP allows the user to set a global content license.

== Installation ==

1. Upload `DC4WP.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Assign a content license URL (if desired) under Settings->DC4WP

== Changelog ==

= 0.7.2 =
* Add check for missing tags. This prevents a bug where pages *sometimes* throw an error due to not having tags. (Working on reproducing this.)

= 0.7.1 =
* Added DC.date redundancy

= 0.7 =
* Added some redundant unqualified DC terms for Zotero compatibility

= 0.6 =
* Change DC.creator to DC.creator.name (make room for other creator info), use last-name-first format
* Add tags to the DC.subject list (finally!)

= 0.5 =
* Change DC.subject from semicolon-delimited list to repeated tag (thanks, Alex Oberhauser!)

= 0.4 =
* Defaut category excluded from DC.subject
* DC.language
* DC.rights.license and DC.rights.rightsHolder
* Admin screen allowing user to set the license (saved to DB)

= 0.3.1 =
* Reorganization and code cleanup

= 0.3 =
* Salvatore Vassallo adds DC.creator and DC.subject

= 0.2 =
* First version.


