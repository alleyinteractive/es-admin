<?php
/**
 * A singular Elasticsearch search.
 *
 * @package ES Admin
 */

namespace ES_Admin;

/**
 * Elasticsearch facet/aggregation
 */
class Search {
	protected $es_args = [];

	protected $results = [];

	protected $hits = [];

	protected $facets = [];

	protected $total;

	public function __construct( $es_args = null ) {
		if ( $es_args ) {
			$this->es_args = $es_args;
			$this->query();
		}
	}

	protected function query() {
		$this->results = ES::instance()->query( $this->es_args );
		$this->results = apply_filters( 'es_admin_results', $this->results );
		$this->parse_hits();
		$this->parse_total();
		$this->parse_facets();
	}

	protected function parse_hits() {
		$this->hits = apply_filters( 'es_admin_parse_hits', [] );
		if ( empty( $this->hits ) ) {
			if ( ! empty( $this->results['hits']['hits'] ) ) {
				$this->hits = $this->results['hits']['hits'];
			}
		}
	}

	protected function parse_total() {
		$this->total = apply_filters( 'es_admin_parse_total', null );
		if ( ! isset( $this->total ) ) {
			// Using isset because 0 is empty but valid
			if ( isset( $this->results['hits']['total'] ) ) {
				$this->total = absint( $this->results['hits']['total'] );
			}
		}
	}

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

	public function has_hits() {
		return ! empty( $this->hits );
	}

	public function hits() {
		return $this->hits;
	}

	public function has_facets() {
		return ! empty( $this->facets );
	}

	public function facets() {
		return $this->facets;
	}

	public function total() {
		return $this->total;
	}
}
