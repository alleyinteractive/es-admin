<?php
/**
 * Author facet class.
 *
 * @package ES Admin
 */

namespace ES_Admin\Facets;
use \ES_Admin\DSL as DSL;

/**
 * Author facet type.
 */
class Author extends Facet_Type {
	/**
	 * The query var this facet should use.
	 *
	 * @var string
	 */
	protected $query_var = 'author';

	/**
	 * Build the facet type object.
	 *
	 * @see Facet_Type::__construct().
	 */
	public function __construct( $args = [] ) {
		$args = wp_parse_args( $args, [
			'key' => 'author',
			'title' => __( 'Author', 'es-admin' ),
		] );
		parent::__construct( $args );
	}

	/**
	 * Build the facet request.
	 *
	 * @return array
	 */
	public function request_dsl() {
		return [
			'terms' => [
				'field' => $this->es->map_field( 'post_author' ),
			],
		];
	}

	/**
	 * Customize the bucket label for this facet type.
	 *
	 * @param  string $label  Bucket label.
	 * @param  array  $bucket Bucket from ES.
	 * @return string
	 */
	public function bucket_label( $bucket ) {
		$user = get_user_by( 'id', intval( $bucket['key'] ) );
		if ( $user instanceof \WP_User && ! empty( $user->display_name ) ) {
			return $user->display_name;
		}
		return $bucket['key'];
	}

	/**
	 * Get the request filter DSL clause.
	 *
	 * @param  array $values Values to pass to filter.
	 * @return array
	 */
	public function filter( $values ) {
		$values = array_map( 'intval', $values );
		$field = $this->es->map_field( 'post_author' );
		if ( 'and' === $this->logic() ) {
			return DSL::all_terms( $field, $values );
		} else {
			return DSL::terms( $field, $values );
		}
	}
}
