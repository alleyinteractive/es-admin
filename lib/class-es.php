<?php
/**
 * ES integration.
 *
 * @package ES Admin
 */

namespace ES_Admin;

/**
 * Elasticsearch controller
 */
class ES {
	use Singleton;

	/**
	 * The adapter used to query Elasticsearch.
	 *
	 * @var Adapters\Adapter
	 */
	protected $adapter;

	/**
	 * Store the facets from the last search.
	 *
	 * @var array Facet objects
	 */
	protected $facets;

	/**
	 * Store the main (shared) search object.
	 *
	 * @var Search
	 */
	protected $main_search;

	/**
	 * Setup the singleton.
	 *
	 * @throws \Exception If the adapter is invalid.
	 */
	public function setup() {
		$adapter = apply_filters( 'es_admin_adapter', null );
		if ( class_exists( $adapter ) ) {
			$this->adapter = new $adapter();
		}

		if ( ! ( $this->adapter instanceof Adapters\Adapter ) ) {
			throw new \Exception( __( 'Invalid Elasticsearch Adapter', 'es-admin' ) );
		}
	}

	/**
	 * Query the ES server through the adapter.
	 *
	 * @param  array $es_args Elasticsearch DSL.
	 * @return array Elasticsearch response.
	 */
	public function query( $es_args ) {
		return $this->adapter->query( $es_args );
	}

	/**
	 * Map a given field to the Elasticsearch index.
	 *
	 * @param  string $field The field to map.
	 * @return string The mapped field.
	 */
	public function map_field( $field ) {
		return $this->adapter->map_field( $field );
	}

	/**
	 * Map a taxonomy field. This will swap in the taxonomy name.
	 *
	 * @param  string $taxonomy Taxonomy to map.
	 * @param  string $field Field to map.
	 * @return string The mapped field.
	 */
	public function map_tax_field( $taxonomy, $field ) {
		if ( 'post_tag' == $taxonomy ) {
			$field = str_replace( 'term_', 'tag_', $field );
		} elseif ( 'category' == $taxonomy ) {
			$field = str_replace( 'term_', 'category_', $field );
		}
		return sprintf( $this->map_field( $field ), $taxonomy );
	}

	/**
	 * Map a meta field. This will swap in the data type.
	 *
	 * @param  string $meta_key Meta key to map.
	 * @param  string $type Data type to map.
	 * @return string The mapped field.
	 */
	public function map_meta_field( $meta_key, $type = '' ) {
		if ( ! empty( $type ) ) {
			return sprintf( $this->map_field( 'post_meta.' . $type ), $meta_key );
		} else {
			return sprintf( $this->map_field( 'post_meta' ), $meta_key );
		}
	}

	/**
	 * Set the main search for this page, like core's main query.
	 *
	 * @param Search $search Search object to consider the main search.
	 */
	public function set_main_search( $search ) {
		if ( $search instanceof Search ) {
			$this->main_search = $search;
		} else {
			$this->main_search = new Search();
		}
	}

	/**
	 * Get the main search for this screen.
	 *
	 * @return Search
	 */
	public function main_search() {
		if ( ! $this->main_search ) {
			$this->set_main_search( null );
		}

		return $this->main_search;
	}
}
