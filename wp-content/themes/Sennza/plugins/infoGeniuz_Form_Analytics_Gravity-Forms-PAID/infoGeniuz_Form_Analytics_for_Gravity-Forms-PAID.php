<?php
/*
Plugin Name: infoGeniuz Form Analytics for Gravity Forms - PAID
Plugin URI: http://www.infogeniuz.com/products/wp-plugins/
Description: Enhance Gravity Forms plugin with hidden data that can now be added to each and every lead filling out your online form using Gravity Forms. PAID version adds more data points and analytics!
Author: Lance Brown & Nathan Briggs
Version: 2.0.4
Author URI: http://www.infogeniuz.com

=== RELEASE NOTES ===
2011-09-08 - v1.0 - first version
2012-05-19 - v2.0 - update to GF 1.6.4, API build to GF, add iGz results to entries table in GF, manual shortcodes for notification emails
*/


class NB_InfoGeniuz_GravityForms {
	protected $nb_infogeniuz_fields = array(
		'Geographic location' => array(
			'igz_g_city' => 'City',
			'igz_g_state' => 'State',
			'igz_g_country' => 'Country',
			'igz_g_latitude' => 'Latitude',
			'igz_g_longitude' => 'Longitude'
		),
		'Time data' => array(
			'igz_t_original' => 'Original Visit Time',
			'igz_t_previous' => 'Previous Visit Time',
			'igz_t_current' => 'Current Visit Time',
			'igz_t_time' => 'Submission Time'
		),
		'Analytics data' => array(
			'igz_a_source' => 'Source',
			'igz_a_medium' => 'Medium',
			'igz_a_term' => 'Term',
			'igz_a_content' => 'Content', 
			'igz_a_campaign' => 'Campaign',
			'igz_a_segment' => 'Segment', 
			'igz_a_pageviews' => 'Pageviews',
			'igz_a_visits' => 'Visits', 
		),
		'Device data' => array(
			'igz_h_browser' => 'Browser', 
			'igz_h_brversion' => 'Browser Version',
			'igz_h_os' => 'Operating System'
		)
	);
	
	public function __construct() {
		add_action( 'gform_entry_created', array( &$this, 'nb_gform_post_submission' ) );
		add_action( 'gform_entry_detail', array( &$this, 'add_to_details' ), 10, 2 );
		add_filter( 'gform_get_form_filter', array( &$this, 'nb_gform_get_form_filter' ) );
		add_filter( 'gform_custom_merge_tags', array( &$this, 'nb_custom_merge_tags' ), 10, 4 );
		add_filter( 'gform_replace_merge_tags', array( &$this, 'nb_replace_merge_tags' ), 10, 7 );

	}

	function nb_gform_get_form_filter( $form_string ) {
		$gform_footer = strpos( $form_string, 'gform_footer' );
		$form_footerpos = strpos( substr( $form_string, $gform_footer ), '>' ) + $gform_footer;
		
		$before_footer = substr( $form_string, 0, $form_footerpos+1 );
		$the_footer_etc = substr( $form_string, $form_footerpos+1 );
		$the_footer_etc ='<input type="hidden" name="igz_a_source" id="igz_a_source" value="1555"/>
					<input type="hidden" name="igz_a_medium" id="igz_a_medium"/>
					<input type="hidden" name="igz_a_term" id="igz_a_term"/>
					<input type="hidden" name="igz_a_content" id="igz_a_content"/>
					<input type="hidden" name="igz_a_campaign" id="igz_a_campaign"/>
					<input type="hidden" name="igz_a_segment" id="igz_a_segment"/>
					<input type="hidden" name="igz_a_pageviews" id="igz_a_pageviews"/>
					<input type="hidden" name="igz_a_visits" id="igz_a_visits"/>
					<input type="hidden" name="igz_t_original" id="igz_t_original"/>
					<input type="hidden" name="igz_t_previous" id="igz_t_previous"/>
					<input type="hidden" name="igz_t_current" id="igz_t_current"/>
					<input type="hidden" name="igz_t_time" id="igz_t_time" />
					<input type="hidden" name="igz_h_browser" id="igz_h_browser" />
					<input type="hidden" name="igz_h_os" id="igz_h_os" value=""/>	
					<input type="hidden" name="igz_h_brversion" id="igz_h_brversion" />
					<input type="hidden" name="igz_g_city" id="igz_g_city" value="default"/>
					<input type="hidden" name="igz_g_state" id="igz_g_state" />
					<input type="hidden" name="igz_g_country" id="igz_g_country" />	
					<input type="hidden" name="igz_g_latitude" id="igz_g_latitude"/>				
					<input type="hidden" name="igz_g_longitude" id="igz_g_longitude" />' . $the_footer_etc . '<script type="text/javascript" src="http://j.maxmind.com/app/geoip.js"></script>';
		
		$script = 
		'<script type="text/javascript" src="'	. plugins_url( 'infogeniuz.js', __FILE__ ) . '"></script>';

		return $before_footer . $script . $the_footer_etc;
	}

