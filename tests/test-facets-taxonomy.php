<?php
/**
 * Class Test_Facets_Taxonomy.
 *
 * @package ES Admin
 */

namespace ES_Admin;
use ES_Admin\Facets\Taxonomy;

/**
 * Taxonomy facet test case.
 */
class Test_Facets_Taxonomy extends \WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
		register_taxonomy( 'my-custom-tax', 'post' );
	}

	public function tearDown() {
		_unregister_taxonomy( 'my-custom-tax' );
		parent::tearDown();
	}

	/**
	 * @expectedException \ES_Admin\Exception
	 * @expectedExceptionMessage Taxonomy facets must provide a taxonomy
	 */
	public function test_missing_taxonomy() {
		new Taxonomy();
	}

	/**
	 * @expectedException \ES_Admin\Exception
	 * @expectedExceptionMessage Invalid taxonomy invalid-taxonomy used for facet
	 */
	public function test_invalid_taxonomy() {
		new Taxonomy( [ 'taxonomy' => 'invalid-taxonomy' ] );
	}

	public function test_request_dsl() {
		$tax = new Taxonomy( [ 'taxonomy' => 'my-custom-tax' ] );
		$this->assertSame( json_decode( '{"terms":{"field":"terms.my-custom-tax.slug"}}', true ), $tax->request_dsl() );
	}

	public function test_filter_and() {
		$tax = new Taxonomy( [ 'taxonomy' => 'my-custom-tax' ] );
		$this->assertSame( json_decode( '{"bool":{"filter":[{"term":{"terms.my-custom-tax.slug":"term-a"}}]}}', true ), $tax->filter( [ 'term-a' ] ) );
	}

	public function test_filter_or() {
		$tax = new Taxonomy( [ 'taxonomy' => 'my-custom-tax', 'logic' => 'or' ] );
		$this->assertSame( json_decode( '{"terms":{"terms.my-custom-tax.slug":["term-a"]}}', true ), $tax->filter( [ 'term-a' ] ) );
	}

	public function test_filter_multiple_terms_and() {
		$tax = new Taxonomy( [ 'taxonomy' => 'my-custom-tax' ] );
		$this->assertSame( json_decode( '{"bool":{"filter":[{"term":{"terms.my-custom-tax.slug":"term-a"}},{"term":{"terms.my-custom-tax.slug":"term-b"}}]}}', true ), $tax->filter( [ 'term-a', 'term-b' ] ) );
	}

	public function test_filter_multiple_terms_or() {
		$tax = new Taxonomy( [ 'taxonomy' => 'my-custom-tax', 'logic' => 'or' ] );
		$this->assertSame( json_decode( '{"terms":{"terms.my-custom-tax.slug":["term-a","term-b"]}}', true ), $tax->filter( [ 'term-a', 'term-b' ] ) );
	}

	public function test_bucket_labels() {
		$tax = new Taxonomy( [ 'taxonomy' => 'my-custom-tax' ] );
		$term_data = [ 'name' => 'Incredible Custom Term 1', 'slug' => 'my-custom-term-1', 'taxonomy' => 'my-custom-tax' ];
		self::factory()->term->create( $term_data );

		$this->assertSame( $term_data['name'], $tax->bucket_label( [ 'key' => $term_data['slug'] ] ) );
	}

	public function test_bucket_labels_invalid_term() {
		$tax = new Taxonomy( [ 'taxonomy' => 'my-custom-tax' ] );
		$term_data = [ 'name' => 'Incredible Custom Term 2', 'slug' => 'my-custom-term-2' ];

		$this->assertSame( $term_data['slug'], $tax->bucket_label( [ 'key' => $term_data['slug'] ] ) );
	}
}
