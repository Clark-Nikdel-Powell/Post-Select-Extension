<?php
/*
Plugin Name: Contact Form 7 - Post Select Extension
Description: Provides a select field with options pulled from a configurable post type. Requires Contact Form 7.
Plugin URI: http://clarknikdelpowell.com
Author: Glenn Welser
Author URI: http://clarknikdelpowell.com/agency/people/glenn
Version: 1.0
License: GPL2

Copyright (C) 2014  Glenn Welser  glenn@clarknikdelpowell.com

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

///////////////////////////////////////////////////////////////////////////////
// PLUGIN CONSTANT DEFINITIONS
////////////////////////////////////////////////////////////////////////////////

define('CF7_PSE_VER', '1.0');

//FILESYSTEM CONSTANTS
define('CF7_PSE_PATH', plugin_dir_path( __FILE__ ));
define('CF7_PSE_URL',  plugin_dir_url(  __FILE__ ));

////////////////////////////////////////////////////////////////////////////////
// ROOT PLUGIN CLASS
////////////////////////////////////////////////////////////////////////////////

class CF7_PSE_Post_Select {

	public function __construct() {

		add_action( 'plugins_loaded', array( __CLASS__, 'wpcf7_postselect_init' ), 20 );
		
		add_filter( 'wpcf7_form_tag', array( __CLASS__, 'wpcf7_form_tag' ), 20 );

	}

	public static function wpcf7_postselect_init() {

		if ( function_exists('wpcf7_add_shortcode') ) {

			wpcf7_add_shortcode( 'postselect', array( __CLASS__, 'wpcf7_postselect_shortcode_handler'), true );
			wpcf7_add_shortcode( 'postselect*', array( __CLASS__, 'wpcf7_postselect_shortcode_handler'), true );

		}

		add_action( 'admin_init', array( __CLASS__, 'wpcf7_add_tag_generator_postselect'), 15 );

	}

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

		$defaults = array();

		if ( $matches = $tag->get_first_match_option( '/^default:([0-9_]+)$/' ) ) {
			$defaults = explode( '_', $matches[1] );
		}

		$multiple = $tag->has_option( 'multiple' );
		$include_blank = $tag->has_option( 'include_blank' );
		$first_as_label = $tag->has_option( 'first_as_label' );

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
		} elseif ( $first_as_label ) {
			$values[0] = '';
		}

		$html = '';
		$hangover = wpcf7_get_hangover( $tag->name );

		foreach ( $values as $key => $value ) {
			$selected = false;

			if ( $hangover ) {
				if ( $multiple ) {
					$selected = in_array( esc_sql( $value ), (array) $hangover );
				} else {
					$selected = ( $hangover == esc_sql( $value ) );
				}
			} else {
				if ( ! $empty_select && in_array( $key + 1, (array) $defaults ) ) {
					$selected = true;
				}
			}

			$item_atts = array(
				'value' => $value,
				'selected' => $selected ? 'selected' : '' );

			$item_atts = wpcf7_format_atts( $item_atts );

			$label = isset( $labels[$key] ) ? $labels[$key] : $value;

			$html .= sprintf( '<option %1$s>%2$s</option>',
				$item_atts, esc_html( $label ) );
		}

		if ( $multiple ) {
			$atts['multiple'] = 'multiple';
		}

		$atts['name'] = $tag->name . ( $multiple ? '[]' : '' );

		$atts = wpcf7_format_atts( $atts );

		$html = sprintf(
			'<span class="wpcf7-form-control-wrap %1$s"><select %2$s>%3$s</select>%4$s</span>',
			sanitize_html_class( $tag->name ), $atts, $html, $validation_error );

		return $html;

	}

	public static function wpcf7_add_tag_generator_postselect() {

		if ( function_exists('wpcf7_add_tag_generator') ) {

			wpcf7_add_tag_generator( 'postselect', __('Post Select Drop-down', 'wpcf7'), 'wpcf7-tg-pane-postselect', array( __CLASS__, 'wpcf7_tg_pane_postselect_' ) );

		}
	}

	public static function wpcf7_tg_pane_postselect_($contact_form) {

		CF7_PSE_Post_Select::wpcf7_tg_pane_postselect( 'postselect' );

	}

	public static function wpcf7_tg_pane_postselect( $type = 'postselect' ) {
	?>
		<div id="wpcf7-tg-pane-<?php echo $type; ?>" class="hidden">
		<form action="">
		<table>
		<tr><td><input type="checkbox" name="required" />&nbsp;<?php echo esc_html( __( 'Required field?', 'contact-form-7' ) ); ?></td></tr>
		<tr><td><?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?><br /><input type="text" name="name" class="tg-name oneline" /></td><td></td></tr>
		</table>

		<table>
		<tr>
		<td><code>id</code> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
		<input type="text" name="id" class="idvalue oneline option" /></td>

		<td><code>class</code> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
		<input type="text" name="class" class="classvalue oneline option" /></td>
		</tr>

		<tr>
		<td>Post Type<br />
		<input type="text" name="post_type" class="post_typevalue oneline option" /><br />
		Meta Value<br />
		<input type="text" name="post_meta" class="post_metavalue oneline option" />
		</td>
		</tr>
		</table>

		<div class="tg-tag"><?php echo esc_html( __( "Copy this code and paste it into the form left.", 'contact-form-7' ) ); ?><br /><input type="text" name="<?php echo $type; ?>" class="tag wp-ui-text-highlight code" readonly="readonly" onfocus="this.select()" /></div>

		<div class="tg-mail-tag"><?php echo esc_html( __( "And, put this code into the Mail fields below.", 'contact-form-7' ) ); ?><br /><input type="text" class="mail-tag wp-ui-text-highlight code" readonly="readonly" onfocus="this.select()" /></div>
		</form>
		</div>
	<?php
	}

	public static function get_post_type( $type, $meta ) {
		
		$args = array(
			
			'post_type'      => $type,
			'post_status'    => 'publish',
			'order'          => 'ASC',
			'orderby'        => 'title',
			'posts_per_page' => -1

		);
		
		$q = new WP_Query( $args );

		$output = [];

		while ($q->have_posts()) {

			$q->the_post();

			$id    = get_the_ID();
			$title = get_the_title();
			$value = get_post_meta( $id, $meta, true );

			$output[] = [ $title, $value ];

		}

		return $output;

	}
	
	public static function wpcf7_form_tag( $val ) {
		
		if ( $val['type'] !== 'postselect' ) {
			return $val;
		}
		
		$tag = new WPCF7_Shortcode( $val );

		$post_type = $tag->get_option('post_type', 'class', true);
		$post_meta = $tag->get_option('post_meta', 'class', true);

		$post_data = CF7_PSE_Post_Select::get_post_type($post_type, $post_meta);

		foreach ($post_data as $entry) {
			$val['raw_values'][] = $entry[0].'|'.$entry[1];
		}
		
		if ( WPCF7_USE_PIPE ) {
			$pipes = new WPCF7_Pipes( $val['raw_values'] );
			$val['values'] = $pipes->collect_befores();
			$val['pipes'] = $pipes;
		} else {
			$val['values'] = $val['raw_values'];
		}
				
		return $val;
		
	}

}

////////////////////////////////////////////////////////////////////////////////
// PLUGIN INITIALIZATION
////////////////////////////////////////////////////////////////////////////////

new CF7_PSE_Post_Select;
