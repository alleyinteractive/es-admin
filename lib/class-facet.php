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
	protected $label;

	protected $buckets;

	protected $title;

	protected $type;

	protected $subtype;

	protected $query_var;

	public function __construct( $label, $buckets ) {
		$this->label = $label;
		$this->buckets = $buckets;
		$this->parse_type();
	}

	protected function parse_type() {
		if ( 'taxonomy_' === substr( $this->label, 0, 9 ) ) {
			$this->type = 'taxonomy';
			$this->subtype = $this->query_var = substr( $this->label, 9 );
		} else {
			$this->type = $this->query_var = $this->label;
		}
	}

	public function field_name() {
		return sprintf( 'facets[%s][]', $this->query_var );
	}

	public function buckets() {
		return $this->buckets;
	}

	public function title() {
		if ( ! isset( $this->title ) ) {
			$this->title = apply_filters( 'es_admin_facet_title', null, $this->label, $this->type, $this->subtype );
			if ( null === $this->title ) {
				switch ( $this->type ) {
					case 'taxonomy' :
						$taxonomy_object = get_taxonomy( $this->subtype );
						if ( ! empty( $taxonomy_object->labels->name ) ) {
							$this->title = $taxonomy_object->labels->name;
						} else {
							$this->title = $this->type;
						}
						break;

					case 'post_type' :
						$this->title = __( 'Content Type', 'es-admin' );
						break;

					case 'post_date' :
						$this->title = __( 'Date', 'es-admin' );
						break;

					case 'post_author' :
						$this->title = __( 'Author', 'es-admin' );
						break;

					default :
						$this->title = $this->label;
						break;
				}
			}
		}

		return $this->title;
	}

	public function get_label_for_bucket( $bucket ) {
		// Allow theme/other plugins to override this
		$label = apply_filters( 'es_admin_facet_bucket_label', null, $bucket, $this->label, $this->type, $this->subtype );
		if ( null !== $label ) {
			return $label;
		}

		if ( isset( $bucket['key_as_string'] ) ) {
			return $bucket['key_as_string'];
		} else {
			switch ( $this->type ) {
				case 'taxonomy' :
					$get_term_by = ( function_exists( 'wpcom_vip_get_term_by' ) ? 'wpcom_vip_get_term_by' : 'get_term_by' );
					$term = call_user_func( $get_term_by, 'slug', $bucket['key'], $this->subtype );
					if ( ! empty( $term->name ) ) {
						return $term->name;
					}
					break;

				case 'post_type' :
					$post_type_obj = get_post_type_object( $bucket['key'] );
					if ( ! empty( $post_type_obj->labels->name ) ) {
						return $post_type_obj->labels->name;
					}
					break;

				case 'post_date' :
					if ( is_numeric( $bucket['key'] ) ) {
						return date( 'Y-m-d', absint( $bucket['key'] ) / 1000 );
					}
					break;

				case 'post_author' :
					$name = get_the_author_meta( 'display_name', absint( $bucket['key'] ) );
					if ( $name ) {
						return $name;
					}
					break;
			}

			return $bucket['key'];
		}
	}

	public function field_value( $value ) {
		if ( 'post_date' === $this->type ) {
			return absint( $value ) / 1000;
		}
		return $value;
	}

	public function checked( $value ) {
		if ( 'post_date' === $this->type ) {
			$value = absint( $value ) / 1000;
		}
		$values = ! empty( $_GET['facets'][ $this->query_var ] ) ? (array) $_GET['facets'][ $this->query_var ] : [];
		checked( in_array( $value, $values ) );
	}
}
