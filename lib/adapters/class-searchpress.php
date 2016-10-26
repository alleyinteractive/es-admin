<?php
/**
 * SearchPress adapter.
 *
 * @package ES Admin
 */

namespace ES_Admin\Adapters;

/**
 * An adapter for SearchPress
 */
class SearchPress extends Adapter {

	/**
	 * Build the object and set the field map.
	 */
	public function __construct() {
		$this->field_map['post_name']              = 'post_name.raw';
		$this->field_map['post_title']             = 'post_title.raw';
		$this->field_map['post_title.analyzed']    = 'post_title';
		$this->field_map['post_content.analyzed']  = 'post_content';
		$this->field_map['post_author']            = 'post_author.user_id';
		$this->field_map['post_date']              = 'post_date.date';
		$this->field_map['post_date_gmt']          = 'post_date_gmt.date';
		$this->field_map['post_modified']          = 'post_modified.date';
		$this->field_map['post_modified_gmt']      = 'post_modified_gmt.date';
		$this->field_map['post_type']              = 'post_type.raw';
		$this->field_map['post_meta']              = 'post_meta.%s.raw';
		$this->field_map['post_meta.analyzed']     = 'post_meta.%s.value';
		$this->field_map['post_meta.signed']       = 'post_meta.%s.long';
		$this->field_map['post_meta.unsigned']     = 'post_meta.%s.long';
		$this->field_map['term_name']              = 'terms.%s.name.raw';
		$this->field_map['term_name.analyzed']     = 'terms.%s.name';
		$this->field_map['category_name']          = 'terms.category.name.raw';
		$this->field_map['category_name.analyzed'] = 'terms.category.name';
		$this->field_map['tag_name']               = 'terms.post_tag.name.raw';
		$this->field_map['tag_name.analyzed']      = 'terms.post_tag.name';
	}

	/**
	 * Run a query against the ES index.
	 *
	 * @param  array $es_args Elasticsearch DSL as a PHP array.
	 * @return array Elasticsearch response as a PHP array.
	 */
	public function query( $es_args ) {
		return SP_API()->search( json_encode( $es_args ), [ 'output' => ARRAY_A ] );
	}
}
