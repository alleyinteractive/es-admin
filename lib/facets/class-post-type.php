<?php
/**
 * Post type facet type
 *
 * @package ES Admin
 */

namespace ES_Admin\Facets;
use \ES_Admin\DSL as DSL;

/**
 * Post type facet type
 */
class Post_Type extends Facet_Type {
	/**
	 * The query var this facet should use.
	 *
	 * @var string
	 */
	protected $query_var = 'post_type';

	/**
	 * Build the facet request.
	 *
	 * @return array
	 */
	public function request() {
		return [
			'post_type' => [
				'terms' => [
					'field' => $this->es->map_field( 'post_type' ),
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
		return DSL::terms( $this->es->map_field( 'post_type' ), $values );
	}
}
