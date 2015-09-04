<?php
/**
 * Contact Form 7 - Post Select Extension
 *
 * Post Select Extension adds a Post Select Drop-down tag option to the Contact 
 * Form 7 form edit screen. The Post Select Drop-down will pull the chosen 
 * values from a single post type and display them as selectable options in an 
 * HTML select tag.
 *
 * @package   CF7_PSE_Post_Select
 * @link      http://clarknikdelpowell.com
 * @since     1.0.0
 * @author    Glenn Welser <glenn@clarknikdelpowell.com>
 * @copyright 2015 Glenn Welser
 * @license   GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Contact Form 7 - Post Select Extension
 * Plugin URI:  http://clarknikdelpowell.com
 * Description: Provides a select form field with options pulled from a configurable post type. Requires Contact Form 7.
 * Version:     1.2.0
 * Author:      Glenn Welser
 * Author URI:  http://clarknikdelpowell.com/agency/people/glenn
 * Text-Domain: contact-form-7-post-select-extension
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Core plugin class.
 * 
 * @since 1.0.0
 */
class CF7_PSE_Post_Select {

	/**
	 * Core functionality.
	 * 
	 * Set the hooks for plugin loading and form tag output.
	 * 
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'plugins_loaded', array( __CLASS__, 'wpcf7_postselect_init' ), 20 );
		
		add_filter( 'wpcf7_form_tag', array( __CLASS__, 'wpcf7_form_tag' ), 20 );

	}

	/**
	 * Set shortcodes and admin hook.
	 *
	 * Add shortcodes for Post Select Drop-down and add hook for the tag 
	 * generator in admin.
	 * 
	 * @since 1.0.0
	 * 
	 * @see   wpcf7_add_shortcode
	 */
	public static function wpcf7_postselect_init() {

		if ( function_exists('wpcf7_add_shortcode') ) {

			wpcf7_add_shortcode( 'postselect', array( __CLASS__, 'wpcf7_postselect_shortcode_handler'), true );
			wpcf7_add_shortcode( 'postselect*', array( __CLASS__, 'wpcf7_postselect_shortcode_handler'), true );

		}

		add_action( 'admin_init', array( __CLASS__, 'wpcf7_add_tag_generator_postselect'), 15 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts'), 20 );

	}

	/**
	* Enqueue scripts.
	*
	* Adds required javascript file to footer of CF7 edit page.
	*
	* @since 1.2.0
	*/
	public static function admin_enqueue_scripts() {

		if ( !isset($_GET['page']) ) {
			return;
		}

		if ( $_GET['page'] != 'wpcf7' ) {
			return;
		}
		wp_enqueue_script( 'cf7_pse_script', plugins_url( '/js/app.js', __FILE__ ), array('jquery'), false, true );

	}

	/**
	 * Shortcode handler.
	 * 
	 * Parses a WPCF7_Shortcode object and returns the resulting HTML select 
	 * form element.
	 * 
	 * @since  1.0.0
	 * 
	 * @see    WPCF7_Shortcode
	 * 
	 * @param  object $tag The post select tag object.
	 * @return string      The formatted HTML select form tag.
	 */
	public static function wpcf7_postselect_shortcode_handler( $tag ) {

		$tag = new WPCF7_Shortcode( $tag );

		if ( empty( $tag->name ) ) {
			return '';
		}

		$validation_error = wpcf7_get_validation_error( $tag->name );

		$class = wpcf7_form_controls_class( $tag->type );

		if ( $validation_error ) {
			$class .= ' wpcf7-not-valid';
		}

		$atts = [];
		$atts['class'] = $tag->get_class_option( $class );
		$atts['id'] = $tag->get_id_option();
		$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

		if ( $tag->is_required() ) {
			$atts['aria-required'] = 'true';
		}

		$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

		$include_blank = $tag->has_option( 'include_blank' );

		$values = $tag->values;
		$labels = $tag->labels;

		if ( $data = (array) $tag->get_data_option() ) {
			$values = array_merge( $values, array_values( $data ) );
			$labels = array_merge( $labels, array_values( $data ) );
		}

		$empty_select = empty( $values );

		if ( $empty_select || $include_blank ) {
			array_unshift( $labels, '---' );
			array_unshift( $values, '' );
		}
		
		$html = '';
		
		$get = $tag->get_option('get', 'class', TRUE);
		$selectedvalue = $get 
				? filter_var($_GET[$get], FILTER_SANITIZE_STRING) 
				: false;
		if (!$selectedvalue) {
			$defaultvalue = $tag->get_option('default', '', TRUE);
			$selectedvalue = $defaultvalue ? urldecode($defaultvalue) : false;
		}
		foreach ( $values as $key => $value ) {	
			$selected = false;
			
			if ($selectedvalue) {
				$selected = ( $selectedvalue == esc_sql( $value ) );
			}

			$item_atts = array(
				'value' => $value,
				'selected' => $selected ? 'selected' : ''
			);

			$item_atts = wpcf7_format_atts( $item_atts );

			$label = isset( $labels[$key] ) ? $labels[$key] : $value;

			$html .= sprintf( '<option %1$s>%2$s</option>', $item_atts, esc_html( $label ) );
		}

		$atts['name'] = $tag->name;

		$atts = wpcf7_format_atts( $atts );

		$html = sprintf(
			'<span class="wpcf7-form-control-wrap %1$s"><select %2$s>%3$s</select>%4$s</span>',
			sanitize_html_class( $tag->name ), $atts, $html, $validation_error );

		wp_reset_query();

		return $html;

	}

	/**
	 * Initialize tag generator.
	 * 
	 * Begins creation of tag generator for Post Select Drop-down option on 
	 * Contact Form 7 edit screen.
	 * 
	 * @since 1.0.0
	 * 
	 * @see wpcf7_add_tag_generator
	 */
	public static function wpcf7_add_tag_generator_postselect() {

		if ( function_exists('wpcf7_add_tag_generator') ) {

			wpcf7_add_tag_generator( 'postselect', __('post select drop-down', 'contact-form-7-post-select-extension'), 'wpcf7-tg-pane-postselect', array( __CLASS__, 'wpcf7_tg_pane_postselect_' ) );

		}
	}

	/**
	 * Call Post Select Drop-down creation pane.
	 * 
	 * This is a pass-through function. It calls the actual Post Select 
	 * Drop-down pane creator.
	 * 
	 * @since 1.0.0
	 * 
	 * @param type $contact_form
	 */
	public static function wpcf7_tg_pane_postselect_($contact_form) {

		CF7_PSE_Post_Select::wpcf7_tg_pane_postselect( 'postselect' );

	}

	/**
	 * Display Post Select Drop-down tag configuration pane.
	 * 
	 * Displays the tag configuration options used when generating the tag pane.
	 * 
	 * @since 1.0.0
	 * 
	 * @param  string $type The specific pane to be displayed.
	 */
	public static function wpcf7_tg_pane_postselect( $type = 'postselect' ) {
	?>
		<div class="control-box">
		<fieldset>
		<legend></legend>
		<table class="form-table">
		<tbody>
		<tr>
		<th scope="row">Field type</th>
		<td><fieldset>
		<legend class="screen-reader-text">Field type</legend>
		<label><input type="checkbox" name="required" />&nbsp;Required field</label></fieldset></td>
		</tr>
		<tr>
		<th scope="row"><label for="tag-generator-panel-<?= $type; ?>-name">Name</label></th>
		<td><input type="text" name="name" class="tg-name oneline" id="tag-generator-panel-<?= $type; ?>-name" /></td>
		</tr>

		<tr>
		<th scope="row"><label for="tag-generator-panel-<?= $type; ?>-post_type">Post type</label></th>
		<td><select name="post_typeselect" data-value="post_type" class="oneline selectoption" id="tag-generator-panel-<?= $type; ?>-post_type"><?php CF7_PSE_Post_Select::post_type_options(); ?></select>
		<input type="hidden" name="post_type" class="post_typevalue oneline option" id="tag-generator-panel-<?= $type; ?>-post_type" /></td>
		</tr>
		<tr>
		<th scope="row"><label for="tag-generator-panel-<?= $type; ?>-option_value">Option value</label></th>
		<td><input type="text" name="option_value" class="option_valuevalue oneline option" id="tag-generator-panel-<?= $type; ?>-option_value" /></td>
		</tr>
		<tr>
		<th scope="row"></th>
		<td><fieldset>
		<legend class="screen-reader-text">Include blank</legend>
		<label><input type="checkbox" name="include_blank" class="option" value="on" />&nbsp;Include a blank item as the first option</label></fieldset></td>
		</tr>
		<tr>
		<th scope="row"><label for="tag-generator-panel-<?= $type; ?>-default">Default value</label></th>
		<td><input type="text" name="default" class="default oneline option" id="tag-generator-panel-<?= $type; ?>-default" /></td>
		</tr>
		<tr>
		<th scope="row"><label for="tag-generator-panel-<?= $type; ?>-get">Query string</label></th>
		<td><input type="text" name="get" class="get oneline option" id="tag-generator-panel-<?= $type; ?>-get" /></td>
		</tr>

		<tr>
		<th scope="row"><label for="tag-generator-panel-<?= $type; ?>-id">Id attribute</label></th>
		<td><input type="text" name="id" class="idvalue oneline option" id="tag-generator-panel-<?= $type; ?>-id" /></td>
		</tr>
		<tr>
		<th scope="row"><label for="tag-generator-panel-<?= $type; ?>-class">Class attribute</label></th>
		<td><input type="text" name="class" class="classvalue oneline option" id="tag-generator-panel-<?= $type; ?>-class" /></td>
		</tr>
		</tbody>
		</table>
		</fieldset>
		</div>
		<div class="insert-box">
		<input type="text" name="<?= $type; ?>" class="tag wp-ui-text-highlight code" readonly="readonly" onfocus="this.select()" />
		<div class="submitbox">
		<input type="button" class="button button-primary insert-tag" value="Insert Tag">
		</div>
		<br class="clear">
		<p class="description mail-tag"><label for="tag-generator-panel-<?= $type; ?>-mailtag">To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (<strong><span class="mail-tag"></span></strong>) into the field on the Mail tab.<input type="text" class="mail-tag code hidden" readonly="readonly" id="tag-generator-panel-<?= $type; ?>-mailtag"></label></p>
		</div>
	<?php
	}

	/**
	 * Get post data.
	 * 
	 * Gets the chosen meta data from the chosen post type and returns an array.
	 * 
	 * @since 1.0.0
	 * 
	 * @see WP_Query
	 * 
	 * @param  string $type The post type to get.
	 * @param  string $meta The data/field to get from the post type.
	 * @return array        An array containing the post ids, titles, and selected data.
	 */
	public static function get_post_type( $type, $meta ) {
		
		$args = array(
			
			'post_type'      => $type,
			'post_status'    => 'publish',
			'order'          => 'ASC',
			'orderby'        => 'title',
			'posts_per_page' => -1

		);
		
		$posts = get_posts( $args );

		$output = [];

		foreach ( $posts as $p ) {

			$title = $p->post_title;
			$value = $p->ID;

			$pos = strpos( $meta, '_meta_' );
			if ( $pos === 0 ) {
				$value = get_post_meta( $p->ID, str_replace('_meta_', '', $meta), true );
			}

			$output[] = [ $title, $value ];

		}

		return $output;

	}
	
	/**
	 * Form tag filter.
	 *
	 * Filters the tag array to add in the selected data values from the 
	 * selected post type. Uses Contact Form 7 Pipes for selected values.
	 * 
	 * @since 1.0.0
	 * 
	 * @see WPCF7_Shortcode, WPCF7_Pipes, CF7_PSE_Post_Select::get_post_type
	 * 
	 * @param  array $tag_array An array containing the defined shortcode options.
	 * @return array            The array with values/pipes added.
	 */
	public static function wpcf7_form_tag( $tag_array ) {
		
		if ( $tag_array['type'] !== 'postselect' ) {
			return $tag_array;
		}
		
		$tag = new WPCF7_Shortcode( $tag_array );

		$post_type = $tag->get_option('post_type', 'class', true);
		$option_value = $tag->get_option('option_value', 'class', true);

		$post_data = CF7_PSE_Post_Select::get_post_type($post_type, $option_value);

		foreach ($post_data as $entry) {
			$tag_array['raw_values'][] = $entry[0].'|'.$entry[1];
		}
		
		if ( WPCF7_USE_PIPE ) {
			$pipes = new WPCF7_Pipes( $tag_array['raw_values'] );
			$tag_array['values'] = $pipes->collect_befores();
			$tag_array['pipes'] = $pipes;
		} else {
			$tag_array['values'] = $tag_array['raw_values'];
		}
				
		return $tag_array;
		
	}

	public static function post_type_options() {
		$args = array( 'public' => true );
		$post_types = get_post_types( $args, 'objects' );
		foreach ( $post_types as $post_type ) {
			?>
			<option value="<?php echo $post_type->name; ?>"><?php echo $post_type->label; ?></option>
			<?php
		}
	}

}

/**
 * Initialize plugin.
 *
 * @since 1.0.0
 */
new CF7_PSE_Post_Select();