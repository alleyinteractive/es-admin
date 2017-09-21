<?php
/**
 * Search results list table.
 *
 * @package ES Admin
 */

namespace ES_Admin;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Search results list table.
 *
 * This code is mostly copied from WordPress Core's WP_Posts_List_Table.
 */
class Results_List_Table extends \WP_List_Table {

	/**
	 * Build the object, setting the defaults.
	 */
	function __construct() {
		parent::__construct( [
			'singular'  => __( 'result', 'es-admin' ),
			'plural'    => __( 'results', 'es-admin' ),
			'ajax'      => true,
		] );
	}

	/**
	 * Get the columns to output.
	 *
	 * @return array
	 */
	function get_columns() {
		return [
			'thumbnail' => _x( 'Image', 'column name', 'es-admin' ),
			'title'     => _x( 'Title', 'column name', 'es-admin' ),
			'post_type' => __( 'Type', 'es-admin' ),
			'author'    => _x( 'Author', 'column name', 'es-admin' ),
			'date'      => _x( 'Date', 'column name', 'es-admin' ),
			// Taxonomies?
		];
	}

	/**
	 * Get the sortable columns.
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		$columns = [
			'date' => [ 'date', true ],
		];

		return $columns;
	}

	/**
	 * Get the name of the primary column.
	 *
	 * @return string The name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'title';
	}

	/**
	 * Output the column title outer wrapper.
	 *
	 * @param \WP_Post $post Current post object.
	 * @param string   $classes The classes for the <td> element.
	 * @param string   $data The data attributes for the <td> element.
	 * @param string   $primary The primary column.
	 */
	protected function _column_title( $post, $classes, $data, $primary ) {
		echo '<td class="' . esc_attr( $classes ) . ' page-title" ' . esc_html( $data ) . '>';
		echo $this->column_title( $post ); // WPCS: XSS ok.
		echo $this->handle_row_actions( $post, 'title', $primary ); // WPCS: XSS ok.
		echo '</td>';
	}

	/**
	 * Handles the title column output.
	 *
	 * @param \WP_Post $post The current WP_Post object.
	 */
	public function column_title( $post ) {
		echo '<strong>';

		$format = get_post_format( $post->ID );
		if ( $format ) {
			$label = get_post_format_string( $format );

			$format_class = 'post-state-format post-format-icon post-format-' . $format;

			$format_args = array(
				'post_format' => $format,
			);

			$this->get_edit_link( $format_args, $label . ':', $format_class, true );
		}

		$can_edit_post = current_user_can( 'edit_post', $post->ID );
		$title = _draft_or_post_title();

		if ( $can_edit_post && 'trash' !== $post->post_status ) {
			printf(
				'<a class="row-title" href="%s" aria-label="%s">%s</a>',
				esc_url( get_edit_post_link( $post->ID ) ),
				/* translators: %s: post title */
				esc_attr( sprintf( __( '&#8220;%s&#8221; (Edit)', 'es-admin' ), $title ) ),
				esc_html( $title )
			);
		} else {
			echo esc_html( $title );
		}
		_post_states( $post );

		if ( isset( $parent_name ) ) {
			$post_type_object = get_post_type_object( $post->post_type );
			echo esc_html( ' | ' . $post_type_object->labels->parent_item_colon . ' ' . $parent_name );
		}
		echo "</strong>\n";

		if ( $can_edit_post && 'trash' !== $post->post_status ) {
			$lock_holder = wp_check_post_lock( $post->ID );

			if ( $lock_holder ) {
				$lock_holder = get_userdata( $lock_holder );
				$locked_avatar_html = get_avatar( $lock_holder->ID, 18 );
				/* translators: %s: name of editor */
				$locked_text = sprintf( __( '%s is currently editing', 'es-admin' ), $lock_holder->display_name );
			} else {
				$locked_avatar_html = $locked_text = '';
			}

			echo '<div class="locked-info"><span class="locked-avatar">' . // WPCS: XSS ok.
				$locked_avatar_html .
				'</span> <span class="locked-text">' .
				esc_html( $locked_text ) .
				"</span></div>\n";
		}

		get_inline_data( $post );
	}

