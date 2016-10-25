<?php
/**
 * Abstract adapter class.
 *
 * @package ES Admin
 */

namespace ES_Admin\Adapters;

/**
 * Abstract adapter class
 */
abstract class Adapter {
	/**
	 * Map core fields to the ES index.
	 *
	 * In addition to what's below, other fields include:
	 * 		post_id
	 *      post_author
	 *      	post_author.user_nicename
	 *      	post_author.display_name
	 * 		post_date
	 * 			post_date.year
	 * 			post_date.month
	 * 			post_date.week
	 * 			post_date.day
	 * 			post_date.day_of_year
	 * 			post_date.day_of_week
	 * 			post_date.hour
	 * 			post_date.minute
	 * 			post_date.second
	 * 		post_date_gmt (plus all the same tokens as post_date)
	 * 		post_content
	 * 			post_content.analyzed
	 * 		post_title
	 * 			post_title.analyzed
	 * 		post_excerpt
	 * 		post_status
	 * 		ping_status
	 * 		post_password
	 * 		post_name
	 * 		post_modified (plus all the same tokens as post_date)
	 * 		post_modified_gmt (plus all the same tokens as post_date)
	 * 		post_parent
	 * 		menu_order
	 * 		post_type
	 * 		post_mime_type
	 * 		comment_count
	 *
	 * @var array
	 */
	protected $field_map = [
		'post_meta'              => 'post_meta.%s',
		'post_meta.analyzed'     => 'post_meta.%s.analyzed',
		'post_meta.long'         => 'post_meta.%s.long',
		'post_meta.double'       => 'post_meta.%s.double',
		'post_meta.binary'       => 'post_meta.%s.boolean',
		'post_meta.date'         => 'post_meta.%s.date',
		'post_meta.datetime'     => 'post_meta.%s.datetime',
		'post_meta.time'         => 'post_meta.%s.time',
		'post_meta.signed'       => 'post_meta.%s.signed',
		'post_meta.unsigned'     => 'post_meta.%s.unsigned',
		'term_id'                => 'terms.%s.term_id',
		'term_slug'              => 'terms.%s.slug',
		'term_name'              => 'terms.%s.name',
		'term_name.analyzed'     => 'terms.%s.name.analyzed',
		'term_tt_id'             => 'terms.%s.term_taxonomy_id',
		'category_id'            => 'terms.%s.term_id',
		'category_slug'          => 'terms.%s.slug',
		'category_name'          => 'terms.%s.name',
		'category_name.analyzed' => 'terms.%s.name.analyzed',
		'category_tt_id'         => 'terms.%s.term_taxonomy_id',
		'tag_id'                 => 'terms.%s.term_id',
		'tag_slug'               => 'terms.%s.slug',
		'tag_name'               => 'terms.%s.name',
		'tag_name.analyzed'      => 'terms.%s.name.analyzed',
		'tag_tt_id'              => 'terms.%s.term_taxonomy_id',
	];

	/**
	 * Run a query against the ES index.
	 *
	 * The adapter is expected to take a PHP array that it will json_encode, and
	 * return a PHP array that has been JSON decoded.
	 *
	 * @param  array $es_args Elasticsearch DSL as a PHP array.
	 * @return array Elasticsearch response as a PHP array.
	 */
	abstract public function query( $es_args );

	/**
	 * Map a core field to the indexed counterpart in Elasticsearch.
	 *
	 * @param  string $field The core field to map.
	 * @return string The mapped field reference.
	 */
	public function map_field( $field ) {
		if ( ! empty( $this->field_map[ $field ] ) ) {
			return $this->field_map[ $field ];
		} else {
			return $field;
		}
	}
}
