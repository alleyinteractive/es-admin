<?php
/**
 * Category facet type
 *
 * @package ES Admin
 */

namespace ES_Admin\Facets;
use \ES_Admin\DSL as DSL;

/**
 * Category facet type
 */
class Category extends Taxonomy {
	/**
	 * Build the facet type object.
	 *
	 * @see Taxonomy::__construct().
	 */
	public function __construct( $args = [] ) {
		$args['taxonomy'] = 'category';
		parent::__construct( $args );
	}
}
