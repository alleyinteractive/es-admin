<?php
/**
 * WordPress.com adapter.
 *
 * @package ES Admin
 */

namespace ES_Admin\Adapters;

/**
 * An adapter for WordPress.com VIP.
 */
class WP_Com extends Adapter {

	/**
	 * Build the object and set the field map.
	 */
	public function __construct() {
		$this->field_map['post_author']                   = 'author_id';
		$this->field_map['post_author.display_name']      = 'author';
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
		$this->field_map['post_password']                 = 'post_password';  // this isn't indexed on wordpress.com.
		$this->field_map['post_name']                     = 'post_name';      // this isn't indexed on wordpress.com.
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
		$this->field_map['menu_order']                    = 'menu_order';     // this isn't indexed on wordpress.com.
		$this->field_map['post_mime_type']                = 'post_mime_type'; // this isn't indexed on wordpress.com.
		$this->field_map['comment_count']                 = 'comment_count';  // this isn't indexed on wordpress.com.
		$this->field_map['post_meta']                     = 'meta.%s.value.raw_lc';
		$this->field_map['post_meta.analyzed']            = 'meta.%s.value';
		$this->field_map['post_meta.long']                = 'meta.%s.long';
		$this->field_map['post_meta.double']              = 'meta.%s.double';
		$this->field_map['post_meta.binary']              = 'meta.%s.boolean';
		$this->field_map['term_id']                       = 'taxonomy.%s.term_id';
		$this->field_map['term_slug']                     = 'taxonomy.%s.slug';
		$this->field_map['term_name']                     = 'taxonomy.%s.name.raw_lc';
		$this->field_map['term_name.analyzed']            = 'taxonomy.%s.name';
		$this->field_map['category_id']                   = 'category.term_id';
		$this->field_map['category_slug']                 = 'category.slug';
		$this->field_map['category_name']                 = 'category.name.raw_lc';
		$this->field_map['category_name.analyzed']        = 'category.name';
		$this->field_map['tag_id']                        = 'tag.term_id';
		$this->field_map['tag_slug']                      = 'tag.slug';
		$this->field_map['tag_name']                      = 'tag.name.raw_lc';
		$this->field_map['tag_name.analyzed']             = 'tag.name';
	}

	/**
	 * Run a query against the ES index.
	 *
	 * @param  array $es_args Elasticsearch DSL as a PHP array.
	 * @return array Elasticsearch response as a PHP array.
	 */
	public function query( $es_args ) {
		if ( function_exists( 'es_api_search_index' ) ) {
			if ( ! empty( $es_args['blog_id'] ) ) {
				$blog_id = absint( $es_args['blog_id'] );
				unset( $es_args['blog_id'] );
			} else {
				$blog_id = get_current_blog_id();
			}

			$es_args['name'] = es_api_get_index_name_by_blog_id( $blog_id );
			if ( is_wp_error( $es_args['name'] ) ) {
				return array();
			}

			/**
			 * Filter the ES args once more, after the site name has been set.
			 *
			 * @var array Elasticsearch DSL query as a PHP array.
			 */
			$es_args = apply_filters( 'es_admin_wp_com_es_args', $es_args );

			$response = es_api_search_index( $es_args, 'es-admin' );
			if ( is_wp_error( $response ) ) {
				return array();
			}

			// Normalize response (ES is hits.hits, wpcom is results.hits; ES is
			// aggregations, wpcom is results.aggregations).
			if ( isset( $response['results'] ) ) {
				$response['hits'] = $response['results'];
				unset( $response['results'] );
				if ( isset( $response['hits']['aggregations'] ) ) {
					$response['aggregations'] = $response['hits']['aggregations'];
					unset( $response['hits']['aggregations'] );
				}
			}

			return $response;
		}
	}
}
