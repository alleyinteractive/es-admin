<?php
/**
 * Facet helper.
 *
 * @package ES Admin
 */

namespace ES_Admin;

/**
 * Elasticsearch facet/aggregation
 */
class Facet {
	/**
	 * The label for this facet, as provided by ES.
	 *
	 * @var string
	 */
	protected $label;

	/**
	 * This facet's buckets (results).
	 *
	 * @var array
	 */
	protected $buckets;

	/**
	 * The label for this facet section.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * The parsed type from the facet label.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The parsed subtype from the facet label, if applicable.
	 *
	 * @var string
	 */
	protected $subtype;

	/**
	 * The query var this facet.
	 *
	 * @var string
	 */
	protected $query_var;

	/**
	 * Build this facet object.
	 *
	 * @param string $label   The label as provided by ES.
	 * @param array  $buckets The buckets/results for the facet.
	 */
	public function __construct( $label, $buckets ) {
		$this->label = $label;
		$this->buckets = $buckets;
		$this->parse_type();
	}

	/**
	 * Parse the type (and subtype) for this facet.
	 */
	protected function parse_type() {
		if ( 'taxonomy_' === substr( $this->label, 0, 9 ) ) {
			$this->type = 'taxonomy';
			$this->subtype = $this->query_var = substr( $this->label, 9 );
		} else {
			$this->type = $this->query_var = $this->label;
		}
	}

	/**
	 * Get the field (checkbox) name for this facet.
	 *
	 * @return string
	 */
	public function field_name() {
		return sprintf( 'facets[%s][]', $this->query_var );
	}

	/**
	 * Get the buckets for this facet.
	 *
	 * @return array
	 */
	public function buckets() {
		return $this->buckets;
	}

	/**
	 * Does this facet have any results?
	 *
	 * @return boolean
	 */
	public function has_buckets() {
		return ! empty( $this->buckets );
	}

	/**
	 * Get the title for this facet section.
	 *
	 * @return string
	 */
	public function title() {
		if ( ! isset( $this->title ) ) {
			$this->title = apply_filters( 'es_admin_facet_title', null, $this->label, $this->type, $this->subtype );
			if ( null === $this->title ) {
				switch ( $this->type ) {
					case 'taxonomy':
						$taxonomy_object = get_taxonomy( $this->subtype );
						if ( ! empty( $taxonomy_object->labels->name ) ) {
							$this->title = $taxonomy_object->labels->name;
						} else {
							$this->title = $this->type;
						}
						break;

					case 'post_type':
						$this->title = __( 'Content Type', 'es-admin' );
						break;

					case 'post_date':
						$this->title = __( 'Date', 'es-admin' );
						break;

					case 'post_author':
						$this->title = __( 'Author', 'es-admin' );
						break;

					default:
						$this->title = $this->label;
						break;
				}
			}
		}

		return $this->title;
	}

	/**
	 * Get the label for an individual bucket.
	 *
	 * @param  array $bucket Bucket from ES.
	 * @return string
	 */
	public function get_label_for_bucket( $bucket ) {
		$label = apply_filters( 'es_admin_facet_bucket_label', null, $bucket, $this->label, $this->type, $this->subtype );
		if ( null !== $label ) {
			return $label;
		}

		if ( isset( $bucket['key_as_string'] ) ) {
			return $bucket['key_as_string'];
		} else {
			switch ( $this->type ) {
				case 'taxonomy':
					$get_term_by = ( function_exists( 'wpcom_vip_get_term_by' ) ? 'wpcom_vip_get_term_by' : 'get_term_by' );
					$term = call_user_func( $get_term_by, 'slug', $bucket['key'], $this->subtype );
					if ( ! empty( $term->name ) ) {
						return $term->name;
					}
					break;

				case 'post_type':
					$post_type_obj = get_post_type_object( $bucket['key'] );
					if ( ! empty( $post_type_obj->labels->name ) ) {
						return $post_type_obj->labels->name;
					}
					break;

				case 'post_date':
					if ( is_numeric( $bucket['key'] ) ) {
						return date( 'Y-m-d', absint( $bucket['key'] ) / 1000 );
					}
					break;

				case 'post_author':
					$name = get_the_author_meta( 'display_name', absint( $bucket['key'] ) );
					if ( $name ) {
						return $name;
					}
					break;
			}

			return $bucket['key'];
		}
	}

	/**
	 * Get the formatted field value.
	 *
	 * @param  mixed $value Raw value.
	 * @return mixed Formatted value.
	 */
	public function field_value( $value ) {
		if ( 'post_date' === $this->type ) {
			return absint( $value ) / 1000;
		}
		return $value;
	}

	/**
	 * Checked helper for the input checkbox. Wraps `checked()` and checks $_GET
	 * to keep the template clean.
	 *
	 * @param  mixed $value Current bucket value.
	 */
	public function checked( $value ) {
		if ( 'post_date' === $this->type ) {
			$value = absint( $value ) / 1000;
		}
		$values = ! empty( $_GET['facets'][ $this->query_var ] ) ? (array) $_GET['facets'][ $this->query_var ] : []; // WPCS: sanitization ok.
		checked( in_array( $value, $values ) );
	}
}