	/**
	 * Handles the post date column output.
	 *
	 * @param \WP_Post $post The current WP_Post object.
	 */
	public function column_date( $post ) {
		if ( '0000-00-00 00:00:00' === $post->post_date ) {
			$t_time = $h_time = __( 'Unpublished', 'es-admin' );
			$time_diff = 0;
		} else {
			$t_time = get_the_time( __( 'Y/m/d g:i:s a', 'es-admin' ) );
			$m_time = $post->post_date;
			$time = get_post_time( 'G', true, $post );

			$time_diff = time() - $time;

			if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
				/* translators: %s: span of time */
				$h_time = sprintf( __( '%s ago', 'es-admin' ), human_time_diff( $time ) );
			} else {
				$h_time = mysql2date( __( 'Y/m/d', 'es-admin' ), $m_time );
			}
		}

		if ( 'publish' === $post->post_status ) {
			esc_html_e( 'Published', 'es-admin' );
		} elseif ( 'future' === $post->post_status ) {
			if ( $time_diff > 0 ) {
				echo '<strong class="error-message">' . esc_html__( 'Missed schedule', 'es-admin' ) . '</strong>';
			} else {
				esc_html_e( 'Scheduled', 'es-admin' );
			}
		} else {
			esc_html_e( 'Last Modified', 'es-admin' );
		}
		echo '<br />';

