<?php
/**
 * Class Test_Facets_Post_Type.
 *
 * @package ES Admin
 */

namespace ES_Admin;
use ES_Admin\Facets\Post_Type;

/**
 * Post Type facet test case.
 */
class Test_Facets_Post_Type extends \WP_UnitTestCase {
	public function test_request_dsl() {
		$facet = new Post_Type();
		$this->assertSame( json_decode( '{"terms":{"field":"post_type"}}', true ), $facet->request_dsl() );
	}

	public function test_filter_and() {
		$facet = new Post_Type( [ 'logic' => 'and' ] );
		$this->assertSame( json_decode( '{"bool":{"filter":[{"term":{"post_type":"post-type-a"}}]}}', true ), $facet->filter( [ 'post-type-a' ] ) );
	}

	public function test_filter_or() {
		$facet = new Post_Type();
		$this->assertSame( json_decode( '{"terms":{"post_type":["post-type-a"]}}', true ), $facet->filter( [ 'post-type-a' ] ) );
	}

	public function test_filter_multiple_terms_and() {
		$facet = new Post_Type( [ 'logic' => 'and' ] );
		$this->assertSame( json_decode( '{"bool":{"filter":[{"term":{"post_type":"post-type-a"}},{"term":{"post_type":"post-type-b"}}]}}', true ), $facet->filter( [ 'post-type-a', 'post-type-b' ] ) );
	}

	public function test_filter_multiple_terms_or() {
		$facet = new Post_Type();
		$this->assertSame( json_decode( '{"terms":{"post_type":["post-type-a","post-type-b"]}}', true ), $facet->filter( [ 'post-type-a', 'post-type-b' ] ) );
	}

	public function test_bucket_labels() {
		$facet = new Post_Type();
		$plural = 'Facet Test Plural Name';
		$post_type = 'test-pt-1';
		register_post_type( $post_type, [
			'labels' => [
				'name' => $plural,
				'singular_name' => 'this gets ignored',
			],
			'public' => true,
		] );

		$this->assertSame( $plural, $facet->bucket_label( [ 'key' => $post_type ] ) );
		_unregister_post_type( $post_type );
	}

	public function test_bucket_labels_invalid_post_type() {
		$facet = new Post_Type();
		$post_type = 'test-pt-2';

		$this->assertSame( $post_type, $facet->bucket_label( [ 'key' => $post_type ] ) );
	}
}
