=== WordPress <--> GitHub Sync ===
Contributors: BenBalter, JamesDiGioia  
Tags: github, git, version control, content, collaboration, publishing  
Requires at least: 3.8  
Tested up to: 4.1  
Stable tag: 1.0.0  
License: GPLv2  
License URI: http://www.gnu.org/licenses/gpl-2.0.html  

== Description ==

*A WordPress plugin to sync content with a GitHub repository (or Jekyll site)*

[![Build Status](https://travis-ci.org/benbalter/wordpress-github-sync.svg)](https://travis-ci.org/benbalter/wordpress-github-sync)

Ever wish you could collaboratively author content for your WordPress site (or expose change history publicly and accept pull requests from your readers)?

Looking to tinker with Jekyll, but wish you could use WordPress's best-of-breed web editing interface instead of Atom? (gasp!)

Well, now you can! Introducing [WordPress <--> GitHub Sync](https://github.com/benbalter/wordpress-github-sync)!

= WordPress <--> GitHub Sync does three things: =

1. Allows content publishers to version their content in GitHub, exposing "who made what change when" to readers

2. Allows readers to submit proposed improvements to WordPress-served content via GitHub's Pull Request model

3. Allows non-technical writers to draft and edit a Jekyll site in WordPress's best-of-breed editing interface

= WordPress <--> GitHub sync might be able to do some other cool things: =

* Allow teams to collaboratively write and edit posts using GitHub (e.g., pull requests, issues, comments)

* Allow you to sync the content of two different WordPress installations via GitHub

* Allow you to stage and preview content before "deploying" to your production server

= How it works =

The sync action is based on two hooks:

1. A per-post sync fired in response to WordPress's `save_post` hook which pushes content to GitHub

2. A sync of all changed files trigged by GitHub's `push` webhook (outbound API call)

== Installation ==

= Using the WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'WordPress GitHub Sync'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Download `wordpress-github-sync.zip` from the WordPress plugins repository.
2. Navigate to the 'Add New' in the plugins dashboard
3. Navigate to the 'Upload' area
4. Select `wordpress-github-sync.zip` from your computer
5. Click 'Install Now'
6. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `wordpress-github-sync.zip`
2. Extract the `wordpress-github-sync` directory to your computer
3. Upload the `wordpress-github-sync` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

= Installing from Source =

Install the plugin and activate it via WordPress's plugin settings page.

  1. `cd wp-content/plugins`
  2. `git clone https://github.com/benbalter/wordpress-github-sync.git`
  3. `cd wordpress-github-sync && composer install`
  4. Activate the plugin in Wordpress' Dashboard > Plugins > Installed Plugins

= Configuring the plugin =

1. [Create a personal oauth token](https://github.com/settings/tokens/new) with the `public_repo` scope. If you'd prefer not to use your account, you can create another GitHub account for this. 
2. Configure your GitHub host, repository, secret (defined in the next step),  and OAuth Token on the WordPress <--> GitHub sync settings page within WordPress's administrative interface. Make sure the repository has an initial commit or the export will fail.
3. Create a WebHook within your repository with the provided callback URL and callback secret, using `application/json` as the content type. To set up a webhook on GitHub, head over to the **Settings** page of your repository, and click on **Webhooks & services**. After that, click on **Add webhook**.
4. Click `Export to GitHub` or if you use WP-CLI, run `wp wpghs export all ===` from the command line, where === = the user ID you'd like to commit as.

== Frequently Asked Questions ==

= Markdown Support =

WordPress <--> GitHub Sync exports all posts as `.md` files for better display on GitHub, but all content is exported and imported as its original HTML. To enable writing, importing, and exporting in Markdown, please install and enable [WP-Markdown](https://wordpress.org/plugins/wp-markdown/), and WordPress <--> GitHub Sync will use it to convert your posts to and from Markdown.

You can also activate the Markdown module from [Jetpack](https://wordpress.org/plugins/jetpack/) or the standalone [JP Markdown](https://wordpress.org/plugins/jetpack-markdown/) to save in Markdown and export that version to GitHub.

= Custom Post Type & Status Support =

By default, WordPress <--> GitHub Sync only exports published posts and pages. If you want to export additional post types or draft posts, you'll have to hook into the filters `wpghs_whitelisted_post_types` or `wpghs_whitelisted_post_statuses` respectively.

In `wp-content`, create or open the `mu-plugins` folder and create a plugin file there called `wpghs-custom-filters.php`. In it, paste and modify the below code:

```php
<?php
/**
 * Plugin Name:  WordPress-GitHub Sync Custom Filters
 * Plugin URI:   https://github.com/benbalter/wordpress-github-sync
 * Description:  Adds support for custom post types and statuses
 * Version:      1.0.0
 * Author:       James DiGioia
 * Author URI:   https://jamesdigioia.com/
 * License:      GPL2
 */

add_filter('wpghs_whitelisted_post_types', function ($supported_post_types) {
  return array_merge($supported_post_types, array(
    // add your custom post types here
    'gistpen'
  ));
});

add_filter('wpghs_whitelisted_post_statuses', function ($supported_post_statuses) {
  return array_merge($supported_post_statuses, array(
    // additional statuses available: https://codex.wordpress.org/Post_Status
    'draft'
  ));
});
```

= Additional Customizations =

There are a number of other filters available in WordPress <--> GitHub Sync for customizing various parts of the export, including the commit message and YAML front-matter. Want more detail? Check out the [wiki](https://github.com/benbalter/wordpress-github-sync/wiki).

We're also working on making it possible to import new posts from GitHub into WordPress, but this is **not yet supported**. For now, please create your posts in WordPress initially then edit the exported file to keep it in sync.

= Contributing =

Found a bug? Want to take a stab at [one of the open issues](https://github.com/benbalter/wordpress-github-sync/issues)? We'd love your help!

See [the contributing documentation](CONTRIBUTING.md) for details.

= Prior Art =

* [WordPress Post Forking](https://github.com/post-forking/post-forking)
* [WordPress to Jekyll exporter](https://github.com/benbalter/wordpress-to-jekyll-exporter)
* [Writing in public, syncing with GitHub](https://konklone.com/post/writing-in-public-syncing-with-github)

== Changelog ==

This change log follows the [Keep a Changelog standards](http://keepachangelog.com/). Versions follows [Semantic Versioning](http://semver.org/).

= [1.0.0] =

* Initial release
* Supports full site sync, Markdown import/export, and custom post type & status support

  [1.0.0]: https://github.com/benbalter/wordpress-github-sync/releases/tag/1.0.0
