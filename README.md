# Contact Form 7 Post Select Extension

This extension for Contact Form 7, allows for the addition of a dropdown box with options and values based on a post type.

For example, if you have a Product post type, using this extension you can add a dropdown box to your contact form allowing the user to choose a specific product they are inquiring about.

## Installation

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The 'Post Select' tag option will be available in the Contact Form 7 tag generator

## Usage

Configuring your post select dropdown:

**Post type:** Select the post type you'd like to use for the selectable dropdown options.

**Option value:** Enter the data field to use for the selected value. This defaults to the post id. To use a post meta field, prepend the field name with '_meta_'.
_Example: To use a post meta field name 'email' as the selected value, enter '_meta_email' in this field._
Note that the actual option values are not output in the resulting form tag HTML but will be output in the email sent out by CF7.

**Default value:** Define a default selected value for the dropdown.

**Query string:** If you'd like the dropdown to default to a selected value based on a query string, enter the query string name in this field.