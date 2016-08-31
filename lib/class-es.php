<?php
namespace ES_Admin;

/**
 * Elasticsearch controller
 */
class ES {
	use Singleton;

	protected $adapter;

	public function setup() {
		$adapter = apply_filters( 'es_admin_adapter', null );
		if ( class_exists( $adapter ) ) {
			$this->adapter = new $adapter;
		}

		if ( ! ( $this->adapter instanceof Adapters\Adapter ) ) {
			throw new \Exception( __( 'Invalid Elasticsearch Adapter', 'es-admin' ) );
		}
	}

	public function query( $es_args ) {
		return $this->adapter->query( $es_args );
	}

	public function map_field( $field ) {
		return $this->adapter->map_field( $field );
	}

	public function map_tax_field( $taxonomy, $field ) {
		if ( 'post_tag' == $taxonomy ) {
			$field = str_replace( 'term_', 'tag_', $field );
		} elseif ( 'category' == $taxonomy ) {
			$field = str_replace( 'term_', 'category_', $field );
		}
		return sprintf( $this->map_field( $field ), $taxonomy );
	}

	public function map_meta_field( $meta_key, $type = '' ) {
		if ( ! empty( $type ) ) {
			return sprintf( $this->map_field( 'post_meta.' . $type ), $meta_key );
		} else {
			return sprintf( $this->map_field( 'post_meta' ), $meta_key );
		}
	}

	public function search_query( $s ) {
		$fields = apply_filters( 'es_admin_searchable_fields', [
			$this->map_field( 'post_title.analyzed' ) . '^3',
			$this->map_field( 'post_excerpt' ),
			$this->map_field( 'post_content.analyzed' ),
			$this->map_field( 'post_author.user_nicename' ),
		] );

		return [
			'multi_match' => [
				'query'    => $s,
				'fields'   => $fields,
				'operator' => 'and',
				'type'     => 'cross_fields',
			],
		];
	}

	public static function dsl_terms( $field, $values, $args = array() ) {
		$type = is_array( $values ) ? 'terms' : 'term';
		return array( $type => array_merge( array( $field => $values ), $args ) );
	}

	public static function dsl_range( $field, $args ) {
		return array( 'range' => array( $field => $args ) );
	}

	public static function dsl_exists( $field ) {
		return array( 'exists' => array( 'field' => $field ) );
	}

	public static function dsl_missing( $field, $args = array() ) {
		return array( 'missing' => array_merge( array( 'field' => $field ), $args ) );
	}

	public static function dsl_match( $field, $value, $args = array() ) {
		return array( 'match' => array_merge( array( $field => $value ), $args ) );
	}

	public static function dsl_multi_match( $fields, $query, $args = array() ) {
		return array( 'multi_match' => array_merge( array( 'query' => $query, 'fields' => (array) $fields ), $args ) );
	}

	public static function dsl_all_terms( $field, $values ) {
		$queries = array();
		foreach ( $values as $value ) {
			$queries[] = array( 'term' => array( $field => $value ) );
		}
		return array( 'bool' => array( 'must' => $queries ) );
	}
}
