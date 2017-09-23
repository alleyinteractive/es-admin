<?php
/**
 * Class Test_DSL
 *
 * @package ES Admin
 */

namespace ES_Admin;

/**
 * DSL test case.
 */
class ES_Admin_Test_Case extends \WP_UnitTestCase {
	protected $es;

	public function setUp() {
		parent::setUp();
		$this->es = ES::instance();
	}

	public function tearDown() {
		$this->es->setup();
		parent::tearDown();
	}

	protected function base_dsl() {
		return [
			'query' => [],
			'_source' => [
				'post_title',
			],
			'from' => 0,
			'size' => 10,
		];
	}

	protected function query_and_get_post_titles( $args ) {
		// Run the search.
		$search = new Search( $args );

		if ( ! $search->has_hits() ) {
			return [];
		}

		// Extract only the post ids.
		$post_titles = array_map( function( $hit ) {
			if ( empty( $hit['_source'][ $this->es->map_field( 'post_title' ) ] ) ) {
				return null;
			}

			$post_title = (array) $hit['_source'][ $this->es->map_field( 'post_title' ) ];
			return reset( $post_title );

		}, $search->hits() );

		// Return the post ids.
		return array_filter( $post_titles );
	}
}
