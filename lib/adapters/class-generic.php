<?php
/**
 * Generic adapter.
 *
 * @package ES Admin
 */

namespace ES_Admin\Adapters;

/**
 * A generic ES implementation for Travis CI
 */
class Generic extends Adapter {

	/**
	 * Build the object and set the field map.
	 */
	public function __construct() {
		$this->field_map['post_meta']         = 'post_meta.%s.value';
		$this->field_map['post_author']       = 'post_author.user_id';
		$this->field_map['post_date']         = 'post_date.date';
		$this->field_map['post_date_gmt']     = 'post_date_gmt.date';
		$this->field_map['post_modified']     = 'post_modified.date';
		$this->field_map['post_modified_gmt'] = 'post_modified_gmt.date';
	}

	/**
	 * Run a query against the ES index.
	 *
	 * @param  array $es_args Elasticsearch DSL as a PHP array.
	 * @return array Elasticsearch response as a PHP array.
	 */
	public function query( $es_args ) {
		$response = wp_remote_post(
			'http://localhost:9200/es-wp-query-unit-tests/post/_search',
			[
				'body'    => wp_json_encode( $es_args ),
				'headers' => [
					'Content-Type' => 'application/json',
				],
			]
		);

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}
}
