<?php
/**
 * Class Test_Facets_Category.
 *
 * @package ES Admin
 */

namespace ES_Admin;
use ES_Admin\Facets\Category;

/**
 * Category facet test case.
 */
class Test_Facets_Category extends \WP_UnitTestCase {

	public function test_request_dsl() {
		$tax = new Category();
		$this->assertSame( json_decode( '{"terms":{"field":"terms.category.slug"}}', true ), $tax->request_dsl() );
	}

	public function test_filter_and() {
		$tax = new Category();
		$this->assertSame( json_decode( '{"bool":{"filter":[{"term":{"terms.category.slug":"cat-a"}}]}}', true ), $tax->filter( [ 'cat-a' ] ) );
	}

	public function test_filter_or() {
		$tax = new Category( [ 'taxonomy' => 'category', 'logic' => 'or' ] );
		$this->assertSame( json_decode( '{"terms":{"terms.category.slug":["cat-a"]}}', true ), $tax->filter( [ 'cat-a' ] ) );
	}

	public function test_filter_multiple_terms_and() {
		$tax = new Category();
		$this->assertSame( json_decode( '{"bool":{"filter":[{"term":{"terms.category.slug":"cat-a"}},{"term":{"terms.category.slug":"cat-b"}}]}}', true ), $tax->filter( [ 'cat-a', 'cat-b' ] ) );
	}

	public function test_filter_multiple_terms_or() {
		$tax = new Category( [ 'taxonomy' => 'category', 'logic' => 'or' ] );
		$this->assertSame( json_decode( '{"terms":{"terms.category.slug":["cat-a","cat-b"]}}', true ), $tax->filter( [ 'cat-a', 'cat-b' ] ) );
	}

	public function test_bucket_labels() {
		$tax = new Category();
		$term_data = [ 'name' => 'Incredible Category 1', 'slug' => 'my-category-1', 'taxonomy' => 'category' ];
		self::factory()->term->create( $term_data );

		$this->assertSame( $term_data['name'], $tax->bucket_label( [ 'key' => $term_data['slug'] ] ) );
	}

	public function test_bucket_labels_invalid_term() {
		$tax = new Category();
		$term_data = [ 'name' => 'Incredible Category 2', 'slug' => 'my-category-2' ];

		$this->assertSame( $term_data['slug'], $tax->bucket_label( [ 'key' => $term_data['slug'] ] ) );
	}
}
