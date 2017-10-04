<?php
/**
 * Class Test_Facets.
 *
 * @package ES Admin
 */

namespace ES_Admin;

/**
 * Facets test case.
 */
class Test_Facets extends \WP_UnitTestCase {
	public function test_titles() {
		$old_title = 'a title';
		$new_title = 'this is the new title';
		$facet = new Mock_Facet( [ 'title' => $old_title ] );
		$this->assertSame( $old_title, $facet->title() );
		$facet->set_title( $new_title );
		$this->assertSame( $new_title, $facet->title() );
	}

	public function test_bucket_label() {
		$facet = new Mock_Facet();
		$this->assertSame( 'foo', $facet->bucket_label( [ 'key' => 'foo' ] ) );
	}

	public function test_bucket_value() {
		$facet = new Mock_Facet();
		$this->assertSame( 'bar', $facet->bucket_value( [ 'key' => 'bar' ] ) );
	}

	public function test_checked() {
		$key = 'my-facet-1';
		$_GET['facets'][ $key ] = 'value-1';
		$result = get_echo( function() use ( $key ) {
			$facet = new Mock_Facet( [ 'key' => $key ] );
			$facet->checked( 'value-1' );
		} );
		$this->assertContains( 'checked', $result );
	}

	public function test_unchecked() {
		$key = 'my-facet-2';
		$_GET['facets'][ $key ] = 'value-2';
		$result = get_echo( function() use ( $key ) {
			$facet = new Mock_Facet( [ 'key' => $key ] );
			$facet->checked( 'value-1' );
		} );
		$this->assertNotContains( 'checked', $result );
	}

	public function test_the_ui() {
		$key = 'my-facet-3';
		$facet = new Mock_Facet( [ 'key' => $key, 'title' => 'ui demo title' ] );
		$facet->set_buckets( [
			[ 'key' => 'a1', 'doc_count' => 9 ],
			[ 'key' => 'b2', 'doc_count' => 6 ],
			[ 'key' => 'c3', 'doc_count' => 3 ],
		] );
		$html = get_echo( [ $facet, 'the_ui' ] );
		$this->assertSame( 3, substr_count( $html, '<li>' ) );
		$this->assertSame( 3, substr_count( $html, '<input type="checkbox" name="facets[' . $key . '][]"' ) );
		$this->assertContains( '<h3>ui demo title</h3>', $html );
		$this->assertRegExp( '/value="a1".*<span class="es-facet-count">\(9\).*value="b2".*<span class="es-facet-count">\(6\).*value="c3".*<span class="es-facet-count">\(3\)/is', $html );
	}
}
