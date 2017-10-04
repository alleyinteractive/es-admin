<?php
/**
 * Mock Facet Type.
 *
 * @package ES Admin
 */

namespace ES_Admin;

/**
 * A generic Facet.
 */
class Mock_Facet extends \ES_Admin\Facets\Facet_Type {
	/**
	 * Build the facet request.
	 *
	 * @return array
	 */
	public function request_dsl() {
		return [
			'terms' => [
				'field' => 'foo',
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
		return [
			'term' => [
				'foo' => 'bar',
			],
		];
	}
}
