<?php
/**
 * Class Test_Facets_Author.
 *
 * @package ES Admin
 */

namespace ES_Admin;
use ES_Admin\Facets\Author;

/**
 * Post Type facet test case.
 */
class Test_Facets_Author extends \WP_UnitTestCase {
	public function test_request_dsl() {
		$facet = new Author();
		$this->assertSame( json_decode( '{"terms":{"field":"post_author.user_id"}}', true ), $facet->request_dsl() );
	}

	public function test_filter_and() {
		$facet = new Author( [ 'logic' => 'and' ] );
		$this->assertSame( json_decode( '{"bool":{"filter":[{"term":{"post_author.user_id":123}}]}}', true ), $facet->filter( [ 123 ] ) );
	}

	public function test_filter_or() {
		$facet = new Author();
		$this->assertSame( json_decode( '{"terms":{"post_author.user_id":[123]}}', true ), $facet->filter( [ 123 ] ) );
	}

	public function test_filter_multiple_terms_and() {
		$facet = new Author( [ 'logic' => 'and' ] );
		$this->assertSame( json_decode( '{"bool":{"filter":[{"term":{"post_author.user_id":123}},{"term":{"post_author.user_id":789}}]}}', true ), $facet->filter( [ 123, 789 ] ) );
	}

	public function test_filter_multiple_terms_or() {
		$facet = new Author();
		$this->assertSame( json_decode( '{"terms":{"post_author.user_id":[123,789]}}', true ), $facet->filter( [ 123, 789 ] ) );
	}

	public function test_bucket_labels() {
		$display_name = 'test display name';
		$user_id = self::factory()->user->create( [
			'user_nicename' => 'test nicename',
			'display_name' => $display_name,
		] );
		$facet = new Author();
		$this->assertSame( $display_name, $facet->bucket_label( [ 'key' => $user_id ] ) );
	}

	public function test_bucket_labels_invalid_post_type() {
		$facet = new Author();
		$this->assertSame( 654, $facet->bucket_label( [ 'key' => 654 ] ) );
	}
}
