=== Advanced Custom Fields: Validated Field ===
Contributors: doublesharp
Tags: acf, advanced custom fields, validation, validate, regex, php, mask, input, readonly, add-on, unique, input, edit, admin, post, page, meta
Requires at least: 3.0
Tested up to: 4.2.1
Stable tag: 1.7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Validated Field add-on for Advanced Custom Fields provides input masking and server-side validation of other field types.

== Description ==
= Validated Field Add-On =
The **Validated Field** add-on for [Advanced Custom Fields](http://wordpress.org/extend/plugins/advanced-custom-fields/)
provides a wrapper for other input types which allows you to provide client side input masking using the jQuery 
[Masked Input Plugin](http://digitalbush.com/projects/masked-input-plugin/), server side validation using either PHP regular expressions 
or PHP code, the option of ensuring a field's uniqueness for all posts by `post_type` and `meta_key`, `post_type`, or site wide, or a 
single post by meta_key, as well as marking a field as read-only. Edit your fields in the ACF Field Group editor and update code using the ACE.js
IDE with autocomplete and syntax validation.

= Features =
1. **Input Masking** - easily set masks on text inputs to ensure data is properly formatted.
2. **Server-Side Validation** - validate the inputs using server side PHP code or regular expressions.
3. **Uniqueness** - ensure that the value being updated is not already in use.
4. **Repeater Fields** - validated fields within a [Repeater Field](http://www.advancedcustomfields.com/add-ons/repeater-field/).
5. **Read Only** - specify a field as read-only allowing it to be displayed but not updated.
6. **WordPress Multi Language** - compatible with multilingual sites using the WPML plugin.
7. **Conditional Logic** - show and hide validated fields based on the values of other "switch" fields.

= Compatibility =
Requires [Advanced Custom Fields](http://wordpress.org/extend/plugins/advanced-custom-fields/) version 4.0 or greater.

== Installation ==
1. Download the plugin and extract to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure validated fields within the Advanced Custom Fields menus.

== Frequently Asked Questions ==
= I've activated the Validated Field plugin, but nothing happens =
Ensure that you have [Advanced Custom Fields](http://wordpress.org/extend/plugins/advanced-custom-fields/) installed and that it is activated. 
Validated Field will appear as a new input type in the field group editor.

= Configuration Options =
Global configurations for the Validated Field plugin can be found in the WordPress Admin under `Custom Fields > Validated Field Settings`.

== Screenshots ==
1. Example configuration for input masking and regular expression checking of a telephone number field.
2. Example of client-side input masking on a telephone number field.
3. Example of failing to pass a regular expression check (phone number is incomplete).
4. Example of failing to pass a uniqueness check - the telephone number is already in use by post "Test 1".
5. Example of PHP validation configuration. This code will fail if the request comes from "127.0.0.1" with the message "You cannot save from localhost!".
6. Example of client side PHP validation failure.

== Changelog ==
= 1.7.4 =
* Update CSS to properly handle visibility of validated fields via conditional logic when the default visibility is hidden for new entries.
* Bug fix: Remove warning when trying to access the Post ID when it is not available, on Options pages for example.


= 1.7.3 =
* Replace call to `acf_render_field_settings()` with action `acf/render_field_settings/type=?` to support ACF 5.2.3+.


= 1.7.2 =
* Allow `$message` to be returned from PHP code instead of `false` to fail validation.
  * Example code: `if ( !filter_var( $value, FILTER_VALIDATE_URL ) ) return 'You must use a valid URL.';`
  * Validation will fail if `$value` is not a URL, otherwise the default validation return is `true`.
* Clean up handling of read only/hidden select fields.
* Include additional default sub field values to prevent array index errors.
* Update to work with the latest version of ACF JavaScript validation - requires nested `<div>` to properly append error message to the correct input field.


= 1.7.1 =
* Update for compatibility with new Repeater/select2 implementation in ACF.
* Dynamically copy field from parent to sub field allowing for greater flexibility in sub field type support.
* Remove old code/comments.
* Bug fix: Only trigger "change" event on sub field type when the field is a clone, not an existing field.
* Bug fix: Fix processing of sub field "name" to correctly trigger ACF filters.


= 1.6 = 
* Show info/error message when input masking is used with the "number" field type as they are not compatible.
* Upgrade to jQuery Masked Input version 1.4.1.
* <strong><i>ACF5 only:</i></strong>
 * Include unique value options of “Post" and "Post + Key".
 * Check submitted values for duplicates when the field type is unique. This fixes the issue where duplicate values are added to a unique repeater field, or the same value is entered for multiple meta_keys when the unique type is "global", "post type", or "post".
* Bug fix: Correctly call `$wpdb->esc_like()` as instance method for "globally unique" fields.
* Bug fix: Improve field/sub field handling and to correctly populate "id" for use in HTML CSS selectors.


= 1.5.1 =
* Bug fix: Use better supported PHP syntax for getting repeater field index, props @dnrms.


= 1.5 =
* Increase height of ACE editor.
* Improve jQuery selectors for WordPress Admin functionality.
* Replace deprecated `like_escape()` function with `$wpdb->esc_like()`.
* Update read-only icon on edit screens.
* Bug fix: Undefined array index errors.
* Bug fix: Repeater field unique validation for post type and post type + key.
* Bug fix: WPML field unique validation for post type and post type + key.
* Bug fix: UI display controls for pattern/ACE editor.


= 1.4 = 
* Support for Advanced Custom Fields 5.0.
* The `$inputs` variable is now available using and index of `meta_key` and returning an array with the values "field", "value", and "prev_value".
* Fix for custom "post_id" string value instead of and integer value.
* Host `ace.js` libraries locally and upgrade to version 1.1.7.
 * Increase size of PHP editor.
 * Support for PHP snippets and autocomplete.
 * New editor functions: Search (`Ctrl+F`/`Cmd+F`), Replace (`Ctrl+Alt+F`/`Cmd+Opt+F`), and Replace All (`Ctrl+Alt+Shft+F`/`Cmd+Opt+Shft+F`).
* Host `fontawesome.css` CSS and fonts locally and upgrade to version 4.2.
* Upgraded jQuery Masked Input plugin to version 1.4.
* Better support for Relationship field settings - only load sub field when the action is `acf/fields/relationship/query_posts` (ACF4) or `acf/fields/relationship/query` (ACF5).
* For ACF 5+ only:
 * The new [`acf/validate_value`](http://www.advancedcustomfields.com/resources/acf-validate_value/) filter is used for better compatibility and performance.
 * Get rid of inline JavaScript in the WordPress Admin and leverage ACF JavaScript events.
* For ACF 4 only:
 * Compatibility with tabbed layouts (natively supported in ACF 5)


= 1.3.1 =
* Bug Fix: Apply input masking to fields for new posts, not just editing existing ones.


= 1.3 =
* Support front end validation using [`acf_form()`](http://www.advancedcustomfields.com/resources/functions/acf_form/).
* Support for WPML, props @gunnyst.
* Move configuration to WordPress Admin under `Custom Fields > Validated Field Settings`.
 * Debug - enable debugging, defaults to off.
 * Drafts - enable draft validation, defaults to on.
 * Front End - enable front end validation, defaults to off.
 * Front End Admin CSS - enable `acf_form_head()` to enqueue an admin theme, defaults to on.
* Improved SQL for unique queries to support Relationship fields - check both arrays and single IDs.
* Fix conflicts with ACF client side validation (required fields, etc).
* Fix reference to `$sub_field['read_only']` with `$field['read_only']` for jQuery masking, props @johnny_br.


= 1.2.7 =
* Bug Fix: Post Preview fix when WordPress 'click' event triggers a 'submit' before the clicked element can be tracked by the plugin.
* Added comments to unpacked JavaScript.


= 1.2.6 =
* Critical Bug Fix: Fix compatibility issues with Firefox.


= 1.2.5.1 =
* Remove debug `error_log()` statement from v1.2.5.


= 1.2.5 =
* Finish text localization, include `es_ES` translation.
* Pack and compress validation javascript.
* Bug Fix: prevent PHP array index notice for non-repeater fields.
* Code formatting.


= 1.2.3 =
* Support for globally bypassing Draft/Preview validation by setting `ACF_VF_DRAFTS` to `false`.
* Support for bypassing Draft/Preview validation per field (defaults to validate).
* Bug fixes: properly hide Draft spinner, cleaned up JavaScript.


= 1.2.2 =
* Properly include plugin version number on JavaScript enqueue for caching and PHP notices.
* Use minified JavaScript unless `ACF_VF_DEBUG` is set to `true`.
* Tested up to WordPress 3.9.1


= 1.2.1 =
* Show 'Validation Failed' message in header as needed.
* Mark form as dirty when input element values change.
* Fix return of `$message` from field configuration to UI.


= 1.2 =
* Support for [Repeater Field](http://www.advancedcustomfields.com/add-ons/repeater-field/) Validated Fields.
* Support for debugging with `ACF_VF_DEBUG` constant.
* Clean up variable names, more code standardization.
* Better handling of required fields with validation.


= 1.1.1.1 =
* Remove debug `error_log()` statement from v1.1.1.


= 1.1.1 =
* Clean up PHP to WordPress standards.
* Fix PHP Notice breaking AJAX call.
* Use defaults to prevent invalid array indexes.
* Update JavaScript for UI Errors.
* More localization prep for text.


= 1.1 = 
* Add Read-only functionality (beta).
* Use standard ACF error/messaging.
* Correctly process "preview" clicks, fixes error where the post would be published.
* Register CSS only in required locations.
* Properly apply subfield filters for `acf/load_value/type=`, `acf/update_value/type=`, `acf/format_value/type=`, `acf/format_value_for_api/type=`, `acf/load_field/type=`, `acf/update_field/type=`.
* Tested up to WordPress 3.9.


= 1.0.7 =
* Critical bug fix for selecting Validated Field type.


= 1.0.6 =
* Bug fix `$sub_field` properties not saving (use `acf/create_field_options` action).
* Bug fix multiple Validated Fields in a set - correct to always use unique selectors.
* Allow for unique query to be run on selected post statuses.
* Set default statuses included in unique queries with filter of `acf_vf/unique_statuses`.
* Remove redundant table wrapper on validated fields.
* Clean up potential strict PHP warnings.


= 1.0.5 =
* Hide spinner for update if a validation error is encountered.
* Allow for uniqueness queries to apply to only published or all post statuses.
* Clean up debugging code writing to the error log for regex validations.


= 1.0.4 =
* Fix javascript error when including ace.js, props @nikademo.
* Fix "Undefined index" PHP notice, props @ikivanov.


= 1.0.3 =
* Bug fix for unique field values per `post_type`. Props @ikivanov.


= 1.0.2 =
* Bug fix for editing a validated field. Ensure proper type is selected and UI refresh is triggered. Props @fab4_33.


= 1.0.1 =
* Clean up strict warnings


= 1.0 =
* Update for compatibility with Advanced Custom Fields 4+
* Implement ace.js for syntax highlighting


= 0.1 =
* Initial version.