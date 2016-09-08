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
	protected $query_var = 'category';

	protected $logic = 'and';

	public function request() {
		return [
			'taxonomy_category' => [
				'terms' => [
					'field' => $this->es->map_tax_field( 'category', 'category_slug' ),
				],
			],
		];
	}

	public function filter( $values ) {
		return DSL::all_terms( $this->es->map_tax_field( 'category', 'category_slug' ), $values );
	}
}
