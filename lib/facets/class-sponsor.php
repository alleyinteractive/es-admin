<?php
/**
 * Sponsor facet type
 *
 * @package ES Admin
 */

namespace ES_Admin\Facets;

use \ES_Admin\DSL as DSL;

/**
 * Sponsor facet type
 */
class Sponsor extends Facet_Type {
	/**
	 * The query var this facet should use.
	 *
	 * @var string
	 */
	protected $query_var = 'post_sponsor';

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
			'meta_post_sponsor' => [
				'terms' => [
					'field' => $this->es->map_meta_field( 'post_sponsor', 'post_sponsor.value' ),
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
		return DSL::all_terms( $this->es->map_meta_field( 'post_sponsor' ), $values );
	}
}
