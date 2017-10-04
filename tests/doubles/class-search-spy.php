<?php
/**
 * Search class spy.
 *
 * @package ES Admin
 */

namespace ES_Admin;

/**
 * A Search class extension for testing.
 */
class Search_Spy extends Search {

	/**
	 * Add ability to test the facetized_args method.
	 *
	 * @param array $es_args Elasticsearch DSL.
	 * @param array $facets  \ES_Admin\Facets\Facet_Type objects.
	 * @return array Elasticsearch DSL with aggregations added if applicable.
	 */
	public function test_facetized_args( array $es_args, array $facets ) {
		$this->es_args = $es_args;
		$this->facets = $facets;
		return $this->facetized_args();
	}
}
