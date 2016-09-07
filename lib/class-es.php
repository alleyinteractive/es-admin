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
	 * Setup the singleton.
	 *
	 * @throws \Exception If the adapter is invalid.
	 */
	public function setup() {
		$adapter = apply_filters( 'es_admin_adapter', null );
		if ( class_exists( $adapter ) ) {
			$this->adapter = new $adapter;
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
	 * Given a search term, return the query DSL for the search.
	 *
	 * @param  string $s Search term.
	 * @return array DSL fragment.
	 */
	public function search_query( $s ) {
		/**
		 * Filter the Elasticsearch fields to search. The fields should already
		 * be mapped (use `ES::map_field()`, `ES::map_tax_field()`, or
		 * `ES::map_meta_field()` to map a field).
		 *
		 * @var array
		 */
		$fields = apply_filters( 'es_admin_searchable_fields', [
			$this->map_field( 'post_title.analyzed' ) . '^3',
			$this->map_field( 'post_excerpt' ),
			$this->map_field( 'post_content.analyzed' ),
			$this->map_field( 'post_author.display_name' ),
		] );

		return $this->dsl_multi_match( $fields, $s, [
			'operator' => 'and',
			'type'     => 'cross_fields',
		] );
	}

	/**
	 * Build a term or terms DSL fragment.
	 *
	 * @param  string $field  ES field.
	 * @param  mixed  $values Value(s) to query/filter.
	 * @param  array  $args Optional. Additional DSL arguments.
	 * @return array DSL fragment.
	 */
	public static function dsl_terms( $field, $values, $args = array() ) {
		$type = is_array( $values ) ? 'terms' : 'term';
		return array( $type => array_merge( array( $field => $values ), $args ) );
	}

	/**
	 * Build a range DSL fragment.
	 *
	 * @param  string $field  ES field.
	 * @param  array  $args Optional. Additional DSL arguments.
	 * @return array  DSL fragment.
	 */
	public static function dsl_range( $field, $args ) {
		return array( 'range' => array( $field => $args ) );
	}

	/**
	 * Build an exists DSL fragment.
	 *
	 * @param  string $field  ES field.
	 * @return array DSL fragment.
	 */
	public static function dsl_exists( $field ) {
		return array( 'exists' => array( 'field' => $field ) );
	}

	/**
	 * Build a missing DSL fragment.
	 *
	 * @param  string $field  ES field.
	 * @param  array  $args Optional. Additional DSL arguments.
	 * @return array DSL fragment.
	 */
	public static function dsl_missing( $field, $args = array() ) {
		return array( 'missing' => array_merge( array( 'field' => $field ), $args ) );
	}

	/**
	 * Build a match DSL fragment.
	 *
	 * @param  string $field  ES field.
	 * @param  string $value  Value to match against.
	 * @param  array  $args Optional. Additional DSL arguments.
	 * @return array DSL fragment.
	 */
	public static function dsl_match( $field, $value, $args = array() ) {
		return array( 'match' => array_merge( array( $field => $value ), $args ) );
	}

	/**
	 * Build a multi_match DSL fragment.
	 *
	 * @param  array  $fields ES fields.
	 * @param  string $query Search phrase to query.
	 * @param  array  $args Optional. Additional DSL arguments.
	 * @return array DSL fragment.
	 */
	public static function dsl_multi_match( $fields, $query, $args = array() ) {
		return array( 'multi_match' => array_merge( array( 'query' => $query, 'fields' => (array) $fields ), $args ) );
	}

	/**
	 * Build a "must" bool fragment for an array of terms.
	 *
	 * @param  string $field  ES field.
	 * @param  array  $values  Values to match.
	 * @return array DSL fragment.
	 */
	public static function dsl_all_terms( $field, $values ) {
		$queries = array();
		foreach ( $values as $value ) {
			$queries[] = array( 'term' => array( $field => $value ) );
		}
		return array( 'bool' => array( 'must' => $queries ) );
	}
}
