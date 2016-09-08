<?php
/**
 * Tag facet type
 *
 * @package ES Admin
 */

namespace ES_Admin\Facets;
use \ES_Admin\DSL as DSL;

/**
 * Tag facet type
 */
class Tag extends Facet_Type {
	protected $query_var = 'post_tag';

	protected $logic = 'and';

	public function request() {
		return [
			'taxonomy_post_tag' => [
				'terms' => [
					'field' => $this->es->map_tax_field( 'post_tag', 'tag_slug' ),
				],
			],
		];
	}

	public function filter( $values ) {
		return DSL::all_terms( $this->es->map_tax_field( 'post_tag', 'tag_slug' ), $values );
	}
}
