<?php
/**
 * Mock adapter.
 *
 * @package ES Admin
 */

namespace ES_Admin;

/**
 * A generic ES implementation for Travis CI
 */
class Mock_Adapter extends \ES_Admin\Adapters\Adapter {
	/**
	 * This will be returned from Mock_Adapter::query().
	 *
	 * @var array
	 */
	public $test_expected_response = [];

	/**
	 * Stores the most recent set of $es_args passed to Mock_Adapter::query().
	 *
	 * @var array
	 */
	public $test_last_query_args;

	/**
	 * Build the object and set the field map.
	 */
	public function __construct() {
	}

	/**
	 * Run a query against the ES index.
	 *
	 * @param  array $es_args Elasticsearch DSL as a PHP array.
	 * @return array Elasticsearch response as a PHP array.
	 */
	public function query( $es_args ) {
		$this->test_last_query_args = $es_args;
		return $this->expected_response;
	}
}
