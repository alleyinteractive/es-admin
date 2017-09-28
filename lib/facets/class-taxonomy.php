<?php
/**
 * Taxonomy facet type
 *
 * @package ES Admin
 */

namespace ES_Admin\Facets;
use \ES_Admin\DSL as DSL;

/**
 * Taxonomy facet type
 */
class Taxonomy extends Facet_Type {

	/**
	 * The logic mode this facet should use. 'and' or 'or'.
	 *
	 * @var string
	 */
	protected $logic = 'and';

	/**
	 * Taxonomy slug.
	 *
	 * @var string
	 */
	protected $taxonomy;

	/**
	 * Taxonomy object.
	 *
	 * @var \WP_Taxonomy
	 */
	protected $taxonomy_object;

	/**
	 * Build the facet type object.
	 *
	 * @param array $args {
	 *     Arguments for the facet type. In addition to argments defined in
	 *     {@see Facet_Type::__construct()}, this class also accepts:
	 *
	 *     @type string $taxonomy The taxonomy slug for this facet type.
	 * }
	 */
	public function __construct( $args = [] ) {
		if ( ! isset( $args['taxonomy'] ) ) {
			throw new \ES_Admin\Exception( 'Taxonomy facets must provide a taxonomy' );
		}

		$this->taxonomy = $args['taxonomy'];
		$this->taxonomy_object = get_taxonomy( $this->taxonomy );
		if ( ! $this->taxonomy_object ) {
			throw new \ES_Admin\Exception( "Invalid taxonomy {$this->taxonomy} used for facet" );
		}

		$args = wp_parse_args( $args, [
			'title' => $this->taxonomy_object->labels->name,
			'query_var' => $this->taxonomy_object->query_var,
			'key' => "taxonomy_{$this->taxonomy}",
		] );

		parent::__construct( $args );
	}

	/**
	 * Build the facet request.
	 *
	 * @return array
	 */
	public function request_dsl() {
		return [
			'terms' => [
				'field' => $this->es->map_tax_field( $this->taxonomy, 'term_slug' ),
			],
		];
	}

	/**
	 * Get the request filter DSL clause.
	 *
	 * @param  array $values Values to pass to filter.
	 * @return array
	 */
	public function filter( $values ) {
		return DSL::all_terms( $this->es->map_tax_field( $this->taxonomy, 'term_slug' ), $values );
	}

	/**
	 * Customize the bucket label for this facet type.
	 *
	 * @param  string $label  Bucket label.
	 * @param  array  $bucket Bucket from ES.
	 * @return string
	 */
	protected function customize_bucket_label( $label, $bucket ) {
		$term = get_term_by( 'slug', $bucket['key'], $this->taxonomy );
		if ( ! empty( $term->name ) ) {
			return $term->name;
		}
		return $label;
	}
}
