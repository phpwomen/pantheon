<?php
/*
Plugin Name: Sennza Google Authorship
Plugin URI: http://www.sennza.com.au/2011/07/google-authorship/
Description: "Google Authorship" is a plugin that adds rel="me", rel="author" and a link to the Author's Google Profile.
Version: 0.5
Author: Bronson Quick
Author URI: http://www.sennza.com.au
License: GPLv2

Copyright 2011 Bronson Quick  (email : bronson@sennza.com.au)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//First up we need to tell WordPress that rel="me" XFN is allowed. Thanks to Yoast for this code snippet: http://yoast.com/wordpress-rel-author-rel-me/
add_action( 'wp_loaded', 'sennza_allow_rel' );
function sennza_allow_rel() {
  global $allowedtags;
  $allowedtags['a']['rel'] = array ();
}

//Add the Google Profile URL
function sennza_google_authorship_add_contact_info( $contactmethods ) {
	$contactmethods['google'] = "Google Profile URL <span class='description'>e.g. https://plus.google.com/111688116309023573923/ </span>";
    $contactmethods['twitter'] = "Twitter URL <span class='description'>e.g. https://twitter.com/#!/bronsonquick</span>";
	return $contactmethods;
}
add_filter('user_contactmethods', 'sennza_google_authorship_add_contact_info');
?>