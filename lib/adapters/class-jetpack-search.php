<?php
/**
 * Jetpack Search adapter.
 *
 * @package ES Admin
 */

namespace ES_Admin\Adapters;

/**
 * An adapter for Jetpack Search.
 */
class Jetpack_Search extends Adapter {

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
		if ( class_exists( '\Jetpack_Search' ) ) {
			$jetpack_search = \Jetpack_Search::instance();
			if ( method_exists( $jetpack_search, 'search' ) ) {
				return $jetpack_search->search( $es_args );
			}
		}
		return [];
	}
}
