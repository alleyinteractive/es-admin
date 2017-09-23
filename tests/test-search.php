<?php
/**
 * Class Test_Search
 *
 * @package ES Admin
 */

namespace ES_Admin;

/**
 * Search test case.
 */
class Test_Search extends ES_Admin_Test_Case {
	public function setUp() {
		parent::setUp();

		$this->factory->post->create( [ 'post_title' => 'lorem-ipsum', 'post_date' => '2009-07-01 00:00:00' ] );
		$this->factory->post->create( [ 'post_title' => 'embedded-video', 'post_date' => '2010-01-01 00:00:00' ] );
		$this->factory->post->create( [ 'post_title' => 'tag-a', 'tags_input' => [ 'tag-a' ], 'post_date' => '2010-05-01 00:00:00' ] );
		$this->factory->post->create( [ 'post_title' => 'tag-b', 'tags_input' => [ 'tag-b' ], 'post_date' => '2010-06-01 00:00:00' ] );
		$this->factory->post->create( [ 'post_title' => 'tags-a-and-b', 'tags_input' => [ 'tag-a', 'tag-b' ], 'post_date' => '2010-08-01 00:00:00' ] );

		index_test_data();
	}

	public function test_basic_search() {
		$dsl = $this->base_dsl();
		$dsl['query']['term']['post_title'] = 'embedded-video';
		$search = new Search( $dsl );
		$this->assertTrue( $search->has_hits() );
		$this->assertFalse( $search->has_facets() );
		$this->assertSame( 1, $search->total() );
		$hits = $search->hits();
		$this->assertSame( [ 'embedded-video' ], (array) $hits[0]['_source']['post_title'] );
	}

	public function test_facets() {
		$dsl = $this->base_dsl();
		$dsl['query']['match_all'] = new \stdClass();
		$dsl['aggregations'] = [
			'taxonomy_post_tag' => [
				'terms' => [
					'field' => $this->es->map_tax_field( 'post_tag', 'tag_slug' ),
				],
			],
		];
		$search = new Search( $dsl );

		$this->assertTrue( $search->has_facets() );

		$facets = $search->facets();
		$this->assertCount( 1, $facets );
		$this->assertTrue( $facets[0]->has_buckets() );

		$buckets = $facets[0]->buckets();
		$this->assertCount( 2, $buckets );
		$this->assertEqualSets( [ 'tag-a', 'tag-b' ], array_column( $buckets, 'key' ) );
		$this->assertSame( [ 2, 2 ], array_column( $buckets, 'doc_count' ) );
	}
}
