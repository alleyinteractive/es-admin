<?php
/**
 * Jetpack Search Network adapter.
 *
 * @package ES Admin
 */

namespace ES_Admin\Adapters;

/**
 * An adapter for Jetpack Search Network.
 */
class Jetpack_Search_Network extends Adapter {

	/**
	 * Build the object and set the field map.
	 */
	public function __construct() {

		// Modify the response so it's in a format ES Admin understands.
		add_filter( 'es_admin_results', [ $this, 'filter_es_admin_results' ] );

		$this->field_map['post_author']                   = 'author_id';
		$this->field_map['post_author.user_nicename']     = 'author_login';
		$this->field_map['post_date']                     = 'date';
		$this->field_map['post_date.year']                = 'date_token.year';
		$this->field_map['post_date.month']               = 'date_token.month';
		$this->field_map['post_date.week']                = 'date_token.week';
		$this->field_map['post_date.day']                 = 'date_token.day';
		$this->field_map['post_date.day_of_year']         = 'date_token.day_of_year';
		$this->field_map['post_date.day_of_week']         = 'date_token.day_of_week';
		$this->field_map['post_date.hour']                = 'date_token.hour';
		$this->field_map['post_date.minute']              = 'date_token.minute';
		$this->field_map['post_date.second']              = 'date_token.second';
		$this->field_map['post_date_gmt']                 = 'date_gmt';
		$this->field_map['post_date_gmt.year']            = 'date_gmt_token.year';
		$this->field_map['post_date_gmt.month']           = 'date_gmt_token.month';
		$this->field_map['post_date_gmt.week']            = 'date_gmt_token.week';
		$this->field_map['post_date_gmt.day']             = 'date_gmt_token.day';
		$this->field_map['post_date_gmt.day_of_year']     = 'date_gmt_token.day_of_year';
		$this->field_map['post_date_gmt.day_of_week']     = 'date_gmt_token.day_of_week';
		$this->field_map['post_date_gmt.hour']            = 'date_gmt_token.hour';
		$this->field_map['post_date_gmt.minute']          = 'date_gmt_token.minute';
		$this->field_map['post_date_gmt.second']          = 'date_gmt_token.second';
		$this->field_map['post_content']                  = 'content';
		$this->field_map['post_content.analyzed']         = 'content';
		$this->field_map['post_title']                    = 'title';
		$this->field_map['post_title.analyzed']           = 'title';
		$this->field_map['post_excerpt']                  = 'excerpt';
		$this->field_map['post_password']                 = 'post_password';  // This isn't indexed on VIP.
		$this->field_map['post_name']                     = 'post_name';      // This isn't indexed on VIP.
		$this->field_map['post_modified']                 = 'modified';
		$this->field_map['post_modified.year']            = 'modified_token.year';
		$this->field_map['post_modified.month']           = 'modified_token.month';
		$this->field_map['post_modified.week']            = 'modified_token.week';
		$this->field_map['post_modified.day']             = 'modified_token.day';
		$this->field_map['post_modified.day_of_year']     = 'modified_token.day_of_year';
		$this->field_map['post_modified.day_of_week']     = 'modified_token.day_of_week';
		$this->field_map['post_modified.hour']            = 'modified_token.hour';
		$this->field_map['post_modified.minute']          = 'modified_token.minute';
		$this->field_map['post_modified.second']          = 'modified_token.second';
		$this->field_map['post_modified_gmt']             = 'modified_gmt';
		$this->field_map['post_modified_gmt.year']        = 'modified_gmt_token.year';
		$this->field_map['post_modified_gmt.month']       = 'modified_gmt_token.month';
		$this->field_map['post_modified_gmt.week']        = 'modified_gmt_token.week';
		$this->field_map['post_modified_gmt.day']         = 'modified_gmt_token.day';
		$this->field_map['post_modified_gmt.day_of_year'] = 'modified_gmt_token.day_of_year';
		$this->field_map['post_modified_gmt.day_of_week'] = 'modified_gmt_token.day_of_week';
		$this->field_map['post_modified_gmt.hour']        = 'modified_gmt_token.hour';
		$this->field_map['post_modified_gmt.minute']      = 'modified_gmt_token.minute';
		$this->field_map['post_modified_gmt.second']      = 'modified_gmt_token.second';
		$this->field_map['post_parent']                   = 'parent_post_id';
		$this->field_map['menu_order']                    = 'menu_order';     // This isn't indexed on VIP.
		$this->field_map['post_mime_type']                = 'post_mime_type'; // This isn't indexed on VIP.
		$this->field_map['comment_count']                 = 'comment_count';  // This isn't indexed on VIP.
		$this->field_map['post_meta']                     = 'meta.%s.value.raw_lc';
		$this->field_map['post_meta.analyzed']            = 'meta.%s.value';
		$this->field_map['post_meta.long']                = 'meta.%s.long';
		$this->field_map['post_meta.double']              = 'meta.%s.double';
		$this->field_map['post_meta.binary']              = 'meta.%s.boolean';
		$this->field_map['term_id']                       = 'taxonomy.%s.term_id';
		$this->field_map['term_slug']                     = 'taxonomy.%s.slug';
		$this->field_map['term_name']                     = 'taxonomy.%s.name.raw_lc';
		$this->field_map['category_id']                   = 'category.term_id';
		$this->field_map['category_slug']                 = 'category.slug';
		$this->field_map['category_name']                 = 'category.name.raw';
		$this->field_map['tag_id']                        = 'tag.term_id';
		$this->field_map['tag_slug']                      = 'tag.slug';
		$this->field_map['tag_name']                      = 'tag.name.raw';
	}