	function nb_gform_post_submission($entry) {
		// add response value to entry meta
		foreach( $this->nb_infogeniuz_fields as $field_group ) {
			foreach( $field_group as $field_key => $field_name ) {
				gform_update_meta( $entry['id'], $field_key, esc_attr( $_POST[ $field_key ] ) );
			}
		}
	}
	
	function add_to_details($form, $lead)
	{
		echo "<table cellspacing='0' class='widefat fixed entry-detail-view'><thead><tr><th id='infogeniuz' colspan='2'>infoGeniuz</th></thead>\n";
		echo "<tbody>\n";
		foreach( $this->nb_infogeniuz_fields as $group_name => $field_group ) {
			echo "<tr><td colspan='2'><table cellspacing='0' class='widefat fixed'><thead><tr><th colspan='2'>{$group_name}</th></tr></thead>\n<tbody>\n";
			foreach( $field_group as $field_key => $field_name ) {
				echo "<tr><th>{$field_name}</th><td>".gform_get_meta( $lead[ 'id' ], $field_key ) ."</td></tr>\n";
			}		
			echo "</tbody>\n</table>\n";
		}
		echo "</tbody>\n</table>\n";
	}


	function nb_custom_merge_tags($merge_tags, $form_id, $fields, $element_id) {
		$merge_tags[] = array( 'label' => 'infoGeniuz: all fields', 'tag' => '{infogeniuz_all}' );
		
		foreach( $this->nb_infogeniuz_fields as $field_group ) {
			foreach( $field_group as $field_key => $field_name ) {
				$name_parts = explode( '_', $field_key ); // Note: REQUIRES field names of the form igz_A_A
				$merge_tags[] = array( 'label' => "infoGeniuz: $field_name", 'tag' => '{infogeniuz_'.$name_parts[2].'}' );
			}
		}
		
		return $merge_tags;
	}

	function nb_replace_merge_tags($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) { 
		foreach( $this->nb_infogeniuz_fields as $field_group ) {
			foreach( $field_group as $field_key => $field_name ) {
				$name_parts = explode( '_', $field_key ); // Note: REQUIRES field names of the form igz_A_A
				$merge_tag = '{infogeniuz_'.$name_parts[2].'}';
				
				$merge_value = gform_get_meta( $entry['id'], $field_key );
				
				$text = str_replace($merge_tag, $merge_value, $text);
			}
		}
		
		//infogeniuz_all
		if( strpos( $text, '{infogeniuz_all}' ) !== false ) {
			$text = $this->nb_infogeniuz_alltags( $text, $entry[ 'id' ], $format );
		}
		
		return $text;
	}

	function nb_infogeniuz_alltags( $text, $entryid, $format ) {
		$metas = array();
		foreach( $this->nb_infogeniuz_fields as $group => $fields ) {
			foreach( $fields as $key => $name ) {
				$metas[ $name ] = gform_get_meta( $entryid, $key );
			}
		}
		if( $format == 'html' ) {
			$to_replace = "<h3>infoGeniuz data</h3><table width='99%' border='0' cellpadding='1' bgcolor='#EAEAEA'><tr><td><table width='100%' border='0' cellpadding='5' bgcolor='#FFFFFF'>\n<tbody>\n";
		}
		foreach( $metas as $name => $value ) {
			if( $format == 'html' ) {
				$to_replace .= "<tr bgcolor='#EAF2FA'>
	<td colspan='2'>
					<font style='font-family:sans-serif;font-size:12px'><strong>{$name}</strong></font>
				</td>
			</tr>
			<tr bgcolor='#FFFFFF'>
				<td width='20'>&nbsp;</td>
				<td>
					<font style='font-family:sans-serif;font-size:12px'>{$value}</font>
				</td>
			</tr>";
			} else {
				$to_replace .= "{$name}: {$value}\n";
			}
		}
		if( $format == 'html' ) {
			$to_replace .= "</tbody>\n</table></td></tr></table>\n\n";
		}		
		return str_replace( '{infogeniuz_all}', $to_replace, $text);
	}


}//end class NB_InfoGeniuz_GravityForms


$nb_infogeniuz_gravityforms = new NB_InfoGeniuz_GravityForms();
