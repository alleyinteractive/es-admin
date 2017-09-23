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
class Test_DSL extends ES_Admin_Test_Case {
	public function setUp() {
		parent::setUp();

		$cat_a = $this->factory->term->create( [ 'taxonomy' => 'category', 'name' => 'cat-a' ] );
		$cat_b = $this->factory->term->create( [ 'taxonomy' => 'category', 'name' => 'cat-b' ] );
		$this->factory->post->create( [ 'post_title' => 'tag-נ', 'tags_input' => [ 'tag-נ' ], 'post_date' => '2008-11-01 00:00:00' ] );
		$this->factory->post->create( [ 'post_title' => 'cats-a-and-b', 'post_date' => '2009-01-01 00:00:00', 'post_category' => [ $cat_a, $cat_b ] ] );
		$this->factory->post->create( [ 'post_title' => 'cat-a', 'post_date' => '2009-04-01 00:00:00', 'post_category' => [ $cat_a ] ] );
		$this->factory->post->create( [ 'post_title' => 'cat-b', 'post_date' => '2009-05-01 00:00:00', 'post_category' => [ $cat_b ] ] );
		$this->factory->post->create( [ 'post_title' => 'lorem-ipsum', 'post_date' => '2009-07-01 00:00:00' ] );
		$this->factory->post->create( [ 'post_title' => 'embedded-video', 'post_date' => '2010-01-01 00:00:00' ] );
		$this->factory->post->create( [ 'post_title' => 'tag-a', 'tags_input' => [ 'tag-a' ], 'post_date' => '2010-05-01 00:00:00' ] );
		$this->factory->post->create( [ 'post_title' => 'tag-b', 'tags_input' => [ 'tag-b' ], 'post_date' => '2010-06-01 00:00:00' ] );
		$this->factory->post->create( [ 'post_title' => 'tags-a-and-b', 'tags_input' => [ 'tag-a', 'tag-b' ], 'post_date' => '2010-08-01 00:00:00' ] );

		index_test_data();
	}

	public function test_search_query() {
		$args = $this->base_dsl();
		$args['query'] = DSL::search_query( 'embedded' );
		$this->assertSame( [ 'embedded-video' ], $this->query_and_get_post_titles( $args ) );
	}

	public function terms_data() {
		return [
			[ 'category', 'term_name', 'cat-a', [ 'cat-a', 'cats-a-and-b' ] ],
			[ 'post_tag', 'term_name', 'tag-b', [ 'tag-b', 'tags-a-and-b' ] ],
			[ 'post_tag', 'term_name', 'tag-נ', [ 'tag-נ' ] ],
			[ 'post_tag', 'tag_name', [ 'tag-a', 'tag-b' ], [ 'tag-a', 'tag-b', 'tags-a-and-b' ] ],
		];
	}

	/**
	 * @dataProvider terms_data
	 * @param  string       $taxonomy Taxonomy
	 * @param  string       $field    Field to search
	 * @param  string|array $values   Value(s) for which to search.
	 */
	public function test_terms( $taxonomy, $field, $values, $expected ) {
		$args = $this->base_dsl();
		$args['query']['bool']['filter'] = [
			DSL::terms( $this->es->map_tax_field( $taxonomy, $field ), $values ),
		];

		$this->assertEqualSets( $expected, $this->query_and_get_post_titles( $args ) );
	}

	public function test_range() {
		$args = $this->base_dsl();
		$args['query']['bool']['filter'] = [
			DSL::range( $this->es->map_field( 'post_date' ), [
				'gte' => '2009-07-01 00:00:00',
				'lt' => '2009-07-02 00:00:00',
			] ),
		];
		$this->assertSame(
			[ 'lorem-ipsum' ],
			$this->query_and_get_post_titles( $args )
		);
	}

	public function test_exists() {
		$args = $this->base_dsl();
		$args['query']['bool']['filter'] = [
			DSL::exists( $this->es->map_tax_field( 'post_tag', 'term_name' ) ),
		];
		$this->assertEqualSets(
			[ 'tag-נ', 'tag-a', 'tag-b', 'tags-a-and-b' ],
			$this->query_and_get_post_titles( $args )
		);
	}

	public function test_missing() {
		$args = $this->base_dsl();
		$args['query']['bool']['filter'] = [
			DSL::missing( $this->es->map_tax_field( 'post_tag', 'term_name' ) ),
		];
		$this->assertEqualSets(
			[ 'cats-a-and-b', 'cat-a', 'cat-b', 'lorem-ipsum', 'embedded-video' ],
			$this->query_and_get_post_titles( $args )
		);
	}

	public function test_match() {
		$args = $this->base_dsl();
		$args['query']['bool']['filter'] = [
			DSL::match( $this->es->map_field( 'post_title' ), 'lorem-ipsum' ),
		];
		$this->assertSame(
			[ 'lorem-ipsum' ],
			$this->query_and_get_post_titles( $args )
		);
	}

	public function test_all_terms() {
		$args = $this->base_dsl();
		$args['query']['bool']['filter'] = [
			DSL::all_terms( $this->es->map_tax_field( 'post_tag', 'term_name' ), [ 'tag-a', 'tag-b' ] ),
		];
		$this->assertSame(
			[ 'tags-a-and-b' ],
			$this->query_and_get_post_titles( $args )
		);
	}
}
