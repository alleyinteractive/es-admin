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
		$fields = apply_filters(
			'es_admin_searchable_fields',
			[
				$es->map_field( 'post_title.analyzed' ) . '^3',
				$es->map_field( 'post_excerpt' ),
				$es->map_field( 'post_content.analyzed' ),
				$es->map_field( 'post_author.display_name' ),
				$es->map_meta_field( '_wp_attachment_image_alt', 'analyzed' ),
			]
		);

		return self::multi_match(
			$fields,
			$s,
			[
				'operator' => 'and',
				'type'     => 'cross_fields',
			]
		);
	}

	/**
	 * Build a term or terms DSL fragment.
	 *
	 * @param  string $field  ES field.
	 * @param  mixed  $values Value(s) to query/filter.
	 * @param  array  $args   Optional. Additional DSL arguments.
	 * @return array DSL fragment.
	 */
	public static function terms( $field, $values, $args = [] ) {
		$type = is_array( $values ) ? 'terms' : 'term';

		return [
			$type => array_merge(
				[
					$field => $values,
				],
				$args
			),
		];
	}

	/**
	 * Build a range DSL fragment.
	 *
	 * @param  string $field ES field.
	 * @param  array  $args  Optional. Additional DSL arguments.
	 * @return array  DSL fragment.
	 */
	public static function range( $field, $args ) {
		return [
			'range' => [
				$field => $args,
			],
		];
	}

	/**
	 * Build an exists DSL fragment.
	 *
	 * @param  string $field ES field.
	 * @return array DSL fragment.
	 */
	public static function exists( $field ) {
		return [
			'exists' => [
				'field' => $field,
			],
		];
	}

	/**
	 * Build a missing DSL fragment.
	 *
	 * @param  string $field ES field.
	 * @param  array  $args  Optional. Additional DSL arguments.
	 * @return array DSL fragment.
	 */
	public static function missing( $field, $args = [] ) {
		return [
			'bool' => [
				'must_not' => [
					'exists' => array_merge(
						[
							'field' => $field,
						],
						$args
					),
				],
			],
		];
	}

	/**
	 * Build a match DSL fragment.
	 *
	 * @param  string $field ES field.
	 * @param  string $value Value to match against.
	 * @param  array  $args  Optional. Additional DSL arguments.
	 * @return array DSL fragment.
	 */
	public static function match( $field, $value, $args = [] ) {
		return [
			'match' => array_merge(
				[
					$field => $value,
				],
				$args
			),
		];
	}

	/**
	 * Build a multi_match DSL fragment.
	 *
	 * @param  array  $fields ES fields.
	 * @param  string $query  Search phrase to query.
	 * @param  array  $args   Optional. Additional DSL arguments.
	 * @return array DSL fragment.
	 */
	public static function multi_match( $fields, $query, $args = [] ) {
		return [
			'multi_match' => array_merge(
				[
					'query'  => $query,
					'fields' => (array) $fields,
				],
				$args
			),
		];
	}

	/**
	 * Build a "filter" bool fragment for an array of terms.
	 *
	 * @param  string $field  ES field.
	 * @param  array  $values Values to match.
	 * @return array DSL fragment.
	 */
	public static function all_terms( $field, $values ) {
		$queries = [];
		foreach ( $values as $value ) {
			$queries[] = [
				'term' => [
					$field => $value,
				],
			];
		}

		return [
			'bool' => [
				'filter' => $queries,
			],
		];
	}
}
