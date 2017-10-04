<?php
/**
 * Class Test_Facets_Date_Histogram.
 *
 * @package ES Admin
 */

namespace ES_Admin;
use ES_Admin\Facets\Date_Histogram;

/**
 * Date Histogram facet test case.
 */
class Test_Facets_Date_Histogram extends \WP_UnitTestCase {
	public function test_request_dsl() {
		$facet = new Date_Histogram();
		$this->assertSame( json_decode( '{"date_histogram":{"field":"post_date.date","interval":"month","format":"yyyy-MM","min_doc_count":2,"order":{"_key":"desc"}}}', true ), $facet->request_dsl() );
	}

	public function test_filter() {
		$facet = new Date_Histogram();
		$this->assertSame( json_decode( '{"bool":{"should":[{"range":{"post_date.date":{"gte":"2017-02-01 00:00:00","lt":"2017-03-01 00:00:00"}}}]}}', true ), $facet->filter( [ strtotime( '2017-02-01' ) ] ) );
	}

	public function test_custom_date_field_request_dsl() {
		$facet = new Date_Histogram( [ 'date_field' => 'post_modified_gmt' ] );
		$this->assertSame( json_decode( '{"date_histogram":{"field":"post_modified_gmt.date","interval":"month","format":"yyyy-MM","min_doc_count":2,"order":{"_key":"desc"}}}', true ), $facet->request_dsl() );
	}

	public function test_custom_date_field_filter() {
		$facet = new Date_Histogram( [ 'date_field' => 'post_modified_gmt' ] );
		$this->assertSame( json_decode( '{"bool":{"should":[{"range":{"post_modified_gmt.date":{"gte":"2017-02-01 00:00:00","lt":"2017-03-01 00:00:00"}}}]}}', true ), $facet->filter( [ strtotime( '2017-02-01' ) ] ) );
	}

	public function test_bucket_labels() {
		$facet = new Date_Histogram();
		$this->assertSame( '2017-03', $facet->bucket_label( [ 'key' => strtotime( '2017-03-01' ) * 1000, 'key_as_string' => '2017-03' ] ) );
	}

	public function test_bucket_value() {
		$facet = new Date_Histogram();
		$this->assertSame( 1491050096, $facet->bucket_value( [ 'key' => '1491050096123' ] ) );
	}

	public function test_checked() {
		$_GET['facets']['post_date'] = '1493596800';
		$result = get_echo( function() {
			$facet = new Date_Histogram();
			$facet->checked( '1493596800234' );
		} );
		$this->assertContains( 'checked', $result );
	}

	public function test_unchecked() {
		$_GET['facets']['post_date'] = '1496275200';
		$result = get_echo( function() {
			$facet = new Date_Histogram();
			$facet->checked( '1493596800234' );
		} );
		$this->assertNotContains( 'checked', $result );
	}
}
