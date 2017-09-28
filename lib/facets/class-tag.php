<?php
/**
 * Tag facet type
 *
 * @package ES Admin
 */

namespace ES_Admin\Facets;
use \ES_Admin\DSL as DSL;

/**
 * Tag facet type
 */
class Tag extends Taxonomy {
	/**
	 * Build the facet type object.
	 *
	 * @see Taxonomy::__construct().
	 */
	public function __construct( $args = [] ) {
		$args['taxonomy'] = 'post_tag';
		parent::__construct( $args );
	}
}
