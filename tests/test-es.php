<?php
/**
 * Class Test_ES
 *
 * @package ES Admin
 */

namespace ES_Admin;

/**
 * ES test case.
 */
class Test_ES extends ES_Admin_Test_Case {
	/**
	 * @expectedException \ES_Admin\Exception
	 */
	public function test_no_adapter() {
		$this->es->set_adapter( null );
	}

	/**
	 * @requires PHPUnit 5.4
	 */
	public function test_query() {
		$adapter = $this->createMock( '\ES_Admin\Adapters\Generic' );
		$adapter->method( 'query' )
			->willReturn( 'query tested' );

		$this->es->set_adapter( $adapter );
		$this->assertSame( 'query tested', $this->es->query( 123 ) );
	}

	/**
	 * @requires PHPUnit 5.4
	 */
	public function test_map_field() {
		$adapter = $this->createMock( '\ES_Admin\Adapters\Generic' );
		$adapter->method( 'map_field' )
			->willReturn( 'field.mapped' );

		$this->es->set_adapter( $adapter );
		$this->assertSame( 'field.mapped', $this->es->map_field( 'map.it' ) );
	}

	public function data_for_map_tax_field() {
		return [
			[ 'post_tag', 'term_name', 'terms.post_tag.name' ],
			[ 'post_tag', 'tag_name', 'terms.post_tag.name' ],
			[ 'category', 'term_slug', 'terms.category.slug' ],
			[ 'category', 'category_slug', 'terms.category.slug' ],
			[ 'custom_taxonomy', 'term_id', 'terms.custom_taxonomy.term_id' ],
		];
	}

	/**
	 * @dataProvider data_for_map_tax_field
	 * @param  string $taxonomy    Taxonomy.
	 * @param  string $field       Field.
	 * @param  string $expectation Expected result.
	 */
	public function test_map_tax_field( $taxonomy, $field, $expectation ) {
		$this->assertSame( $expectation, $this->es->map_tax_field( $taxonomy, $field ) );
	}

	public function test_map_meta_field() {
		$this->assertSame( 'post_meta._thumbnail_id.value', $this->es->map_meta_field( '_thumbnail_id' ) );
	}

	public function test_map_meta_field_with_type() {
		$this->assertSame( 'post_meta._thumbnail_id.date', $this->es->map_meta_field( '_thumbnail_id', 'date' ) );
	}

	public function test_main_search() {
		$this->assertInstanceOf( '\ES_Admin\Search', $this->es->main_search() );
	}

	/**
	 * @requires PHPUnit 5.4
	 */
	public function test_set_main_search() {
		$search = $this->createMock( '\ES_Admin\Search' );
		$search->method( 'hits' )
			->willReturn( 'search results' );

		$this->es->set_main_search( $search );
		$this->assertSame( 'search results', $this->es->main_search()->hits() );
	}
}
