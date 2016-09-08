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
	protected $logic = 'or';

	protected $query_var;

	protected $es;

	public function __construct() {
		$this->es = ES::instance();
	}

	abstract public function request();

	abstract public function filter( $values );

	public function logic() {
		return $this->logic;
	}

	public function query_var() {
		return $this->query_var;
	}
}
