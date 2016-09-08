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
	protected $query_var = 'post_type';

	public function request() {
		return [
			'post_type' => [
				'terms' => [
					'field' => $this->es->map_field( 'post_type' ),
				],
			],
		];
	}

	public function filter( $values ) {
		return DSL::terms( $this->es->map_field( 'post_type' ), $values );
	}
}
