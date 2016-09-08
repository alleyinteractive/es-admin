<?php
/**
 * Post date facet type
 *
 * @package ES Admin
 */

namespace ES_Admin\Facets;
use \ES_Admin\DSL as DSL;

/**
 * Post date facet type
 */
class Post_Date extends Facet_Type {
	protected $query_var = 'post_date';

	public function request() {
		return [
			'post_date' => [
				'date_histogram' => [
					'field' => $this->es->map_field( 'post_date' ),
					'interval' => 'month',
					'format' => 'yyyy-MM',
					'min_doc_count' => 2,
					'order' => [
						'_key' => 'desc',
					],
				],
			],
		];
	}

	public function filter( $values ) {
		$should = [];
		foreach ( $values as $date ) {
			$gte = date( 'Y-m-d H:i:s', $date );
			$lt = date( 'Y-m-d H:i:s', strtotime( date( 'Y-m-d', $date ) . ' + 1 month' ) );
			$should[] = DSL::range( $this->es->map_field( 'post_date' ), [ 'gte' => $gte, 'lt' => $lt ] );
		}

		return array( 'bool' => array( 'should' => $should ) );
	}
}
