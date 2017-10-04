<?php
/**
 * A singular Elasticsearch search.
 *
 * @package ES Admin
 */

namespace ES_Admin;

/**
 * Elasticsearch query.
 */
class Search {
	/**
	 * Elasticsearch DSL arguments.
	 *
	 * @var array
	 */
	public $es_args = [];

	/**
	 * Raw results from ES.
	 *
	 * @var array
	 */
	public $results = [];

	/**
	 * Hits (search results) from ES.
	 *
	 * @var array
	 */
	public $hits = [];

	/**
	 * Facets queried..
	 *
	 * @var array
	 */
	public $facets = [];

	/**
	 * Search results total.
	 *
	 * @var int
	 */
	public $total;

	/**
	 * Build the search.
	 *
	 * @param array $es_args Elasticsearch DSL.
	 * @param array $facets  \ES_Admin\Facets\Facet_Type objects.
	 */
	public function __construct( $es_args = null, array $facets = [] ) {
		if ( $facets ) {
			$this->facets = $facets;
		}
		if ( $es_args ) {
			$this->es_args = $es_args;
			$this->query();
		}
	}

	/**
	 * Run the query.
	 */
	public function query() {
		$es_args = $this->facetized_args();
		// Ensure that query is never empty.
		if ( empty( $es_args['query'] ) ) {
			$es_args['query'] = [
				'match_all' => new \stdClass(),
			];
		}

		/**
		 * Filter the final query before sending to Elasticsearch.
		 *
		 * @param array  $es_args Elasticsearch DSL.
		 * @param Search $search  This object.
		 */
		$es_args = apply_filters( 'es_admin_query', $es_args, $this );
		$this->results = ES::instance()->query( $es_args );

		/**
		 * Filter the query results prior to parsing them.
		 *
		 * @param array  $results Raw Elasticsearch response.
		 * @param array  $es_args Elasticsearch DSL.
		 * @param Search $search  This object.
		 */
		$this->results = apply_filters( 'es_admin_results', $this->results, $es_args, $this );
		$this->parse_hits();
		$this->parse_total();
		$this->parse_facets();
	}

	protected function facetized_args() {
		$es_args = $this->es_args;
		foreach ( $this->facets as $facet ) {
			// Add the aggregation to the query.
			$es_args['aggs'][ $facet->key() ] = $facet->request_dsl();

			// If the facet is being requested, automatically add that to
			// the filters.
			// @todo refactor out $_GET here to make this more flexible. This class
			//       shouldn't care where the 'active' data comes from.
			if ( ! empty( $_GET['facets'][ $facet->query_var() ] ) ) {
				$values = array_map( 'sanitize_text_field', (array) $_GET['facets'][ $facet->query_var() ] ); // WPCS: sanitization ok.
				$es_args['query']['bool']['filter'][] = $facet->filter( $values );
			}
		}

		return $es_args;
	}

	/**
	 * Pull the hits out of the ES response.
	 */
	protected function parse_hits() {
		/**
		 * Allow plugins/themes to short-circuit how the hits get parsed from
		 * the raw Elasticsearch response.
		 *
		 * @param null|array $hits    By default, this is null. If this becomes
		 *                            an array through the filter, default
		 *                            processing is skipped.
		 * @param array      $results Raw Elasticsearch response.
		 * @param Search     $search  This object.
		 */
		$this->hits = apply_filters( 'es_admin_parse_hits', null, $this->results, $this );
		if ( ! is_array( $this->hits ) ) {
			if ( ! empty( $this->results['hits']['hits'] ) ) {
				$this->hits = $this->results['hits']['hits'];
			} else {
				$this->hits = [];
			}
		}
	}

	/**
	 * Pull the total out of the ES response.
	 */
	protected function parse_total() {
		/**
		 * Allow plugins/themes to short-circuit how the hits get parsed from
		 * the raw Elasticsearch response.
		 *
		 * @param null|int $hits    By default, this is null. If this becomes an
		 *                          int through the filter, default processing
		 *                          is skipped.
		 * @param array    $results Raw Elasticsearch response.
		 * @param Search   $search     This object.
		 */
		$this->total = apply_filters( 'es_admin_parse_total', null, $this->results, $this );
		if ( ! is_int( $this->total ) ) {
			// Using isset because 0 is empty but valid.
			if ( isset( $this->results['hits']['total'] ) ) {
				$this->total = absint( $this->results['hits']['total'] );
			}
		}
	}

	/**
	 * Pull the facets out of the ES response.
	 */
	protected function parse_facets() {
		foreach ( $this->facets as $facet ) {
			if ( ! empty( $this->results['aggregations'][ $facet->key() ]['buckets'] ) ) {
				$facet->set_buckets( $this->results['aggregations'][ $facet->key() ]['buckets'] );
			} else {
				$facet->set_buckets( [] );
			}
		}
	}

	/**
	 * Does this search have any results?
	 *
	 * @return boolean
	 */
	public function has_hits() {
		return ! empty( $this->hits );
	}

	/**
	 * Get the search results.
	 *
	 * @return array
	 */
	public function hits() {
		return $this->hits;
	}

	/**
	 * Does this search have any facets?
	 *
	 * @return boolean
	 */
	public function has_facets() {
		return ! empty( $this->facets );
	}

	/**
	 * Get the facet objects.
	 *
	 * @return array
	 */
	public function facets() {
		return $this->facets;
	}

	/**
	 * Did this search generate any facet responses?
	 *
	 * @return boolean True if yes, false if no.
	 */
	public function has_facet_responses() {
		foreach ( $this->facets as $facet ) {
			if ( $facet->has_buckets() ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get the total.
	 *
	 * @return int
	 */
	public function total() {
		return $this->total;
	}
}
