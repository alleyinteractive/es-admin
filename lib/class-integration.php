<?php
/**
 * Seamless search integration throughout the admin.
 *
 * @package ES Admin
 */

namespace ES_Admin;

/**
 * Replace the search backbone in core admin searches.
 */
class Integration {
	use Singleton;

	/**
	 * Build the singleton.
	 */
	public function setup() {
		if ( $this->is_active() ) {
			add_filter( 'ajax_query_attachments_args', [ $this, 'query_attachments' ] );
			add_filter( 'pre_get_posts', [ $this, 'main_search' ] );
		}
	}

	/**
	 * Is integration active?
	 *
	 * @return bool True for yes, false for no.
	 */
	public function is_active() {
		return (bool) Settings::instance()->get_settings( 'enable_integration' );
	}

	/**
	 * Use Elasticsearch in media searches.
	 *
	 * @param  array $args \WP_Query args.
	 * @return array
	 */
	public function query_attachments( $args ) {
		// Only use ES Admin if this is a search.
		if ( ! empty( $args['s'] ) ) {
			$args['es'] = true;

			if ( ! empty( $args['post_status'] ) ) {
				$args['post_status'] = $this->attachments_post_status( $args['post_status'] );
			}

			// Temporary fix; currently, VIP does not index post_mime_type.
			if ( isset( $args['post_mime_type'] ) && defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV ) {
				unset( $args['post_mime_type'] );
			}

			$args = apply_filters( 'es_admin_integration_query_attachments', $args );
			add_filter( 'es_searchable_fields', [ $this, 'query_attachments_searchable_fields' ], 10, 2 );
		}

		return $args;
	}

	/**
	 * Use Elasticsearch in main queries if this is a search.
	 *
	 * @param  \WP_Query $query \WP_Query object, passed by reference.
	 */
	public function main_search( &$query ) {
		if ( $query->is_main_query() && $query->is_search() ) {
			$query->set( 'es', true );

			// Maybe add 'public' to the post status list.
			if ( in_array( 'attachment', (array) $query->get( 'post_type' ), true ) ) {
				$post_status = $query->get( 'post_status' );
				if ( ! empty( $post_status ) ) {
					$query->set( 'post_status', $this->attachments_post_status( $post_status ) );
				}
			}
			do_action_ref_array( 'es_admin_integration_pre_get_posts', [ &$query ] );
		}
	}

	/**
	 * Filter the fields ES_WP_Query searches.
	 *
	 * @param  array        $fields Mapped ES fields. {@see \ES_WP_Query::$es_map}.
	 * @param  \ES_WP_Query $query ES_WP_Query object for field mapping.
	 * @return array
	 */
	public function query_attachments_searchable_fields( $fields, $query ) {
		$fields[] = $query->es_map( 'post_excerpt' );
		$fields[] = $query->meta_map( '_wp_attachment_image_alt', 'analyzed' );
		return apply_filters( 'es_admin_query_attachments_searchable_fields', $fields, $query );
	}

	/**
	 * Potentially alter the post_status when attachments are queried.
	 *
	 * @param  string|array $status Post status.
	 * @return string|array
	 */
	protected function attachments_post_status( $status ) {
		/**
		 * This filter allows developers to optionally ignore the post_status
		 * field on attachments. Depending on how content is built to be
		 * indexed, the post_status may be the parent post's post_status, which
		 * would then be impossible to search against.
		 *
		 * @param bool $ignore_attachment_post_status Defaults to false.
		 */
		if ( apply_filters( 'es_admin_ignore_attachment_post_status', false ) ) {
			return 'any';
		}

		return $status;
	}
}
