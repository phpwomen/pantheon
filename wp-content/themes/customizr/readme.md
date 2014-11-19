![Customizr - Free Wordpress Theme - v3.2.0](/screenshot.png) 

*Enjoy designing a beautiful website live with the WP customizer : 17 color skins, logo upload, social profiles, sliders, layout choices, home featured blocks, or even live css styling. The flat and fully responsive design can be used for small businesses, portfolios, blogs, corporate sites or landing pages. Built with valid HTML5 and CSS3 (from the Twitter Bootstrap), cross-browser tested, the theme is translation ready and available in 23 languages. Ready for WooCommerce, bbPress, qTranslate, the code is easily extensible with a comprehensive API of hooks.*

# Copyright
**Customizr** is a free WordPress theme designed by Nicolas Guillaume in Nice, France. ([website : Themes and Co](http://www.themesandco.com>)) 
Feel free to use, modify and redistribute this theme as you like.
You may remove any copyright references (unless required by third party components) and crediting is not necessary, but very appreciated... ;-D. 
Customizr is distributed under the terms of the GNU GPL v2.0 or later


# Documentation and FAQs
* DOCUMENTATION : http://themesandco.com/customizr
* FAQs : http://themesandco.com/customizr/faq
* SNIPPETS : http://themesandco.com/code-snippets/
* HOOKS API : http://www.themesandco.com/customizr/hooks-api/


# Licenses
Unless otherwise specified, all the theme files, scripts and images
are licensed under GNU General Public License version 2, see file license.txt.
The exceptions to this license are as follows:
* Bootstrap by Twitter and the Glyphicon set are licensed under the GPL-compatible [http://www.apache.org/licenses/LICENSE-2.0 Apache License v2.0]
* bootstrap-carousel.js v2.3.0 is licensed under the Apache License
* holder.js v1.9 is licensed under the Apache License
* modernizr.js is dual licensed under the BSD and MIT licenses
* jquery.iphonecheck.js is copyrighted by Thomas Reynolds, licensed GPL & MIT
* jquery.fancybox-1.3.4.js is dual licensed under the MIT and GPL licenses
* retina.min.js is copyrighted by Imulus, LLC, Ben Atkin, and other contributors and licensed under MIT
* iCheck v1.0.1 by Damir Sultanov, http://git.io/arlzeA, MIT Licensed
* selecter v3.0.9 - 2014-02-10, Copyright 2014 Ben Plum, MIT Licensed
* stepper v3.0.5 - 2014-02-06, Copyright 2014 Ben Plum, MIT Licensed
* Hammer.JS - v2.0.4 - Copyright (c) 2014 Jorik Tangelder, MIT license
* Icon Set:	Entypo is licensed under SIL Open-Font License
* The image phare.jpg is a free public picture from Wikimedia, copyright 2013 Alf van Beem (http://commons.wikimedia.org/wiki/File:Ca_1941_DAF_%27Rijdende_regenjas%27_pic7.JPG) , and distributed under the terms of the Creative Commons CC0 1.0 Universal Public Domain Dedication (http://creativecommons.org/publicdomain/zero/1.0/deed.en)
* The image chevrolet.jpg is a free public picture from Wikimedia, copyright 2013 Alf van Beem (http://commons.wikimedia.org/wiki/File:%2755_Chevrolet_ornament.JPG) , and distributed under the terms of the Creative Commons CC0 1.0 Universal Public Domain Dedication (http://creativecommons.org/publicdomain/zero/1.0/deed.en)
* The image customizr.jpg is a creation of Nicolas Guillaume licensed under GPL v2+.
* The image slider-loader.gif is released under the WTFPL license (http://www.wtfpl.net/, GPL compatible), generated on http://ajaxload.info/.


# Changelog
= 3.2.5 November 15th 2014 =
* added (lang) Thai language (th), thanks to <a href="http://new.forest.go.th" target="_blank">Wee Sritippho</a>
* updated (lang) French translation
* improved (grunt) skin generation
* updated (css) rtl skins
* updated (css) set outline : none for a.tc-carousel-control. Fixes a visual bug reported on Firefox.

= 3.2.4 =
* added customizer : new header z-index option
* fixed Logo centered link bug fix. Added a clear both property to .navbar-wrapper
* fixed menu on tablets landscape, if menu was set to "open on hover", submenus could not be revealed. Fixed by forcing the click behaviour if wp_is_mobile()
* improved  front scripts concatenation boolean filter : 'tc_load_concatenated_front_scripts' default to true. js files can be loaded loaded separetely in dev mode Load bootstrap param not used anymore
* improved customizer sections for wp<4.0 : set conditional priorities ( based on is_wp_version_before_4_0) to reoder the section more consistently skin, header, content, footer....
* fixed Customizer frozen bug. case default control : falls back to no input attr if wp version < 4.0 because input_attrs() was introduced in 4.0
* improved customizer panels : remove useless check if wp version >= 4.0 new private property : is_wp_version_before_4_0
* added Grunt : dev mode, customizer control script is a concatenation of libraries and _control.js
* added Grunt : in dev mode, tc-scripts is a concatenation of main.js + params-dev-mode.js + fancybox + bootstrap
* added Livereload script loaded on dev mode TC_DEV constant is true added in customize_controls_print_scripts when customizing and in wp_head when live
* added Grunt : ftp push enabled for all files Grunt : tc-scripts.min.js concatenates params-dev-mode.js, bootstrap.js, jquery.fancybox-1.3.4.min.js, tc-scripts.js Grunt : tc-script.js jshint @to finish
* fixed menu : 'tc-submenu-fade' is applied if option 'tc_menu_submenu_fade_effect' is true AND ! wp_is_mobile()
* fixed TCparams (localized params) was not defined as a js var
* updated lang : pl_PL, thanks to Marcin Paweł Sadowski
* updated lang : de_DE , thanks to Martin Bangemann


= 3.2.3 November 5th 2014 =
* fixed (php, class-header-header_main.php) remove space after filter declaration for tc_tagline_text
* added (php, class-content-post_list.php) new boolean filter tc_show_post_in_post_list + condition on $post global variable
* added (php, class-fire-admin_page.php) New action hooks__system_config_before, __system_config_after
* fixed (php, class-content-featured_pages.php, class-content-post_thumbnails.php, class-header-header_main.php) JetPack photon bug fixed on the wp_get_attachment_image_src() return value array
* changed (php, class-header-header_main.php) New method : tc_prepare_logo_title_display() hooked on '__header' in place of tc_logo_title_display(), fires 2 new methods tc_logo_view() and tc_title_view()
* fixed (php, class-header-header_main.php) in tc_prepare_logo_title_display() the logo filetype is now checked with a custom function TC_utils::tc_check_filetype(), instead of wp_check_filetype(). This new method checks the filetype on the whole string instead of at the very end of it => fixes the JetPack photon bug for logo
* added (php, class-fire-utils) tc_check_filetype() method
* added (php, class-content-post_thumbnails.php) new filter named tc_thumbnail_link_class => array of css classes
* removed (php, class-content-post_thumbnails.php) 'tc_no_round_thumb' filter, now handled by the 'tc_thumbnail_link_class'  filter
* added (php, class-content-post_thumbnails.php) new filter 'tc_post_thumbnail_img_attributes'
* improved (php, class-content-post_thumbnails.php ) better handling of dynamic inline style for thumbnails img with height || width < to default thumbnails dimensions
* improved (php) get_the_title() has been replaced by esc_attr( strip_tags( get_the_title() ) ) when used as title attribute
* improved (css) set a high z-index (10000) to header.tc-header
* improved (js, tc-script.js) localized params (TCParams) falls back to a default object if they are not loaded (=> typically happens whith a misconfigured cache plugin with combined js files)
* improved (css,php:class-fire-resources.php) font icons have been extracted from the skin stylesheet and are now inlining early in head. New filters : 'tc_font_icon_priority' (default = 0 ), tc_font_icons_path (default : TC_BASE_URL . 'inc/assets/css'), 'tc_inline_font_icons' (default = html string of the inline style)
* improved (js, php:class-fire-resources.php) when debug mode enabled : tc-script.js is loaded not minified. Boostrap is loaded separately and not minified
* added (js:bootstrap.js, php:class-fire-utils_settings_map.php,class-fire-resources.php) new checkbox option in the customizer 'tc_menu_resp_dropdown_limit_to_viewport'.In responsive mode, users can now choose whether the dropdown menu has to be fully deployed or limited to the viewport's height.
* updated (lang) nl_NL : thanks to Joris Dutmer
* added (php:class-fire-utils_settings_map.php) New checkbox option in the customizer 'tc_sticky_transparent_on_scroll' => allow user to disable the semi-transparency of the sticky header on scroll. Default => Enabled (true)
* added (php:class-content-comments.php) New filter 'tc_list_comments_args'. Default value = array( 'callback' => array ( $this , 'tc_comment_callback' ) , 'style' => 'ul' )
* added (php:class-fire-init.php) Added add_theme_support( 'title-tag' ) recommended way for themes to display titles as of WP4.1. source : https://make.wordpress.org/core/2014/10/29/title-tags-in-4-1/
* fixed (css) Bug : since v3.2.1 upgrade, left sidebar was not displayed under 980px https://wordpress.org/support/topic/left-sidebar-disappeared-in-responsive-design-after-todays-upgrade?replies=3
* fixed (lang, php:class-content-comments.php) plural translation string wrapped in _n() where not translated
* improved (js) In customizing mode, jQuery plugins icheck, stepper, selecter are loaded only when necessary. For example : 'function' != typeof(jQuery.fn.stepper) => avoir double loading if a plugin already uses this $ module.
* improved (js, theme-customizr-control.js) icheck : init only if necessary (  0 == $(this).closest('div[class^="icheckbox"]').length )=> beacause it can have been already initiated by a plugin.
* improved (css, class-fire-admin_init.php) admincss handle for enqueuing has been prefixed with tc-, like all other resources of the theme
* improved (css, tc_admin.css) Now minified
* fixed (php, class-fire-utils.php) bbPress compatibility issue. Was generating a notice bbp_setup_current_user was called incorrectly. The current user is being initialized without using $wp->init(). This was due to the tc_get_default_options(), using is_user_logged_in(), called too early. Now hooked in "after_setup_theme" and compatible with bbPress
* updated (lang) es_ES : thanks to María Digo
* improved (js, tc-script.js) Smooth Scrolling option : to avoid potential conflicts with plugins using the 'click' event on anchor's links, the scope of targeted links has been limited to the the #content wrapper : $('a[href^="#"]', '#content')
* fixed (css) Back to top arrow : Better backgroundstyle for ie9+
* fixed (css) ie9- Support : fixed tagline displayed twice issue
* fixed (css) .social-block is displayed and centered for @media (max-width: 320px)
* updated(css) blue3.css is now the default skin, color #27CDA5
* fixed (php, class-fire-init.php) Better handling of the retina mode. the original file is now generated in high definition @x2
* updated : the default slider images have been re-designed and their @x2 version (for high definitation devices) has been added in inc/assets/img
* updated : screenshot of the theme


= 3.2.2 October 30th 2014 =
* fixed (js, tc-script.js) the 'touchstart' event don't trigger the responsive menu toggle anymore => was generating a major bug on responsive devices reported here : https://wordpress.org/support/topic/321-responsive-menu-wont-stay-open?replies=18, and here : https://wordpress.org/support/topic/bug-report-44?replies=4
* added (php, class-fire-admin_page.php) New hooks in admin : '__before_welcome_panel' '__after_welcome_panel
* added (php) new class TC_admin_page handling the welcome panel including the changelog and user system infos
* updated (lang) ru_RU : thanks to <a href="http://bootwalksnews.com/" target="_blank">Evgeny Sudakov</a>
* updated (lang) es_ES : thanks to María Digo
* updated (lang) zh_CN : thanks to Luckie Joy
* updated (lang) hu_HU : thanks to Ferencz Székely
* updated (lang) ca_ES : thanks to Jaume Albaigès
* updated (lang) sk_SK : thanks to <a href="http://www.pcipservis.eu/" target="_blank">Tomáš Lojek</a>
* updated (lang) de_DE : thanks to <a href="http://foerde-mentor.de" target="_blank">Bernd Troba</a>


= 3.2.1 October 20th 2014 =
* fixed (css) Featured pages recentering for max-width 979px
* fixed (css) Sticky header menu background
* improved (js, tc-scripts.js) Scroll event timer only for ie


= 3.2.0 October 20th 2014 =
* added (php, class-content-slider.php) New action hooked : __after_carousel_inner. Used to render the slider controls.
* added (js) slider swipe support with hammer.js. Controls not renderd for mobile devices.
* fixed (php, class-content-comments.php, comments.php) Comment title was not included in the translation strings (out of the poedit source paths). New filter on comment_form_defaults filter 
* added (css, php : class-fire-init.php) css : class 'is-customizing' is added to the body tag in a customization context
* changed (css) transition: width 0.2s ease-in-out, left 0.25s ease-in-out, right 0.25s ease-in-out; is only applied in a customization context.
* changed : (php, class-header-header_main.php) tc_logo_class filter is now handled as an array of css classes instead of a string : implode( " ", apply_filters( 'tc_logo_class', array( 'brand', 'span3') ) )
* added : (php, class-fire-utils.php, class-header-header_main.php) Navbar new customizer option tc_header_layout
* added : (php, class-fire-utils.php, class-header-header_main.php) Navbar new customizer option tc_display_boxed_navbar
* added : (php, class-fire-utils.php, class-header-header_main.php) Tagline ew customizer option tc_show_tagline
* added : (php, class-fire-utils.php, class-content-post4
4141.p14hp) Single post view : new filter tc_single_post_thumbnail_view
* added : (php, class-content-post_thumbnail.php) new class dedicated to the thumbnail view and control : TC_post_thumbnails
* changed : (php, class-content-post_thumbnails.php) thumbnails : filter name tc_post_list_thumbnail changed to tc_display_post_thumbnail. tc_get_post_list_thumbnail changed to tc_get_thumbnail_data
* added : (php, class-fire-utils.php, class-content-post.php) Thumbnails : new option in the customizer tc_single_post_show_thumb
* added : (php, class-content-post_list.php) New filter : tc_attachment_as_thumb_query_args.
* added : (php, class-fire-utils.php, class-content-post_list.php) Thumbnails : new option in the customizer tc_post_list_show_thumb, tc_post_list_use_attachment_as_thumb, tc_post_list_thumb_shape, tc_post_list_thumb_height, tc_post_list_thumb_position, tc_post_list_thumb_alternate
* added : (php, class-fire-utils.php, class-content-footer_main.php) Back to top link : new option in the customizer tc_show_back_to_top
* added : (php, class-fire-utils.php, class-fire-init.php ) Links : new option in the customizer tc_link_hover_effect.
* added : (php, class-content-post_list.php) New filter : tc_thumb_size_name. Default value : 'tc-thumb'
* added : (php, class-fire-utils_settings_map.php) Creation of class-fire-utils_settings_map.php for the customizer settings. Instanciated before TC_utils().
* added : (php, class-content-post_metas.php, class-fire-utils.php ) Post metas : 3 new options in the customizer : tc_show_post_metas_home, tc_show_post_metas_single_post, tc_show_post_metas_post_lists. View implemented with a new callback : add_action( 'template_redirect', array( $this , 'tc_set_post_metas' ));
* added : (php, class-content-headings.php, class-fire-utils.php ) Icons in title : new options in the customizer : tc_show_page_title_icon, tc_show_post_title_icon, tc_show_archive_title_icon, tc_show_post_list_title_icon, tc_show_sidebar_widget_icon, tc_show_footer_widget_icon. View implemented with 2 new callbacks  : add_filter ( 'tc_content_title_icon' , array( $this , 'tc_set_post_page_icon' )), add_filter ( 'tc_archive_icon', array( $this , 'tc_set_archive_icon' ))
* added : (php, class-content-breadcrumb.php, class-fire-utils.php ) Breadcrumb : 4 new optionw in the customizer : tc_show_breadcrumb_home, tc_show_breadcrumb_in_pages, tc_show_breadcrumb_in_single_posts, tc_show_breadcrumb_in_post_lists. Implemented with a new filter and callback :  add_filter( 'tc_show_breadcrumb_in_context' 	, array( $this , 'tc_set_breadcrumb_display_in_context' ) )
* added : (lang) Hebrew (he_IL) translation added. Thanks to <a href="http://www.glezer.co.il/">Yaacov Glezer</a>.
* updated : (lang) Russian translation, thanks to <a href="http://webmotya.com/">Evgeny</a>.
* added : (php, class-content-slider.php) new hooks before and after each slides : __before_all_slides, __after_all_slides, __before_slide_{$id}, __after_slide_{$id}
* added : (php, class-content-sidebar.php) new hook for the social links title : tc_sidebar_socials_title
* improvement : (php, class-header_main.php) remove getimagesize() responsible for many bug reports. The logo width and height are now get directly from the WP attachement object which is way more reliable. New filters : 'tc_logo_attachment_img', 'tc_fav_attachment_img'. Backward compatibility is ensured by testing if the option is numeric (id) and falls back to the path src type if not.
* improvement : (php, class-fire-utils.php) logo and favicon upload options are now handled with a specific type of control tc_upload, which has its own rendering class (extension of WP_Customize_Control)
* improvement : (js, theme-customizer-control.js) new constructor added to wp.customize object. Inspired from the WP built-in UploadControl constructor. It uses the id instead of the url attribute of the attachement backbone model.
* fixed : (css) replaced .tc-hover-menu.nav.nav li:hover > ul by .tc-hover-menu.nav li:hover > ul
* improved (css) footer top border changed to 12px to 10px, same as header bottom border
* improved (js, bootstrap) for mobile viewports, apply max-height = viewport to the revealed submenus+ make it scrollable
* improved (php, class-content-post_list.php) round thumb : if size is not set for media, then falls back to medium and force max-width and max-height.


= 3.1.24 Septembre 21th 2014 =
* fixed : (php, class-fire-init.php#393 ) check if defined( 'WPLANG'). WPLANG has to be defined in wp-config.php, but it might not be defined sometimes.
* fixed : (php, class-content-slider.php) the slider loader block has been taken out of the carousel inner wrapper. Fixes the issue reported here : http://www.themesandco.com/customizr-theme-v3-1-23-tested-wordpress-v4-0/#li-comment-235017. The slider loader is diplayed by default for the demo slider.
* added : (php, class-fire-init.php) new option in Customizer > Images => checkbox to display a gif loader on slides setup. Default == false.
* added : (php, class-content-post_navigation.php) 4 new filters to get control on all the options of the single and archive post navigation links : tc_previous_single_post_link_args, tc_next_single_post_link_args, tc_next_posts_link_args, tc_previous_posts_link_args
* improved : (php, class-fire-utils.php#315 ) cleaner code for the fancybox filter on 'the_content'
* improved : (php, class-fire-ressources.php) performance : holder.min.js is now loaded when featured pages are enabled AND FP are set to show images


= 3.1.23 Septembre 6th 2014 =
* improved : (php, class-fire-ressources.php, js : tc-scripts.js ) Performances : tc-scripts.js now includes all front end scripts in one file. 1) Twitter Bootstrap scripts, 2) Holder.js , 3) FancyBox - jQuery Plugin, 4) Retina.js, 5) Customizr scripts. New boolean filters to control each scripts load : tc_load_bootstrap, tc_load_modernizr, tc_load_holderjs, tc_load_customizr_script.
* added : (php, class-footer-footer_main.php#55) 2 new action hooks before and after the footer widgets row : '__before_footer_widgets' , '__after_footer_widgets'
* added : (php, class-footer-footer_main.php#142) Colophon center block : 2 new filter hooks : tc_copyright_link, tc_credit_link
* improved : (php, class-footer-footer_main.php#55) before and after footer widgets hooks have been moved out of the active_sidebar condition in order to be used even with widget free footer
* changed : (php, class-content-breadcrumb.php#581 ) filter hook name has been changed from 'breadcrumb_trail_items' to 'tc_breadcrumb_trail_items'
* changed : (php, class-content-featured_pages.php#112) filter name changed from 'fp_holder_img' to 'tc_fp_holder_img' for namespace consistency reasons
* improved : (php, class-content-featured_pages.php) filter hooks missing parameters ( $fp_single_id and / or $featured_page_id) have been added to 'tc_fp_title', 'tc_fp_text_length', 'fp_img_src, 'tc_fp_img_size', 'tc_fp_round_div', 'tc_fp_title_tag', 'tc_fp_title_block', 'tc_fp_text_block', 'tc_fp_button_block', 'tc_fp_single_display'
* improved : (php, class-content-featured_pages.php) new holder image style. Foreground color is the main skin color.
* updated (js, holder.js) version 2.4 of the script.
* improved : (php, class-fire-init.php#386) replace the disable_for_jetpack() callback by the built-in wp function __return_false()
* added : (php : class-fire-init.php, css) 2 new social networks :  tumblr and flickr.
* added : (php : class-fire-init.php, css) new skin_color_map property
* improved : (php, class-content-post_list.php#240) use apply_filters_ref_array instead of apply_filters for some filters
* improved : (php, class-content-post_list.php#240) 'tc_get_post_list_thumbnail' filter : the current post id has been included in the array of parameters
* improved : (php, class-content-post_list.php#259) 'tc_post_thumb_img' filter : the current post id has been included in the parameters
* improved : (php, class-content-post_metas.php#189) use apply_filters_ref_array instead of apply_filters
* added : (php, class-content-post_metas.php) entry-date meta : new filter to use the modified date instead of the actual post date : 'tc_use_the_post_modified_date'. Default to false. Note : get_the_modified_date() == get_the_date() if the post has never been updated.
* improved : (php, class-content-sidebar.php#115) current_filter() added as parameter of the 'tc_social_in_sidebar' filter hook
* improved : (php, class-content-slider#193) $slider_name_id parameter added to the following filter hooks : tc_slide_background, tc_slide_title, tc_slide_text, tc_slide_color, tc_slide_link_id, tc_slide_link_url, tc_slide_button_text, tc_slide_title_tag, tc_slide_button_class
* added : (php : class-content-slider.php, js : tc-scripts.js, css) Slider : for a better experience, when the re-center option is checked in Appearance > Customizer > Responsive settings, a gif loader is displayed while recentering.
* fixed : (php, class-fire-admin_init.php#312) Changelog was not displayed in ?page=welcome.php#customizr-changelog. Now look for '= '.CUSTOMIZR_VER to match the current version changelog
* improved : (php, class-header-header_main.php#223) action hook 'before_navbar' renamed to '__before_navbar' for namespace consistency reasons
* added : (php, class-header-header_main.php) added 'tc_head_display' filter
* improved : (php, class-header-header_main.php) tc_favicon_display filter is now handled with a sprintf()
* added : (php, class-header-header_main.php) new filters tc_logo_link_title , tc_site_title_link_title
* changed : (php, class-header-header_main.php ) filter names : __max_logo_width => tc_logo_max_width and __max_logo_height => tc_logo_max_height
* changed : (php, class-header-header_menu.php#97) filter menu_wrapper_class renamed in tc_menu_wrapper_class
* changed : (php, class-header-nav_walker.php#41 ) filter menu_open_on_clicks renamed in tc_menu_open_on_click
* added : (php, comments.php) new filter : tc_comments_wrapper_class inside div#comments
* changed : (php, comments.php) filter comment_separator renamed to tc_comment_separator
* improved : (php, comments.php) cleaner code
* changed : (php, init.php#47) Class loading order. Utils are now loaded before resources.
* changed : (php, class-fire-resources.php) localized params filter renamed 'tc_customizr_script_params'. Left and Right sidebars classes are now set dynamically form the global layout params.
* changed : (php, class-fire-utils.php#497) added the $key parameter to tc_social_link_class
* improved : (php , class-fire-utils.php#207)tc_get_the_ID() : now check the wp_version global to avoid the get_post() whitout parameter issue. ( $post parameter became optional after v3.4.1 )
* added : (php, class-controls.php) 2 new action hooks : __before_setting_control, __after_setting_control, using the setting id as additional parameter.
* fixed : (css) .navbar-inner .nav li : 1px hack for chrome to not loose the focus on menu item hovering


= 3.1.22 August 16th 2014 =
* added : (css, class-fire-init.php#75) 9 new minified css skins
* fixed : (php, class-content-breadcrumb.php#443) added a check is_array(get_query_var( 'post_type' ) in archive context
(bug reported here : https://wordpress.org/support/topic/illegal-offset-type-in-isset-or-empty-in-postphp-after-upgrade-to-custom3120)
* improved : (php, class-content-headings.php#224) added a boolean filter named 'tc_display_link_for_post_titles' (default = true) to control whether the post list titles have to be a link or not


= 3.1.21 August 11th 2014 =
* fixed : (php, class-content-post_list.php) boolean filter 'tc_include_cpt_in_archives' is set to false. Following a bug reported here http://wordpress.org/support/topic/content-removedchanged-after-updating-to-3120?replies=8 Thanks to http://wordpress.org/support/profile/le-formateur for reporting it.


= 3.1.20 August 9th 2014 =
* added : (lang) Ukrainian translation. Many thanks to <a href="http://akceptor.org/">Aceptkor!</a>
* added : (php, class-content-post_list.php) new filter to control if attachment have to be included in search results or not : tc_include_attachments_in_search_results. Default : true.
* added : (php, class-content-post_list.php) Custom Post Types : new pre_get_posts action. Now includes Custom Posts Types (set to public and excluded_from_search_result = false) in archives and search results. In archives, it handles the case where a CPT has been registered and associated with an existing built-in taxonomy like category or post_tag
* added : (php, class-content-post_metas.php) Now handles any custom or built-in taxonomies associated with built-in or custom post types. Displays the taxonomy terms like post category if hierarchical, and like post tags if not hierarchical. Uses a new helper (private method) : _get_terms_of_tax_type(). New filter created : tc_exclude_taxonomies_from_metas, with default value : array('post_format') => allows to filter which taxonomy to displays in metas with a customizable granularity since it accepts 2 parameters : post type and post id.
* added : (php, class-fire-utils.php) added the social network key to the target filter : apply_filters( 'tc_socials_target', 'target=_blank', $key )
* added : (php, class-header-header_main.php) favicon and logo src are ssl compliant => fixes the "insecure content" warning in url missing 'https' in an ssl context
* added : (php, class-fire-utils.php) new placeholder image for the demo slider customizr.jpg
* added : ( php, class-content-featured_pages.php ) add edit link to featured pages titles when user is logged in and has the capabilities to do so
* improved : (php, class-content-breadcrumb.php) now displays all levels of any hierarchical taxinomies by default and for all types of post (including hierarchical CPT). This feature can be disabled with a the filter : tc_display_taxonomies_in_breadcrumb (set to true by default). In the case of hierarchical post types (like page or hierarchical CPT), the taxonomy trail is only displayed for the higher parent.
* improved : (php, class-fire-utils.php and class-controls.php) moved the slider-check control message if no slider created yet to tc_theme_options[tc_front_slider] control


= 3.1.19 July 18th 2014 =
* added : (php, class-fire-init.php) support for svg and svgz in media upload
* added : (php, class-header-header_main.php) new filter 'tc_logo_img_formats'
* fixed : (php, class-content-breadcrumb#291) check existence of rewrite['with_front']
* fixed : (php) closing tags php removed from all classes


= 3.1.18 July 11rd 2014 =
* added : (lang) Czech translation. Many thanks to Martin Filák!
* added : (php , class-content_slider.php) two new action hooks (filters) for a better control of the slider layout class (tc_slider_layout_class) and the slider image size (tc_slider_img_size)
* added : (php, class-fire-resources.php) new filter named "tc_custom_css_priority" to take control over the custom css writing priority in head
* added : (php) empty index.php added in all folders
* improved : (php) Every class is now "pluggable" and can be overriden
* improved : (php, class-content-post_list.php) the missing $layout parameter has been added to the "tc_post_list_thumbnail" filter
* improved : (php, class-content-headings.php) headings of the page for post is now displayed by default (if not front page). Action hook (filter) : tc_page_for_post_header_content
* improved : (php, class-content-sidebar.php) before and after sidebars hooks have been moved out of the active_sidebar condition in order to be used even with widget free sidebars


= 3.1.17 July 6rd 2014 =
* fixed : back to previous screenshot


= 3.1.16 July 3rd 2014 =
* improved : (php, css, js) better file structure. Every front end has been move iin /inc folder
* improved : (php) init class and functions have been moved in /inc/init.php
* improved : new theme screenshot
* fixed : (php, class-content-slider.php#102) missing icon parameter has been added to wp_get_attachment_image()


= 3.1.15 May 31st 2014 =
* fixed : (css : editor-style.css) background default color flagged as !important
* fixed : (php : class-content-headings.php) post edit button is displayed to author of the post and admin profiles Thanks to <a href="http://www.themesandco.com/author/eri_trabiccolo/">Rocco</a>
* fixed : (php : class-content-slider.php) slider edit button is displayed for users with the upload_files capability
* fixed : (php : class-content-comments.php) class comment-{id} has been added to the article comment wrapper to ensure compatibility with the recent comment WP built-in widget


= 3.1.14 =
* added : (js : theme-customizer-control.js, css : theme-customizer-control.css, php : class-admin-customize.php) Donate block can be disable forever in admin.


= 3.1.13 =
* added : (lang) Danish translation. Thanks to <a href="http://teknikalt.dk">Peter Wiwe</a>
* added : (css, js) Donate link in admin

= 3.1.12 =
* fixed : (css) category archive icon now displayed again in chrome
* fixed : (php : TC_init::tc_add_retina_support) retina bug fixed by <a href="http://wordpress.org/support/profile/electricfeet" target="_blank">electricfeet</a>
* improved : (php : TC_breadcrumb ) breadcrumb trail for single posts, category and tag archive now includes the page_for_posts rewrited if defined.
* improved : (php) Better handling of the comment reply with the add_below parameter. Thanks to <a href="http://www.themesandco.com/author/eri_trabiccolo/">Rocco</a>.
* improved : (php) TC_Utils::tc_get_option() returns false if option not set
* removed : (php) Customiz'it button has been taken off


= 3.1.11 =
* added : (php , css) customizer : new option in the Skin Settings, enable/disable the minified version of skin
* added : (php) customizer : new option in the Responsive Settings, enable/disable the automatic centering of slides
* added : (js, php) automatic centering of the slider's slides on any devices. Thanks to <a href="http://www.themesandco.com/author/eri_trabiccolo/">Rocco</a>.
* improved : (css) skins have been minified to speed up load time (~ saved 80Ko)
* improved : (php) logo and favicon are now saved as relative path => avoid server change issues.
* improved : (php) better class loading. Check the context and loads only the necessary classes.
* improved : (php) customizer map has been moved into the class-fire-utils.php
* improved : (php) performance improvement for options. Default options are now generated once from the customizer map and saved into database as default_options
* improved : (js) block repositioning is only triggered on load for responsive devices
* updated : (translation) Slovak translation has been updated. Thanks to <a href="www.pcipservis.eu">Michal Hranicky</a>.


= 3.1.10 =
* fixed : (php : TC_init::tc_plugins_compatibility() , custom-page.php) WooCommerce compatibility issue fixed.
* added : (TC_customize::tc_customize_register() , TC_resources::tc_enqueue_customizr_scripts() , tc_script.js ) New option in customizer : Enable/Disable block reordering for smartphone viewport.


= 3.1.9 =
* fixed : (js  : tc_scripts.js , php : index.php ) responsive : dynamic content block position bug fixed in tc_script.js, the wrapper had to be more specific to avoid block duplication when inserting other .row inside main content. Thanks to <a href="http://www.themesandco.com/author/eri_trabiccolo/" target="_blank">Rocco Aliberti</a>.
* fixed : (php : TC_resources::tc_enqueue_customizr_scripts() ) comment : notice on empty archives due to the function comments_open(). A test on  0 != $wp_query -> post_count has been added in TC_resources::tc_enqueue_customizr_scripts(). Thanks to <a href="http://www.themesandco.com/author/eri_trabiccolo/" target="_blank">Rocco Aliberti</a>.
* improved : (js  : tc_scripts.js) responsive : the sidebar classes are set dynamically with a js localized var using the tc_{$position}_sidebar_class filter


= 3.1.8 =
* fixed : (js) responsive : dynamic content block position bug fixed in tc_script.js


= 3.1.7 =
* fixed : (css) : icons rendering for chrome
* improved : (css) : footer white icons also for black skin
* added : (php) utils : new filter with 2 parameters to tc_get_option
* added : (php) featured pages : new filter tc_fp_id for the featured pages
* added : (php) featured pages : new parameters added to the fp_img_src filter
* improved : (php) metaboxes : no metaboxes for acf post types
* improved : (js) responsive : dynamic content block position on resize hase been improved in tc_script.js
* fixed : (php) Image size : slider full size sets to 9999 instead of 99999 => was no compatible with Google App engine
* improved : (php) slider : make it easier to target individual slides with a unique class/or id
* added : (php) footer : dynamic actions added inside the widget wrapper
* improved : (php) footer : additional parameter for the tc_widgets_footer filter
* improved : (php)(js) comments : Comment reply link the whole button is now clickable
* fixed : (html) Google Structured Data : addition of the "updated" class in entry-date


= 3.1.6 =
* added : (php)(js) customizer controls : new filter for localized params
* added : (php) featured pages : new filters for title, excerpt and button blocks
* added : (php) search : form in the header if any results are found
* improved : (php) body tag : "itemscope itemtype="http://schema.org/WebPage" included in the 'tc_body_attributes' filter hook
* improved : (php) overall code : check added on ob_end_clean()
* improved : (php) headings : new filters by conditional tags
* improved : (php) comments : 'comment_text' WP built-in filter has been added in the comment callback function
* fixed : (js) submenu opening on click problem : [data-toggle="dropdown"] links are excluded from smoothscroll function
* fixed : (php) compatibility with NEXTGEN plugin : fixed ob_start() in class-content-headings::tc_content_headings()


= 3.1.5 =
* fixed : (php) child themes bug : child theme users can now override the Customizr files with same path/name.php.


= 3.1.4 =
* fixed : (css) featured pages : another responsive thumbnails alignment for max-width: 979px


= 3.1.3 =
* fixed : (css) featured pages : responsive thumbnails alignment


= 3.1.2 =
* improved : (php) minor file change : the class__.php content has been moved in functions.php


= 3.1.1 =
* added : (language) Turkish : thanks to <a href="http://www.ahmethakanergun.com/">Ahmet Hakan Ergün</a>
* added : (css) customizer : some styling
* fixed : (css) post thumbnails : minor alignment issues
* fixed : (php) translations in customizer for dropdown lists


= 3.1.0 =
* added : (language) Hungarian : thanks to Ferencz Székely
* added : (php) Site title : filter on the tag
* added : (php) archives (categories, tags, author, dates) and search results titles can be filtered
* added : (php) posts : 2 new hooks before and after post titles. Used for post metas.
* added : (php) logo and site title : new filter for link url (allowing to change the link on a per page basis)
* added : (php) featured pages : filters for page link url and text length
* added : (php) featured pages : new filter for the button text (allowing to change the title on a per page basis)
* added : (php) slider : new filters allowing a full control of img, title, text, link, button, color
* added : (php) slider : new function to easily get slides out of a slider
* added : (php) Slider : New edit link on each slides
* added : (php) comments : filter on the boolean controlling display
* added : (php) comments : direct link from post lists to single post comments section
* added : (php) comments : new filters allowing more control on the comment bubble
* added : (php) metaboxes : filter on display priority below WYSIWYG editor
* added : (php) footer : filters on widgets area : more controls on number of widgets and classes
* added : (php) sidebars : filters on column width classes
* added : (php) content : filters on the layout
* added : (php) page : support for excerpt
* added : (js)(php)(css) Retina : customizr now supports retina devices. Uses Ben Atkin's retina.js script.
* added : (js)(php)(css) New option : Optional smooth scroll effect on anchor links in the same page
* added : (js)(php) Slider : easier control of the stop on hover
* added : (php)(css) Menu : new option to select hover/click expansion mode of submenus
* added : (css) Bootstrap : Glyphicons are now available
* added : (php) Social Networks : possibility to easily add any social networks in option with a custom icon on front end
* added : (php) Social Networks : filter allowing additional link attributes like rel="publisher" for a specific social network
* added : (php) Posts/pages headings : new filters to enable/disable icons
* added : (php) Post lists : edit link in post titles for admin and edit_posts allowed users
* added : (php)(css) Intra post pagination : better styling with buttons
* added : (php) sidebars : two sidebar templates are back. needed by some plugins
* changed : (php) Featured page : name of the text filter is now 'fp_text'
* improved : (css) Menu : style has been improved
* improved : (php) slider : controls are not displayed if only on slide.
* improved : (php) fancy box : checks if isset $post before filtering content
* improved : (css) widgets : arrow next to widget's list is only displayed for default WP widgets
* fixed : (php) blog page layout : when blog was set to a page, the specific page layout was not active anymore
* fixed : (php) menu : the tc_menu_display filter was missing a parameter
* fixed : (php) comments : removed the useless permalink for the comments link in pages and posts


= 3.0.15 =
* added : (language) Catalan : thanks to <a href="https://twitter.com/jaume_albaiges" target="_blank">Jaume Albaig&egrave;s</a>
* fixed : (js) Slider : ie7/ie8/ie9 hack (had to be re-implemented) : thanks to @barryvdh (https://github.com/twbs/bootstrap/pull/3052)


= 3.0.14 =
* added : (language) Arabic : thanks to Ramez Bdiwi
* added : (language) RTL support : thanks to Ramez Bdiwi
* added : (language) Romanian : thanks to <a href="http://websiter.ro" target="_blank">Andrei Gheorghiu</a>
* added : (php) two hooks in index.php before article => allowing to add sections
* added : (php) new customizer option : select the length of posts in lists : excerpt of full length
* added : (php) add_size_images : new filters for image sizes
* added : (php) rtl : check on WPLANG to register the appropriate skin
* added : (php) featured pages : new filter for featured pages areas
* added : (php) featured pages : new filter for featured page text
* added : (php) slider : 3 filters have been added in class-admin-meta_boxes.php to modify the text, title and button length __slide_text_length, __slide_title_length, __slide_button_length
* added : (php) logo : 2 new filters to control max width and max height values (if logo resize options is enabled) : '__max_logo_width' , '__max_logo_height'
* added : (php) body tag : a new action hook '__body_attributes'
* added : (php) header tag : new '__header_classes' filter
* added : (php) #main-wrapper : new 'tc_main_wrapper_classes' filter
* added : (php) footer : new '__footer_classes' filter
* added : (js) scrollspy from Bootstrap
* added : (js) Scrollspy : updated version from Bootstrap v3.0. handles submenu spy.
* added : (css) back to top link colored with the skin color links
* added : (css) bootstrap : alerts, thumbnails, labels-badges, progress-bars, accordion stylesheets
* added : (css) Editor style support for skins, user style.css, specific post formats and rtl.
* improved : (css) performance : Avoid AlphaImageLoader filter for IE and css minified for fancybox stylesheet
* improved : (css) (php) logo : useless h1 tag has been removed if logo img. Better rendering function with printf. Better filters of logo function. 2 new actions have been added before and after logo : '__before_logo' , '__after_logo'
* removed : (php) Post list content : removed the useless buble $style var
* removed : (css) featured pages : useless p tag wrap for fp-button removed
* removed : (php) User experience : redirection to welcome screen on activation/update
* removed : (php) Security : Embedded video, Google+, and Twitter buttons
* fixed : (php) breadcrumb class : add a check isset on the $post_type_object->rewrite['with_front'] for CPT
* fixed : (php) a check on is_archive() has been added to tc_get_the_ID() function in class fire utils
* fixed : (php) we used tc__f('__ID') instead of get_the_ID() in class-header-slider
* fixed : (php) we add a hr separator after header only for search results and archives
* fixed : (php) comments : 'tc_comment_callback' filter hook was missing parameters
* fixed : (php) featured pages : filter  'tc_fp_single_display' was missing parameters
* fixed : (css) comments avatar more responsive
* fixed : (css) ie9 and less : hack to get rid of the gradient effect => was bugging the responsive menu.


= 3.0.13 =
* fixed : (php) Logo upload : we check if the getimagesize() function generates any warning (due to restrictions of use on some servers like 1&1) before using it. Thanks to <a href="http://wordpress.org/support/profile/svematec" target="_blank">svematec</a>, <a href="http://wordpress.org/support/profile/electricfeet" target="_blank">electricfeet</a> and <a href="http://wordpress.org/support/profile/heronswalk" target="_blank">heronswalk</a> for reporting this issue so quickly!


= 3.0.12 =
* fixed : (php) the slider is now also displayed on the blog page. Thanks to <a href="http://wordpress.org/support/profile/defttester" target="_blank">defttester</a> and <a href="http://wordpress.org/support/profile/rdellconsulting" target="_blank">rdellconsulting</a>

= 3.0.11 =
* added : (php) filter to the skin choices (in customizer options class), allowing to add new skins in the drop down list
* added : (php) filter for enqueuing the styles (in class ressources), allowing a better control for child theme
* added : (css) current menu item or current menu ancestor is colored with the skin color
* added : (php) function to check if we are using a child theme. Handles WP version <3.4.
* improved : (css) new conditional stylesheets ie8-hacks : icon sizes for IE8
* improved : (css) better table styling
* improved : (php) logo dimensions are beeing rendered in the img tag
* improved : (php) class group instanciation is faster, using the class group array instead of each singular group of class.
* improved : (php) the search and archive headers are easier to filter now with dedicated functions
* fixed : (css) archives and search icons color were all green for all skins
* fixed : (php) 404 content was displayed several times in a nested url rewrite context thanks to <a href="http://wordpress.org/support/profile/electricfeet" target="_blank">electricfeet</a>
* fixed : (php) attachment meta data dimensions : checks if are set $metadata['height'] && $metadata['width'] before rendering
* fixed : (php) attachment post type : checks if $post is set before getting the type
* fixed : (php) left and right sidebars are rendered even if they have no widgets hooked in thanks to <a href="http://wordpress.org/support/profile/pereznat" target="_blank">pereznat</a>.


= 3.0.10 =
* CHILD THEME USERS, templates have been modified : index.php, header.php, footer.php, comments.php *
* added : (php) (css) (html) New option : Draggable help box and clickable tooltips to easily display some contextual information and help for developers
* added : (php) support for custom post types for the slider meta boxes
* added : (php) new filter to get the post type
* added : polish translation. thanks to Marcin Sadowski from <a href="http://www.sadowski.edu.pl" target="_blank">http://www.sadowski.edu.pl</a>
* added : (php) (html) attachments are now listed in the search results with their thumbnails and descriptions, just like posts or pages
* added : (css) comment navigation styling, similar to post navigation
* added : (php) (css) author box styling (if bio field not empty)
* added : (css) comment bubble for pages
* added : (js) smooth transition for "back to top" link. Thanks to Nikolov : <a href="https://github.com/nikolov-tmw" target="_blank">https://github.com/nikolov-tmw</a>
* added : (js) smooth image loading on gallery attachment navigation
* added : (lang) Dutch translation. Thanks to Joris Dutmer.
* added : (css) icon to title of archive, search, 404
* improved : (php) attachment screen layout based on the parent
* improved : (php) simpler action hooks structure in the main templates : index, header, footer, comments, sidebars
* improved : (css) responsive behaviour : slider caption now visible for devices < 480px wide, thumbnail/content layout change for better display, body extra padding modified
* improved : (php) For better performances : options (single and full array) are now get from the TC_utils class instance instead of querying the database. (except for the customization context where they have to be retrieved dynamically from database on refresh)
* improved : (js) performance : tc_scripts and ajax_slider have been minified
* fixed : (css) IE fix : added z-index to active slide to fix slides falling below each other on transition. Thanks to PMStanley <a href="https://github.com/PMStanley">https://github.com/PMStanley</a>
* fixed : (css) IE fix : added 'top: 25%' to center align slide caption on older versions of IE. Thanks to PMStanley <a href="https://github.com/PMStanley" target="_blank">https://github.com/PMStanley</a>
* fixed : (php) empty reply button in comment threads : now checks if we reach the max level of threaded comment to render the reply button
* fixed : (php) empty nav buttons in single posts are not displayed anymore
* fixed : (css) font-icons compatibility with Safari is fixed for : page, formats (aside, link; image, video) and widgets (recent post, page menu, categories) thanks to <a href="http://wordpress.org/support/profile/electricfeet" target="_blank">electricfeet</a>
* fixed : (css) ordered list margin were not consistent in the theme thanks to <a href="http://wordpress.org/support/profile/electricfeet" target="_blank">electricfeet</a>
* fixed : (css) slider text overflow
* removed : sidebars templates. Sidebar content is now rendered with the class-content-sidebar.php


= 3.0.9 =
* ! SAFE UPGRADE FOR CHILD THEME USERS (v3.0.8 => v3.0.9) ! *
* fixed : function tc_is_home() was not checking the case where display nothing on home page. No impact for child theme users. Thanks to <a href="http://wordpress.org/support/profile/monten01">monten01</a>, <a href="http://wordpress.org/support/profile/rdellconsulting" target="_blank">rdellconsulting</a>
* fixed : When the permalink structure was not set to default, conditional tags is_page() and is_attachement() stopped working. They are now replaced by tests on $post -> post_type in class-main-content.php
* fixed : test if jet_pack is enabled before filtering post_gallery hook => avoid conflict
* fixed : @media print modified to remove links thanks to <a href="http://wordpress.org/support/profile/electricfeet" target="_blank">electricfeet</a>
* fixed : btn-info style is back to original Bootstrap style thanks to <a href="http://wordpress.org/support/profile/jo8192" target="_blank">jo8192</a>
* fixed : featured pages text => html tags are removed from page excerpt
* improved : custom css now allows special characters
* improved : better css structure, media queries are grouped at the end of the css files
* added : two new social networks in Customizer options : Instagram and WordPress
* added : help button and page in admin with links to FAQ, documentation and forum
* added : new constant TC_WEBSITE for author URI


= 3.0.8 =
* fixed : function tc_is_home() was missing a test. No impact for child theme users. Thanks to <a href="http://wordpress.org/support/profile/ldanielpour962gmailcom">http://wordpress.org/support/profile/ldanielpour962gmailcom</a>, <a href="http://wordpress.org/support/profile/rdellconsulting">http://wordpress.org/support/profile/rdellconsulting</a>, <a href="http://wordpress.org/support/profile/andyblackburn">http://wordpress.org/support/profile/andyblackburn</a>, <a href="http://wordpress.org/support/profile/chandlerleighcom">http://wordpress.org/support/profile/chandlerleighcom</a>


= 3.0.7 =
* fixed : the "force default layout" option was returning an array instead of a string. Thanks to http://wordpress.org/support/profile/edwardwilliamson and http://wordpress.org/support/profile/henry12345 for pointing this out!
* improved : get infos from parent theme if using a child theme in customizr-__ class constructor
* improved : enhanced filter for footer credit
* added : a notice about changelog if using a child theme
* improved : use esc_html tags in featured page text and slider captions


= 3.0.6 =
* fixed : Spanish translation has been fixed. Many thanks again to Maria del Mar for her great job!
* fixed : Pages protected with password will not display any thumbnail or excerpt when used in a featured page home block (thanks to rocketpopgames http://wordpress.org/support/profile/rocketpopgames)
* improved : performance : jquery.fancybox.1.3.4.js and modernizr have been minified
* added : footer credits can now be filtered with add_filter( 'footer_credits') and hooked with add_action ('__credits' )
* added : new customizer option to personnalize the featured page buttons


= 3.0.5 =
* fixed : breadcrumb translation domain was not right
* fixed : domain translation for comment title was not set
* fixed : in v3.0.4, a slider could disappeared only if some slides had been inserted at one time and then deleted or disabled afterward. Thanks to Dave http://wordpress.org/support/profile/rdellconsulting!
* fixed : holder.js script bug in IE v8 and lower. Fixed by updating holder.js v1.9 to v2.0. Thanks to Joel (http://wordpress.org/support/profile/jrisberg) and Ivan (http://wordpress.org/support/profile/imsky).
* improved : better handling of comment number bubble everywhere : check if comments are opened AND if there are comments to display
* improved : welcome screen on update/activate : changelog automatic update, new tweet button
* improved : lightbox navigation is now enabled for galleries with media link option choosen (new filters on post gallery and attachment_link)
* improved : better code organization : split of content class in specific classes by content type
* added : customizr option for images : enable/disable autoscale on lightbox zoom
* added : jQuery fallback for CSS Transitions in carousel (ie. Internet Explorer) : https://github.com/twbs/bootstrap/pull/3052/files
* added : spanish translation. Thanks to Maria del Mar


= 3.0.4 =
* fixed : minor css correction on responsive thumbnail hover effect
* fixed : minor syntaxic issue on comment title (printf)
* fixed : translation domain was wrong for social networks
* fixed : slider arrows were still showing up if slides were deleted but not the slider itself. Added a routine to check if slides have attachment.
* improved : image galleries : if fancybox active, lightbox navigation is now enabled
* improved : better capability control of edit page button. Only appears if user_can edit_pages (like for posts)
* added : Activation welcome screen
* added : new action in admin_init hook to load the meta boxes class


= 3.0.3 =
* added : german translation. Thanks to Martin Bangemann <design@humane-wirtschaft.de> !
* changed : default option are now based on customizer settings
* fixed : reordering slides was deleting the slides


= 3.0.2 =
* fixed : problem fixed on theme zipping and upload in repository 


= 3.0.1 =
* fixed : "header already sent" error fixed (space before php opening markup in an admin class) was generating an error on log out  

= 3.0 =
* changed : global code structure has changed. Classes are instanciated by a singleton factory, html is rendered with actions, values are called through filters
* fixed : favicon rendering, $__options was not defined in head
* fixed : sidebars reordering on responsive display, tc_script.js file


= 2.1.8 =
* changed : activation options are disable for posts_per_page and show_on_front
* changed : redirect to customizr screen on first theme activation only


= 2.1.7 =
* fixed : home page slider was checking a $slider_active variable not correctly defined
* fixed : slider name for page and post was only ajax saved. Now also regular save on post update.


= 2.1.6 =
* improved : Menu title padding
* fixed : front office : page and post sliders could not be disable once created
* removed : some unnecessary favicon settings
* fixed : function wp_head() moved just before the closing <head> tag
* added : filter on wp_filter function
* added : russion translation, thanks to Evgeny Sudakov!
* improved : thumbnail and content layout for posts lists
* fixed : ajax saving was not working properly for page/page slider, a switch case was not breaked.



= 2.1.5 =
* fixed 	: When deleted from a slider, the first slide was not cleared out from option array
* added 	: Titles in customizer sections
* added 	: checkbox to enable/disable featured pages images
* added 	: Optional colored top border in customizer options
* added 	: new black skin
* removed 	: text-rendering: optimizelegibility for hx, in conflict with icon fonts in chrome version 27.0.1453.94
* improved 	: blockquote styling
* fixed 	: in tc_script.js insertbefore() target is more precise
* improved 	: font icons are now coded in CSS Value (Hex)
* added 	: add_action hooks in the templates index and sidebars


= 2.1.4 =
* fixed : in tc_meta_boxes.php, line 766, a check on the existence of $slide object has been added
* fixed : iframe content was dissapearing when reordering divs on resize. Now  handled properly in tc_scripts.js
* fixed : breadcrumb menu was getting covered (not clickable) in pages. fixed in css with z-index.
* fixed : thumbnails whith no-effect class are now having property min-height:initial => prevents stretching effect
* fixed : revelead images of featured page were stretched when displayed with @media (max-width: 979px) query
* fixed : better vertical alignment of the responsive menu
* changed : color of slider arrows on hover
* changed : text shadow of titles
* changed : color and shadow of site description

= 2.1.3 =
* fixed : in tc_voila_slider, jump to next loop if attachment has been deleted
* removed : title text in footer credit
* fixed : image in full width slider are displayed with CSS properties max-width: 100%, like before v2.0.9

= 2.1.2 =
* fixed : new screenshot.png

= 2.1.1 =
* fixed : new set of images licensed under Creative Commons CC0 1.0 Universal Public Domain Dedication (GPL Compatible)


= 2.1.0 =
* fixed : slide was still showing up when 'add to a slider' button was unchecked and a slider selected
* fixed : new set of images with compliant licenses


= 2.0.9 =
* replaced : jquery fancybox with a GPL compatible version
* removed : icon set non GPL compatible
* added : icon sets Genericons and Entypo GPL compatible
* fixed : image in full width slider are now displayed with CSS properties height:100% et width: auto
* added : function hooked on wp_head to render the custom CSS


= 2.0.8 =
* removed : minor issue, the function tc_write_custom_css() was written twice in header.php

= 2.0.7 =
* fixed : custom featured text (for featured pages) on front page was not updated when updated from customizer screen
* fixed : title of page was displayed when selected as static page for front page
* fixed : border-width of the status post-type box
* added : custom css field in customizer option screen
* added : lightbox checkbox option in customizer option screen

= 2.0.6 =
* added : new customizer option to enable/disable comments in page. Option is tested in index.php before rendering comment_templates for pages
* fixed : in the stylesheets, the right border of tables was unnecessary

= 2.0.5 =
* fixed : printf php syntax in footer.php

= 2.0.4 =
* fixed : test on current_user_can( 'edit_post' ) in template part content-page.php was generating a Notice: Undefined offset: 0 in ~/wp-includes/capabilities.php on line 1067
* added : copyright and license declaration in style.css

= 2.0.3 =
* fixed : same unique slug as prefix for all custom function names, classes, public/global variables, database entries.

= 2.0.2 =
* fixed : CSS image gallery navigation arrows
* removed : the_content() in attachment templates
* fixed : bullet list in content now visible
* added : hover effect on widget lists
* fixed : skin colors when hovering and focusing form fields
* fixed : skin colors when hovering social icons

= 2.0.1 =
* Removal of meta description (plugin territory)
* Page edit button is only visible for users logged in and with edit_post capabilities

= 2.0 =
* Replacement of the previous custom post type slider feature (was plugin territory) with a custom fields and options slider generator  
* Addition of ajax powered meta boxes in post/page/attachment for the sliders

= 1.1.7 =
* file structure simplification : one core loop in index.php

= 1.1.6 =
* Removal of add_editor_style()
* Addition of image.php and content-attachemnt.php for the images galleries and attachement rendering

= 1.1.5 =
* Sanitization of home_url() in some files (with esc_url)
* Clearing of warning message in slides list : check on the $_GET['action'] index
* Addition of some localized strings
* Removal of the optional WP footer credit links

= 1.1.4 =
* addition of selected() and checked() functions in metaboxes input
* better sanitization of WP customizer inputs : 3 sanitization callbacks added in tc_customizr_control_class for number, textarea and url

= 1.1 =
* Better stylesheets enqueuing
* Fix the quick mode edit for slide custom post : add a script to disable the clearing of metas fields on update
* Add a fallback screen on activation if WP version < 3.4 => WP Customizer not supported
* Fix the slide caption texts rendering change the conditions (&& => ||)

= 1.0 =
* Initial Release
 

Enjoy it!