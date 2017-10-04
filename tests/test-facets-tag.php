<?php
/**
 * Class Test_Facets_Tag.
 *
 * @package ES Admin
 */

namespace ES_Admin;
use ES_Admin\Facets\Tag;

/**
 * Tag facet test case.
 */
class Test_Facets_Tag extends \WP_UnitTestCase {

	public function test_request_dsl() {
		$tax = new Tag();
		$this->assertSame( json_decode( '{"terms":{"field":"terms.post_tag.slug"}}', true ), $tax->request_dsl() );
	}

	public function test_filter_and() {
		$tax = new Tag();
		$this->assertSame( json_decode( '{"bool":{"filter":[{"term":{"terms.post_tag.slug":"tag-a"}}]}}', true ), $tax->filter( [ 'tag-a' ] ) );
	}

	public function test_filter_or() {
		$tax = new Tag( [ 'taxonomy' => 'post_tag', 'logic' => 'or' ] );
		$this->assertSame( json_decode( '{"terms":{"terms.post_tag.slug":["tag-a"]}}', true ), $tax->filter( [ 'tag-a' ] ) );
	}

	public function test_filter_multiple_terms_and() {
		$tax = new Tag();
		$this->assertSame( json_decode( '{"bool":{"filter":[{"term":{"terms.post_tag.slug":"tag-a"}},{"term":{"terms.post_tag.slug":"tag-b"}}]}}', true ), $tax->filter( [ 'tag-a', 'tag-b' ] ) );
	}

	public function test_filter_multiple_terms_or() {
		$tax = new Tag( [ 'taxonomy' => 'post_tag', 'logic' => 'or' ] );
		$this->assertSame( json_decode( '{"terms":{"terms.post_tag.slug":["tag-a","tag-b"]}}', true ), $tax->filter( [ 'tag-a', 'tag-b' ] ) );
	}

	public function test_bucket_labels() {
		$tax = new Tag();
		$term_data = [ 'name' => 'Incredible Tag 1', 'slug' => 'my-tag-1', 'taxonomy' => 'post_tag' ];
		self::factory()->term->create( $term_data );

		$this->assertSame( $term_data['name'], $tax->bucket_label( [ 'key' => $term_data['slug'] ] ) );
	}

	public function test_bucket_labels_invalid_term() {
		$tax = new Tag();
		$term_data = [ 'name' => 'Incredible Tag 2', 'slug' => 'my-tag-2' ];

		$this->assertSame( $term_data['slug'], $tax->bucket_label( [ 'key' => $term_data['slug'] ] ) );
	}
}