		/**
		 * Filter the published time of the post.
		 *
		 * @param string  $t_time      The published time.
		 * @param \WP_Post $post        Post object.
		 * @param string  $column_name The column name.
		 * @param string  $mode        The list display mode ('excerpt' or 'list').
		 */
		echo '<abbr title="' . esc_attr( $t_time ) . '">' . esc_html( apply_filters( 'post_date_column_time', $h_time, $post, 'date', 'list' ) ) . '</abbr>';
	}

	/**
	 * Handles the post author column output.
	 *
	 * @param \WP_Post $post The current WP_Post object.
	 */
	public function column_author( $post ) {
		$args = array(
			'author' => get_the_author_meta( 'ID' ),
		);
		$this->get_edit_link( $args, get_the_author(), '', true );
	}

	/**
	 * Handles the thumbnail column output.
	 *
	 * @param \WP_Post $post The current WP_Post object.
	 */
	public function column_thumbnail( $post ) {
		if ( has_post_thumbnail( $post ) ) {
			echo get_the_post_thumbnail( $post, [ 100, 100 ] );
		} elseif ( 'attachment' === $post->post_type ) {
			echo wp_get_attachment_image( $post->ID, [ 100, 100 ] );
		}
	}

	/**
	 * Handles the post type column output.
	 *
	 * @param \WP_Post $post The current WP_Post object.
	 */
	public function column_post_type( $post ) {
		$post_type_object = get_post_type_object( $post->post_type );
		echo esc_html( $post_type_object->labels->singular_name );
	}

	/**
	 * Handles the default column output.
	 *
	 * @param \WP_Post $post        The current WP_Post object.
	 * @param string   $column_name The current column name.
	 */
	public function column_default( $post, $column_name ) {
		if ( 'categories' === $column_name ) {
			$taxonomy = 'category';
		} elseif ( 'tags' === $column_name ) {
			$taxonomy = 'post_tag';
		} elseif ( 0 === strpos( $column_name, 'taxonomy-' ) ) {
			$taxonomy = substr( $column_name, 9 );
		} else {
			$taxonomy = false;
		}
		if ( $taxonomy ) {
			$taxonomy_object = get_taxonomy( $taxonomy );
			$terms = get_the_terms( $post->ID, $taxonomy );
			if ( is_array( $terms ) ) {
				$out_html = array();
				foreach ( $terms as $t ) {
					$posts_in_term_qv = array();
					if ( 'post' != $post->post_type ) {
						$posts_in_term_qv['post_type'] = $post->post_type;
					}
					if ( $taxonomy_object->query_var ) {
						$posts_in_term_qv[ $taxonomy_object->query_var ] = $t->slug;
					} else {
						$posts_in_term_qv['taxonomy'] = $taxonomy;
						$posts_in_term_qv['term'] = $t->slug;
					}

					$label = sanitize_term_field( 'name', $t->name, $t->term_id, $taxonomy, 'display' );
					$out_html[] = $this->get_edit_link( $posts_in_term_qv, $label );
				}
				/* translators: used between list items, there is a space after the comma */
				echo join( esc_html__( ', ', 'es-admin' ), $out_html ); // WPCS: XSS ok.
			} else {
				echo '<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">' . esc_html( $taxonomy_object->labels->no_terms ) . '</span>';
			}
			return;
		}

		if ( is_post_type_hierarchical( $post->post_type ) ) {

			/**
			 * Fires in each custom column on the Posts list table.
			 *
			 * This hook only fires if the current post type is hierarchical,
			 * such as pages.
			 *
			 * @since 2.5.0
			 *
			 * @param string $column_name The name of the column to display.
			 * @param int    $post_id     The current post ID.
			 */
			do_action( 'manage_pages_custom_column', $column_name, $post->ID );
		} else {

			/**
			 * Fires in each custom column in the Posts list table.
			 *
			 * This hook only fires if the current post type is non-hierarchical,
			 * such as posts.
			 *
			 * @since 1.5.0
			 *
			 * @param string $column_name The name of the column to display.
			 * @param int    $post_id     The current post ID.
			 */
			do_action( 'manage_posts_custom_column', $column_name, $post->ID );
		}

		/**
		 * Fires for each custom column of a specific post type in the Posts list table.
		 *
		 * The dynamic portion of the hook name, `$post->post_type`, refers to the post type.
		 *
		 * @since 3.1.0
		 *
		 * @param string $column_name The name of the column to display.
		 * @param int    $post_id     The current post ID.
		 */
		do_action( "manage_{$post->post_type}_posts_custom_column", $column_name, $post->ID );
	}

	/**
	 * Query and set the items to display in the table.
	 */
	function prepare_items() {
		$per_page = 20;

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		// You can bypass Elasticsearch and instead use WP_Query by adding es=0
		// to the url.
		if ( isset( $_GET['es'] ) && '0' === $_GET['es'] ) {
			$args = [
				'post_type' => get_post_types( [ 'public' => true ] ),
				'post_status' => 'any',
				'posts_per_page' => $per_page,
				'paged' => $this->get_pagenum(),
				'ignore_sticky_posts' => true,
				'perm' => 'readable',
			];

			if ( ! empty( $_GET['s'] ) ) {
				$args['s'] = sanitize_text_field( wp_unslash( $_GET['s'] ) );
				$args['orderby'] = 'relevance';
				$args['order'] = 'DESC';
			}

			if ( ! empty( $_GET['orderby'] ) ) {
				$args['orderby'] = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
			}
			if ( ! empty( $_GET['order'] ) ) {
				$args['order'] = sanitize_text_field( wp_unslash( $_GET['order'] ) );
			}

			$query = new \WP_Query();
			$this->items = $query->query( $args );

			$this->set_pagination_args( array(
				'total_items' => $query->found_posts,
				'per_page'    => $per_page,
			) );
		} else {
			$es = ES::instance();

			// Setup base filters.
			// @todo remove post_types that the current user can't access.
			$post_types = array_values( get_post_types( [ 'public' => true ] ) );
			$exclude_post_statuses = array_values( get_post_stati( [ 'exclude_from_search' => true ] ) );

			$facets = apply_filters( 'es_admin_configured_facets', [
				new Facets\Post_Type(),
				new Facets\Category(),
				new Facets\Tag(),
				new Facets\Post_Date(),
			] );

			// Build the ES args.
			$args = [
				'query' => [
					'bool' => [
						'filter' => [ DSL::terms( $es->map_field( 'post_type' ), $post_types ) ],
						'must_not' => DSL::terms( $es->map_field( 'post_status' ), $exclude_post_statuses ),
					],
				],
				'_source' => [
					'post_id',
				],
				'from' => 0,
				'size' => $per_page,
			];

			// Build the search query.
			if ( ! empty( $_GET['s'] ) ) {
				$args['query']['bool']['must'][] = DSL::search_query( sanitize_text_field( wp_unslash( $_GET['s'] ) ) );
			}

			// Setup pagination.
			$page = $this->get_pagenum();
			if ( ! $page ) {
				$page = 1;
			}
			$args['from'] = ( $page - 1 ) * $per_page;

			// Build the sorting.
			if ( ! empty( $_GET['orderby'] ) ) {
				$order = ( ! empty( $_GET['order'] ) && 'desc' === strtolower( $_GET['order'] ) ) ? 'desc' : 'asc'; // WPCS: sanitization ok.
				switch ( $_GET['orderby'] ) {
					case 'relevance':
						$orderby = '_score';
						break;

					default:
						$orderby = 'post_date';
						break;
				}

				// Map the key if it's not score.
				$key = '_score' === $orderby ? $orderby : $es->map_field( $orderby );

				$args['sort'] = [
					[ $key => $order ],
				];
			} elseif ( ! empty( $_GET['s'] ) ) {
				$args['sort'] = [
					'_score',
					[ $es->map_field( 'post_date' ) => 'desc' ],
				];
			} else {
				$args['sort'] = [
					[ $es->map_field( 'post_date' ) => 'desc' ],
				];
			}
			$args['sort'] = apply_filters( 'es_admin_table_sort', $args['sort'], $es, $args );

			// Build the facets and add filters from any facets in the request.
			$aggs = [];
			foreach ( $facets as $facet_type ) {
				$aggs = array_merge( $aggs, $facet_type->request() );
				if ( ! empty( $_GET['facets'][ $facet_type->query_var() ] ) ) {
					$values = array_map( 'sanitize_text_field', (array) $_GET['facets'][ $facet_type->query_var() ] ); // WPCS: sanitization ok.
					$args['query']['bool']['filter'][] = $facet_type->filter( $values );
				}
			}

			$aggs = apply_filters( 'es_admin_facets_query', $aggs );
			if ( ! empty( $aggs ) ) {
				$args['aggs'] = $aggs;
			}

			// Run the search.
			$this->items = [];
			$search = new Search( $args );
			$es->set_main_search( $search );

			if ( ! $search->has_hits() ) {
				$this->items = [];
				return;
			}

			$post_ids = array();
			foreach ( $search->hits() as $hit ) {
				if ( empty( $hit['_source'][ $es->map_field( 'post_id' ) ] ) ) {
					continue;
				}

				$post_id = (array) $hit['_source'][ $es->map_field( 'post_id' ) ];
				$post_ids[] = absint( reset( $post_id ) );
			}

			$post_ids = array_filter( $post_ids );
			if ( empty( $post_ids ) ) {
				$this->items = [];
				return;
			}

			$query = new \WP_Query();
			$this->items = $query->query( [
				'post_type' => get_post_types(),
				'post_status' => 'any',
				'posts_per_page' => $per_page,
				'ignore_sticky_posts' => true,
				'perm' => 'readable',
				'post__in' => $post_ids,
				'orderby' => 'post__in',
			] );

			$this->set_pagination_args( [
				'total_items' => $search->total(),
				'per_page'    => $per_page,
			] );
		}
	}

	/**
	 * Helper to create links to edit.php with params.
	 *
	 * @access protected
	 *
	 * @param array  $args  URL parameters for the link.
	 * @param string $label Link text.
	 * @param string $class Optional. Class attribute. Default empty string.
	 * @param bool   $echo Optional. Output or return. Defaults to false (return).
	 * @return string The formatted link string.
	 */
	protected function get_edit_link( $args, $label, $class = '', $echo = false ) {
		if ( empty( $args['page'] ) ) {
			$args['page'] = 'es-admin-search';
		}
		$url = add_query_arg( $args, 'admin.php' );

		$class_html = '';
		if ( ! empty( $class ) ) {
			$class_html = sprintf(
				' class="%s"',
				esc_attr( $class )
			);
		}

		$link_html = sprintf(
			'<a href="%s"%s>%s</a>',
			esc_url( $url ),
			$class_html,
			esc_html( $label )
		);
		if ( $echo ) {
			echo $link_html; // WPCS: XSS ok.
		} else {
			return $link_html;
		}
	}

	/**
	 * Output a single row.
	 *
	 * Sets up postdata and the global post.
	 *
	 * @param  \WP_Post $post The current post object.
	 */
	public function single_row( $post ) {
		$post = get_post( $post );

		$GLOBALS['post'] = $post; // WPCS: override ok.
		setup_postdata( $post );

		$classes = [];
		$lock_holder = wp_check_post_lock( $post->ID );
		if ( $lock_holder ) {
			$classes[] = 'wp-locked';
		}

		?>
		<tr id="post-<?php echo absint( $post->ID ); ?>" class="<?php echo esc_attr( implode( ' ', get_post_class( $classes, $post->ID ) ) ); ?>">
			<?php $this->single_row_columns( $post ); ?>
		</tr>
		<?php
	}

	/**
	 * Generates and displays row action links.
	 *
	 * @param object $post        Post being acted upon.
	 * @param string $column_name Current column name.
	 * @param string $primary     Primary column name.
	 * @return string Row actions output for posts.
	 */
	protected function handle_row_actions( $post, $column_name, $primary ) {
		if ( $primary !== $column_name ) {
			return '';
		}

		$post_type_object = get_post_type_object( $post->post_type );
		$can_edit_post = current_user_can( 'edit_post', $post->ID );
		$actions = array();
		$title = _draft_or_post_title();

		if ( $can_edit_post && 'trash' != $post->post_status ) {
			$actions['edit'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				get_edit_post_link( $post->ID ),
				/* translators: %s: post title */
				esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'es-admin' ), $title ) ),
				esc_html__( 'Edit', 'es-admin' )
			);
		}

		if ( current_user_can( 'delete_post', $post->ID ) ) {
			if ( 'trash' === $post->post_status ) {
				$actions['untrash'] = sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Restore &#8220;%s&#8221; from the Trash', 'es-admin' ), $title ) ),
					esc_html__( 'Restore', 'es-admin' )
				);
			} elseif ( EMPTY_TRASH_DAYS ) {
				$actions['trash'] = sprintf(
					'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
					get_delete_post_link( $post->ID ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Move &#8220;%s&#8221; to the Trash', 'es-admin' ), $title ) ),
					esc_html_x( 'Trash', 'verb', 'es-admin' )
				);
			}
			if ( 'trash' === $post->post_status || ! EMPTY_TRASH_DAYS ) {
				$actions['delete'] = sprintf(
					'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
					get_delete_post_link( $post->ID, '', true ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Delete &#8220;%s&#8221; permanently', 'es-admin' ), $title ) ),
					esc_html__( 'Delete Permanently', 'es-admin' )
				);
			}
		}

		if ( is_post_type_viewable( $post_type_object ) ) {
			if ( in_array( $post->post_status, array( 'pending', 'draft', 'future' ) ) ) {
				if ( $can_edit_post ) {
					$preview_link = get_preview_post_link( $post );
					$actions['view'] = sprintf(
						'<a href="%s" rel="permalink" aria-label="%s">%s</a>',
						esc_url( $preview_link ),
						/* translators: %s: post title */
						esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', 'es-admin' ), $title ) ),
						esc_html__( 'Preview', 'es-admin' )
					);
				}
			} elseif ( 'trash' != $post->post_status ) {
				$actions['view'] = sprintf(
					'<a href="%s" rel="permalink" aria-label="%s">%s</a>',
					esc_url( get_permalink( $post->ID ) ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'es-admin' ), $title ) ),
					esc_html__( 'View', 'es-admin' )
				);
			}
		}

		if ( is_post_type_hierarchical( $post->post_type ) ) {

			/**
			 * Filter the array of row action links on the Pages list table.
			 *
			 * The filter is evaluated only for hierarchical post types.
			 *
			 * @since 2.8.0
			 *
			 * @param array $actions An array of row action links. Defaults are
			 *                         'Edit', 'Quick Edit', 'Restore, 'Trash',
			 *                         'Delete Permanently', 'Preview', and 'View'.
			 * @param \WP_Post $post The post object.
			 */
			$actions = apply_filters( 'page_row_actions', $actions, $post );
		} else {

			/**
			 * Filter the array of row action links on the Posts list table.
			 *
			 * The filter is evaluated only for non-hierarchical post types.
			 *
			 * @since 2.8.0
			 *
			 * @param array $actions An array of row action links. Defaults are
			 *                         'Edit', 'Quick Edit', 'Restore, 'Trash',
			 *                         'Delete Permanently', 'Preview', and 'View'.
			 * @param \WP_Post $post The post object.
			 */
			$actions = apply_filters( 'post_row_actions', $actions, $post );
		}

		return $this->row_actions( $actions );
	}
}
