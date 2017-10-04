<?php
/**
 * Date Histogram facet type.
 *
 * @package ES Admin
 */

namespace ES_Admin\Facets;
use \ES_Admin\DSL as DSL;

/**
 * Date Histogram facet type
 */
class Date_Histogram extends Facet_Type {

	/**
	 * The field to facet and query against.
	 *
	 * @var string Defaults to post_date but may be overridden via __construct().
	 */
	protected $date_field = 'post_date';

	/**
	 * Build the facet type object.
	 *
	 * @param array $args {
	 *     Arguments for the facet type. In addition to argments defined in
	 *     {@see Facet_Type::__construct()}, this class also accepts:
	 *
	 *     @type string $date_field The date field to facet and query against.
	 *                              Defaults to `post_date`.
	 * }
	 */
	public function __construct( $args = [] ) {
		if ( ! empty( $args['date_field'] ) ) {
			$this->date_field = $args['date_field'];
		}

		$args = wp_parse_args( $args, [
			'key' => 'histogram_' . $this->date_field,
			'title' => __( 'Date', 'es-admin' ),
			'query_var' => $this->date_field,
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
			'date_histogram' => [
				'field' => $this->es->map_field( $this->date_field ),
				'interval' => 'month',
				'format' => 'yyyy-MM',
				'min_doc_count' => 2,
				'order' => [
					'_key' => 'desc',
				],
			],
		];
	}

	/**
	 * Get the request filter DSL clause.
	 *
	 * @param  array $values Values to pass to filter.
	 * @return array
	 */
	public function filter( $values ) {
		$should = [];
		foreach ( $values as $date ) {
			$gte = date( 'Y-m-d H:i:s', $date );
			$lt = date( 'Y-m-d H:i:s', strtotime( date( 'Y-m-d', $date ) . ' + 1 month' ) );
			$should[] = DSL::range( $this->es->map_field( $this->date_field ), [ 'gte' => $gte, 'lt' => $lt ] );
		}

		return array( 'bool' => array( 'should' => $should ) );
	}

	/**
	 * Customize the bucket label for this facet type.
	 *
	 * @param  array  $bucket Bucket from ES.
	 * @return string
	 */
	public function bucket_label( $bucket ) {
		return $bucket['key_as_string'];
	}

	/**
	 * Customize the bucket value for this facet type.
	 *
	 * @param  array  $bucket Bucket from ES.
	 * @return string
	 */
	public function bucket_value( $bucket ) {
		return absint( floor( $bucket['key'] / 1000 ) );
	}

	/**
	 * Checked helper for input checkboxes. Wraps `checked()` and checks $_GET.
	 *
	 * @param  mixed $value Current bucket value.
	 */
	public function checked( $value ) {
		$values = ! empty( $_GET['facets'][ $this->query_var() ] ) ? (array) $_GET['facets'][ $this->query_var() ] : []; // WPCS: sanitization ok.
		checked( in_array( absint( floor( $value / 1000 ) ), $values ) );
	}
}
