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
	protected $es_args = [];

	/**
	 * Raw results from ES.
	 *
	 * @var array
	 */
	protected $results = [];

	/**
	 * Hits (search results) from ES.
	 *
	 * @var array
	 */
	protected $hits = [];

	/**
	 * Facets from ES.
	 *
	 * @var array
	 */
	protected $facets = [];

	/**
	 * Search results total.
	 *
	 * @var int
	 */
	protected $total;

	/**
	 * Build the search.
	 *
	 * @param array $es_args Elasticsearch DSL.
	 */
	public function __construct( $es_args = null ) {
		if ( $es_args ) {
			$this->es_args = $es_args;
			$this->query();
		}
	}

	/**
	 * Run the query.
	 */
	protected function query() {
		$this->results = ES::instance()->query( $this->es_args );
		$this->results = apply_filters( 'es_admin_results', $this->results );
		$this->parse_hits();
		$this->parse_total();
		$this->parse_facets();
	}

	/**
	 * Pull the hits out of the ES response.
	 */
	protected function parse_hits() {
		$this->hits = apply_filters( 'es_admin_parse_hits', [] );
		if ( empty( $this->hits ) ) {
			if ( ! empty( $this->results['hits']['hits'] ) ) {
				$this->hits = $this->results['hits']['hits'];
			}
		}
	}

	/**
	 * Pull the total out of the ES response.
	 */
	protected function parse_total() {
		$this->total = apply_filters( 'es_admin_parse_total', null );
		if ( ! isset( $this->total ) ) {
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
		$this->facets = apply_filters( 'es_admin_parse_facets', [] );
		if ( empty( $this->facets ) ) {
			if ( ! empty( $this->results['aggregations'] ) ) {
				foreach ( $this->results['aggregations'] as $label => $buckets ) {
					$this->facets[] = new Facet( $label, $buckets['buckets'] );
				}
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
	 * Get the facets.
	 *
	 * @return array
	 */
	public function facets() {
		return $this->facets;
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
