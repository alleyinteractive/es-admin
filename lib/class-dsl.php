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
class DSL {

	/**
	 * Given a search term, return the query DSL for the search.
	 *
	 * @param  string $s Search term.
	 * @return array DSL fragment.
	 */
	public static function search_query( $s ) {
		$es = ES::instance();

		/**
		 * Filter the Elasticsearch fields to search. The fields should already
		 * be mapped (use `ES::map_field()`, `ES::map_tax_field()`, or
		 * `ES::map_meta_field()` to map a field).
		 *
		 * @var array
		 */
		$fields = apply_filters( 'es_admin_searchable_fields', [
			$es->map_field( 'post_title.analyzed' ) . '^3',
			$es->map_field( 'post_excerpt' ),
			$es->map_field( 'post_content.analyzed' ),
			$es->map_field( 'post_author.display_name' ),
		] );

		return DSL::multi_match( $fields, $s, [
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
	public static function terms( $field, $values, $args = array() ) {
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
	public static function range( $field, $args ) {
		return array( 'range' => array( $field => $args ) );
	}

	/**
	 * Build an exists DSL fragment.
	 *
	 * @param  string $field  ES field.
	 * @return array DSL fragment.
	 */
	public static function exists( $field ) {
		return array( 'exists' => array( 'field' => $field ) );
	}

	/**
	 * Build a missing DSL fragment.
	 *
	 * @param  string $field  ES field.
	 * @param  array  $args Optional. Additional DSL arguments.
	 * @return array DSL fragment.
	 */
	public static function missing( $field, $args = array() ) {
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
	public static function match( $field, $value, $args = array() ) {
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
	public static function multi_match( $fields, $query, $args = array() ) {
		return array( 'multi_match' => array_merge( array( 'query' => $query, 'fields' => (array) $fields ), $args ) );
	}

	/**
	 * Build a "must" bool fragment for an array of terms.
	 *
	 * @param  string $field  ES field.
	 * @param  array  $values  Values to match.
	 * @return array DSL fragment.
	 */
	public static function all_terms( $field, $values ) {
		$queries = array();
		foreach ( $values as $value ) {
			$queries[] = array( 'term' => array( $field => $value ) );
		}
		return array( 'bool' => array( 'must' => $queries ) );
	}
}