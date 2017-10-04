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
		$this->assertFalse( $search->has_facet_responses() );
		$this->assertSame( 1, $search->total() );
		$hits = $search->hits();
		$this->assertSame( [ 'embedded-video' ], (array) $hits[0]['_source']['post_title'] );
	}

	public function test_facets() {
		$dsl = $this->base_dsl();
		$search = new Search( $dsl, [ new Facets\Tag() ] );

		$this->assertTrue( $search->has_facets() );
		$this->assertTrue( $search->has_facet_responses() );

		$facets = $search->facets();
		$this->assertCount( 1, $facets );
		$this->assertTrue( $facets[0]->has_buckets() );

		$buckets = $facets[0]->buckets();
		$this->assertCount( 2, $buckets );
		$this->assertEqualSets( [ 'tag-a', 'tag-b' ], array_column( $buckets, 'key' ) );
		$this->assertSame( [ 2, 2 ], array_column( $buckets, 'doc_count' ) );
	}

	public function test_facetizing_args() {
		$search = new Search_Spy();
		$dsl = $this->base_dsl();

		$facetized_args = $search->test_facetized_args( $dsl, [ new Facets\Tag() ] );

		// Confirm that Search::$es_args didn't change.
		$this->assertSame( $dsl, $search->es_args );

		// Filters shouldn't have been modified.
		$this->assertTrue( empty( $search->es_args['query']['bool']['filter'] ) );
		$this->assertTrue( empty( $facetized_args['query']['bool']['filter'] ) );

		// Aggs should have been modified.
		$this->assertTrue( empty( $search->es_args['aggs'] ) );
		$this->assertFalse( empty( $facetized_args['aggs'] ) );

		// The only change should have been to aggs, so remove that and confirm.
		unset( $facetized_args['aggs'] );
		$this->assertSame( $dsl, $facetized_args );
	}

	public function test_facetizing_args_filtered() {
		$_GET = [ 'facets' => [ 'tag' => 'tag-a' ] ];
		$search = new Search_Spy();
		$dsl = $this->base_dsl();

		$facetized_args = $search->test_facetized_args( $dsl, [ new Facets\Tag() ] );

		// Confirm that Search::$es_args didn't change.
		$this->assertSame( $dsl, $search->es_args );

		// Filters should have been modified because of $_GET.
		$this->assertTrue( empty( $search->es_args['query']['bool']['filter'] ) );
		$this->assertFalse( empty( $facetized_args['query']['bool']['filter'] ) );

		// Aggs should have been modified.
		$this->assertTrue( empty( $search->es_args['aggs'] ) );
		$this->assertFalse( empty( $facetized_args['aggs'] ) );

		// The only changes should have been to aggs and filters, so remove
		// them and confirm.
		unset( $facetized_args['aggs'], $facetized_args['query']['bool'] );
		$this->assertSame( $dsl, $facetized_args );
	}

	public function test_no_query() {
		$dsl = $this->base_dsl();
		$search = new Search( $dsl );

		$this->assertTrue( $search->has_hits() );
		$this->assertFalse( $search->has_facets() );
		$this->assertFalse( $search->has_facet_responses() );
		$this->assertSame( 5, $search->total() );
		$this->assertTrue( empty( $search->es_args['query'] ) );
	}

	public function test_no_results() {
		$dsl = $this->base_dsl();
		$dsl['query']['term']['post_title'] = 'zzzzzzzzzzzz';
		$search = new Search( $dsl );

		$this->assertFalse( $search->has_hits() );
		$this->assertFalse( $search->has_facets() );
		$this->assertFalse( $search->has_facet_responses() );
		$this->assertSame( 0, $search->total() );
	}
}
