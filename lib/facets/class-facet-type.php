<?php
/**
 * Facet types abstract class
 *
 * @package ES Admin
 */

namespace ES_Admin\Facets;
use \ES_Admin\ES as ES;

/**
 * Facet types abstract class
 */
abstract class Facet_Type {
	/**
	 * The logic mode this facet should use. 'and' or 'or'.
	 *
	 * @var string
	 */
	protected $logic = 'or';

	/**
	 * The query var this facet should use.
	 *
	 * @var string
	 */
	protected $query_var;

	/**
	 * A reference to the \ES_Admin\ES singleton.
	 *
	 * @var ES
	 */
	protected $es;

	/**
	 * Build the facet type object.
	 */
	public function __construct() {
		$this->es = ES::instance();
	}

	/**
	 * Build the facet request.
	 *
	 * @return array
	 */
	abstract public function request();

	/**
	 * Get the request filter DSL clause.
	 *
	 * @param  array $values Values to pass to filter.
	 * @return array
	 */
	abstract public function filter( $values );

	/**
	 * Get the logic mode for this facet.
	 *
	 * @return string 'and' or 'or'.
	 */
	public function logic() {
		return $this->logic;
	}

	/**
	 * Get the query var for this facet.
	 *
	 * @return string
	 */
	public function query_var() {
		return $this->query_var;
	}
}
