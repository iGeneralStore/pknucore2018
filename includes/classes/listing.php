<?php

namespace CASE27\Classes;

use \CASE27\Classes\Conditions\Conditions;
use \CASE27\Integrations\ListingTypes\Designer;
use \CASE27\Integrations\ListingTypes\ListingType;

class Listing {
	private $data, $categories;
	public $schedule, $type = null;

	public function __construct( \WP_Post $post ) {
		$this->data = $post;
		$this->schedule = new WorkHours( (array) get_post_meta( $this->data->ID, '_work_hours', true ) );

		if ( $listing_type = ( get_page_by_path( $post->_case27_listing_type, OBJECT, 'case27_listing_type' ) ) ) {
			$this->type = new ListingType( $listing_type );
		}
	}

	public function get_id() {
		return $this->data->ID;
	}

	public function get_name() {
		return $this->data->post_title;
	}

	public function get_slug() {
		return $this->data->post_name;
	}

	public function get_data( $key = null ) {
		if ( $key ) {
			if ( isset( $this->data->$key ) ) {
				return $this->data->$key;
			}

			return null;
		}

		return $this->data;
	}

	public function get_link() {
		return get_permalink( $this->data );
	}

	public function get_schedule() {
		return $this->schedule;
	}

	public function get_type() {
		return $this->type;
	}

	public function get_field( $key ) {
		if ( ! $this->type ) {
			return false;
		}

		if ( ! ( $field = $this->type->get_field( $key ) ) ) {
			return false;
		}

		$conditions = new Conditions( $field, $this->data );

		if ( ! $conditions->passes() ) {
			return false;
		}

		return $this->get_field_value( $field );
	}

	public function get_field_value( $field ) {
		if ( in_array( $field['type'], [ 'term-checklist', 'term-select', 'term-multiselect' ] ) ) {
    		$value = array_filter( (array) wp_get_object_terms(
    			$this->get_id(), $field['taxonomy'],
    			[ 'orderby' => 'term_order', 'order' => 'ASC' ])
    		);

    		if ( is_wp_error( $value ) ) {
    			$value = [];
    		}
		} elseif ( isset( $this->data->{$field['slug']} ) ) {
			$value = $this->data->{$field['slug']};
		} elseif ( isset( $this->data->{'_' . $field['slug']} ) ) {
			$value = $this->data->{'_' . $field['slug']};
		} else {
			$value = '';
		}

		if ( is_serialized( $value ) ) {
			$value = unserialize( $value );
		}

		return $value;
	}

	/**
	 * Replace field tags with the actual field value.
	 * Example items to be replaced: [[tagline]] [[description]] [[twitter-id]]
	 *
	 * @since 1.5.0
	 * @param string $string String to replace values into.
	 * @return string
	 */
	public function compile_string( $string ) {
		preg_match_all('/\[\[+(?P<fields>.*?)\]\]/', $string, $matches);

		if ( empty( $matches['fields'] ) ) {
			return $string;
		}

		// Get all field values.
		$fields = [];
		foreach ( array_unique( $matches['fields'] ) as $slug ) {
			$fields[ $slug ] = '';

			if ( ( $value = $this->get_field( $slug ) ) ) {
				if ( is_array( $value ) ) {
					$value = join( ', ', $value );
				}

				// Escape square brackets so any shortcode added by the listing owner won't be run.
				$fields[ $slug ] = str_replace( [ "[" , "]" ] , [ "&#91;" , "&#93;" ] , $value );
			}
		}

		// Replace tags with field values.
		foreach ( $fields as $slug => $value ) {

			// If any of the used fields are empty, return false.
			if ( ! $value ) {
				return false;
			}

			$string = str_replace( "[[$slug]]", esc_attr( $value ), $string );
		}

		// Preserve line breaks.
		return $string;
	}

	/**
	 * Replace [[field]] with the field value in a string.
	 *
	 * @since 1.5.1
	 * @param string $string to replace [[field]] from.
	 * @param string $value that will replace [[field]].
	 * @return string
	 */
	public function compile_field_string( $string, $value ) {
		$string = str_replace( '[[field]]', c27()->esc_shortcodes( esc_attr( $value ) ), $string );

		return do_shortcode( $string );
	}

	public function get_preview_options() {
		// Get the preview template options for the listing type of the current listing.
		$options = $this->type ? $this->type->get_preview_options() : [];

   		// Merge with the default options, in case the listing type options meta returns null.
		return c27()->merge_options([
	        'template' => 'alternate',
	        'background' => [
	            'type' => 'gallery',
	        ],
	        'buttons' => [],
	        'info_fields' => [],
	        'footer' => [
	            'sections' => [],
	        ],
	    ], $options);
	}
}