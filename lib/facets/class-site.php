<?php
/**
 * Site facet type
 *
 * @package ES Admin
 */

namespace ES_Admin\Facets;
use \ES_Admin\DSL as DSL;

/**
 * Site facet type
 */
class Site extends Facet_Type {
	/**
	 * The query var this facet should use.
	 *
	 * @var string
	 */
	protected $query_var = 'site';

	/**
	 * The logic mode this facet should use. 'and' or 'or'.
	 *
	 * @var string
	 */
	protected $logic = 'or';

	/**
	 * Build the facet request.
	 *
	 * @return array
	 */
	public function request() {
		return [
			'blog_id' => [
				'terms' => [
					'field' => 'blog_id',
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
		return DSL::terms( $this->es->map_field( 'blog_id' ), $values );
	}
}