	/**
	 * Filter the ES Admin result so it matches what ES Admin expects.
	 *
	 * @param array $results Query results.
	 * @return array
	 */
	public function filter_es_admin_results( $results ) {

		// Nest the hits one more level.
		$results['results']['hits'] = [
			'hits' => $results['results']['hits'],
		];

		// Duplicate the fields to _source.
		foreach ( $results['results']['hits']['hits'] as &$hit ) {
			if ( isset( $hit['fields'] ) && empty( $hit['_source'] ) ) {
				$hit['_source'] = $hit['fields'];
			}
		}

		// Return results that are actually a level deep.
		return $results['results'];
	}

	/**
	 * Run a query against the ES index.
	 *
	 * @param array $es_args Elasticsearch DSL as a PHP array.
	 * @return array Elasticsearch response as a PHP array.
	 */
	public function query( $es_args ) {
		$jetpack_options = get_option( 'jetpack_options' );
		if ( is_array( $jetpack_options ) ) {
			$blog_id = $jetpack_options['id'];
		}

		$additional_blog_ids = array_filter(
			sanitize_text_field( $_GET['additional_blog_ids'] ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			function( $id ) {
				return is_numeric( $id );
			}
		);

		if ( empty( $additional_blog_ids ) ) {
			$req      = new \WP_REST_Request( \WP_REST_Server::READABLE, '/nbc/v1/sites' );
			$response = ( new \SML\REST_Client() )->request_to_data( $req );

			$additional_blog_ids = wp_list_pluck( $response, 'jetpack_blog_id' );
			$additional_blog_ids = array_diff( $additional_blog_ids, [ $blog_id ] );
		}

		$es_args['blog_id']             = $blog_id;
		$es_args['additional_blog_ids'] = array_filter( $additional_blog_ids );
		$es_args['fields']              = [ 'post_id', 'blog_id' ];
		$es_args['aggregations']        = $es_args['aggs'];
		unset( $es_args['aggs'] );
		$bool = $es_args['query']['bool'];
		if ( array_key_exists( 'must', $bool ) ) {
			$es_args['query']['bool']['must'][0]['multi_match']['fields'] = [
				'title.en^0.1',
				'excerpt.en^0.1',
				'content.en^0.1',
				'post_author.display_name.en^0.1',
				'meta._wp_attachment_image_alt.value.en^0.1',
			];
		}

		if ( class_exists( '\Jetpack_Search' ) ) {
			$jetpack_search = \Jetpack_Search::instance();
			if ( method_exists( $jetpack_search, 'search' ) ) {
				$results = $jetpack_search->search( $es_args );
				if ( is_wp_error( $results ) ) {
					printf(
						'<div style="color: red">%s</div>',
						esc_html( $results['message'] )
					);
					return wp_json_encode(
						'{
							"results": {
								"total": 0,
								"max_score": null,
								"hits": []
							},
							"took": 0
						}'
					);
				}
				return $results;
			}
		} else {
			// local testing - for use when Jetpack not available.
			$endpoint    = sprintf( '/sites/%s/search', $blog_id );
			$service_url = 'https://public-api.wordpress.com/rest/v1' . $endpoint;

			unset( $es_args['authenticated_request'] );

			$request_args = array(
				'headers'    => array(
					'Content-Type' => 'application/json',
				),
				'timeout'    => 3,
				'user-agent' => 'jetpack_search',
			);

			$request_body = wp_json_encode( $es_args );

			$request_args = array_merge(
				$request_args,
				array(
					'body' => $request_body,
				)
			);

			$request = wp_remote_post( $service_url, $request_args );

			if ( is_wp_error( $request ) ) {
				return $request;
			}

			$response = json_decode( wp_remote_retrieve_body( $request ), true );
			return $response;
			// end local testing - for use when Jetpack not available.
		}
		return [];
	}
}
