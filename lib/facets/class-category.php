<?php
/**
 * Category facet type
 *
 * @package ES Admin
 */

namespace ES_Admin\Facets;
use \ES_Admin\DSL as DSL;

/**
 * Category facet type
 */
class Category extends Facet_Type {
	/**
	 * The query var this facet should use.
	 *
	 * @var string
	 */
	protected $query_var = 'category';

	/**
	 * The logic mode this facet should use. 'and' or 'or'.
	 *
	 * @var string
	 */
	protected $logic = 'and';

	/**
	 * Build the facet request.
	 *
	 * @return array
	 */
	public function request() {
		return [
			'taxonomy_category' => [
				'terms' => [
					'field' => $this->es->map_tax_field( 'category', 'category_slug' ),
				],
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
		return DSL::all_terms( $this->es->map_tax_field( 'category', 'category_slug' ), $values );
	}
}
