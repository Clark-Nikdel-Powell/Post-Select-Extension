=== Plugin Name ===
Contributors: gwelser
Tags: contact, contact form, contact form 7, select, dropdown, Post
Requires at least: 4.0
Tested up to: 4.3
Stable tag: 1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Provides a select form field with options pulled from a configurable post type. Requires Contact Form 7.

== Description ==

This extension for Contact Form 7, allows for the addition of a dropdown box with options and values based on a post type.

For example, if you have a Product post type, using this extension you can add a dropdown box to your contact form allowing the user to choose a specific product they are inquiring about.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. The 'Post Select' tag option will be available in the Contact Form 7 tag generator

== Usage ==

Configuring your post select dropdown:

**Post type:** Select the post type you'd like to use for the selectable dropdown options.
**Option value:** Enter the data field to use for the selected value. This defaults to the post id. To use a post meta field, prepend the field name with '_meta_'.
_Example: To use a post meta field name 'email' as the selected value, enter '_meta_email' in this field._
Note that the actual option values are not output in the resulting form tag HTML but will be output in the email sent out by CF7.
**Default value:** Define a default selected value for the dropdown.
**Query string:** If you'd like the dropdown to default to a selected value based on a query string, enter the query string name in this field.

== Screenshots ==

1. The form tag generator.
2. The shorttag in a form.
3. The resulting output showing Posts as the dropdown options.

== Changelog ==

= 1.2 =
* Added a tag to the CP7_PSE shortcode to set the initially selected value of the post-select drop-down to a query string value.
* Added a tag to the CP7_PSE shortcode to set the initially selected value of the post select drop-down to a default value.

= 1.1 =
Code refactoring.

= 1.0 =
* Initial release.