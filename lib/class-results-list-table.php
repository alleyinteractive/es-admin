<?php
namespace ES_Admin;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 *
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 *
 * Our theme for this list table is going to be movies.
 */
class Results_List_Table extends \WP_List_Table {

	/** ************************************************************************
	 * REQUIRED. Set up a constructor that references the parent constructor. We
	 * use the parent reference to set some default configs.
	 ***************************************************************************/
	function __construct(){
		global $status, $page;

		// Set parent defaults
		parent::__construct( [
			'singular'  => __( 'result', 'es-admin' ),
			'plural'    => __( 'results', 'es-admin' ),
			'ajax'      => true
		] );
	}

	function get_columns(){
		return [
			'thumbnail' => _x( 'Thumbnail', 'column name', 'es-admin' ),
			'title'     => _x( 'Title', 'column name', 'es-admin' ),
			'post_type' => __( 'Type', 'es-admin' ),
			'author'    => _x( 'Author', 'column name', 'es-admin' ),
			'date'      => _x( 'Date', 'column name', 'es-admin' ),
			// Taxonomies?
		];
	}

	/**
	 * @return array
	 */
	protected function get_sortable_columns() {
		$columns = [
			'date' => 'date',
		];

		if ( empty( $_GET['s'] ) ) {
			$columns['date'] = [ 'date', true ];
		}

		return $columns;
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @return string The name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'title';
	}

	/**
	 * @param WP_Post $post
	 * @param string  $classes
	 * @param string  $data
	 * @param string  $primary
	 */
	protected function _column_title( $post, $classes, $data, $primary ) {
		echo '<td class="' . $classes . ' page-title" ', $data, '>';
		echo $this->column_title( $post );
		echo $this->handle_row_actions( $post, 'title', $primary );
		echo '</td>';
	}

	/**
	 * Handles the title column output.
	 *
	 * @param WP_Post $post The current WP_Post object.
	 */
	public function column_title( $post ) {
		echo "<strong>";

		$format = get_post_format( $post->ID );
		if ( $format ) {
			$label = get_post_format_string( $format );

			$format_class = 'post-state-format post-format-icon post-format-' . $format;

			$format_args = array(
				'post_format' => $format,
			);

			echo $this->get_edit_link( $format_args, $label . ':', $format_class );
		}

		$can_edit_post = current_user_can( 'edit_post', $post->ID );
		$title = _draft_or_post_title();

		if ( $can_edit_post && $post->post_status != 'trash' ) {
			printf(
				'<a class="row-title" href="%s" aria-label="%s">%s</a>',
				get_edit_post_link( $post->ID ),
				/* translators: %s: post title */
				esc_attr( sprintf( __( '&#8220;%s&#8221; (Edit)' ), $title ) ),
				$title
			);
		} else {
			echo $title;
		}
		_post_states( $post );

		if ( isset( $parent_name ) ) {
			$post_type_object = get_post_type_object( $post->post_type );
			echo ' | ' . $post_type_object->labels->parent_item_colon . ' ' . esc_html( $parent_name );
		}
		echo "</strong>\n";

		if ( $can_edit_post && $post->post_status != 'trash' ) {
			$lock_holder = wp_check_post_lock( $post->ID );

			if ( $lock_holder ) {
				$lock_holder = get_userdata( $lock_holder );
				$locked_avatar = get_avatar( $lock_holder->ID, 18 );
				$locked_text = esc_html( sprintf( __( '%s is currently editing' ), $lock_holder->display_name ) );
			} else {
				$locked_avatar = $locked_text = '';
			}

			echo '<div class="locked-info"><span class="locked-avatar">' . $locked_avatar . '</span> <span class="locked-text">' . $locked_text . "</span></div>\n";
		}

		get_inline_data( $post );
	}

	/**
	 * Handles the post date column output.
	 *
	 * @since 4.3.0
	 * @access public
	 *
	 * @global string $mode
	 *
	 * @param WP_Post $post The current WP_Post object.
	 */
	public function column_date( $post ) {
		global $mode;

		if ( '0000-00-00 00:00:00' === $post->post_date ) {
			$t_time = $h_time = __( 'Unpublished', 'es-admin' );
			$time_diff = 0;
		} else {
			$t_time = get_the_time( __( 'Y/m/d g:i:s a', 'es-admin' ) );
			$m_time = $post->post_date;
			$time = get_post_time( 'G', true, $post );

			$time_diff = time() - $time;

			if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
				$h_time = sprintf( __( '%s ago', 'es-admin' ), human_time_diff( $time ) );
			} else {
				$h_time = mysql2date( __( 'Y/m/d', 'es-admin' ), $m_time );
			}
		}

		if ( 'publish' === $post->post_status ) {
			_e( 'Published', 'es-admin' );
		} elseif ( 'future' === $post->post_status ) {
			if ( $time_diff > 0 ) {
				echo '<strong class="error-message">' . __( 'Missed schedule', 'es-admin' ) . '</strong>';
			} else {
				_e( 'Scheduled', 'es-admin' );
			}
		} else {
			_e( 'Last Modified', 'es-admin' );
		}
		echo '<br />';
		if ( 'excerpt' === $mode ) {
			/**
			 * Filter the published time of the post.
			 *
			 * If `$mode` equals 'excerpt', the published time and date are both displayed.
			 * If `$mode` equals 'list' (default), the publish date is displayed, with the
			 * time and date together available as an abbreviation definition.
			 *
			 * @since 2.5.1
			 *
			 * @param string  $t_time      The published time.
			 * @param WP_Post $post        Post object.
			 * @param string  $column_name The column name.
			 * @param string  $mode        The list display mode ('excerpt' or 'list').
			 */
			echo apply_filters( 'post_date_column_time', $t_time, $post, 'date', $mode );
		} else {

			/** This filter is documented in wp-admin/includes/class-wp-posts-list-table.php */
			echo '<abbr title="' . $t_time . '">' . apply_filters( 'post_date_column_time', $h_time, $post, 'date', $mode ) . '</abbr>';
		}
	}

	/**
	 * Handles the checkbox column output.
	 *
	 * @param WP_Post $post The current WP_Post object.
	 */
	public function column_cb( $post ) {
		if ( current_user_can( 'edit_post', $post->ID ) ): ?>
			<label class="screen-reader-text" for="cb-select-<?php the_ID(); ?>"><?php
				printf( __( 'Select %s' ), _draft_or_post_title() );
			?></label>
			<input id="cb-select-<?php the_ID(); ?>" type="checkbox" name="post[]" value="<?php the_ID(); ?>" />
			<div class="locked-indicator"></div>
		<?php endif;
	}

	/**
	 * Handles the post author column output.
	 *
	 * @since 4.3.0
	 * @access public
	 *
	 * @param WP_Post $post The current WP_Post object.
	 */
	public function column_author( $post ) {
		$args = array(
			'author' => get_the_author_meta( 'ID' )
		);
		echo $this->get_edit_link( $args, get_the_author() );
	}

	public function column_thumbnail( $post ) {
		if ( has_post_thumbnail( $post ) ) {
			echo get_the_post_thumbnail( $post, [ 100, 100 ] );
		} elseif ( 'attachment' === $post->post_type ) {
			echo wp_get_attachment_image( $post->ID, [ 100, 100 ] );
		}
	}

	public function column_post_type( $post ) {
		$post_type_object = get_post_type_object( $post->post_type );
		echo esc_html( $post_type_object->labels->singular_name );
	}

	/**
	 * Handles the default column output.
	 *
	 * @since 4.3.0
	 * @access public
	 *
	 * @param WP_Post $post        The current WP_Post object.
	 * @param string  $column_name The current column name.
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
				$out = array();
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

					$label = esc_html( sanitize_term_field( 'name', $t->name, $t->term_id, $taxonomy, 'display' ) );
					$out[] = $this->get_edit_link( $posts_in_term_qv, $label );
				}
				/* translators: used between list items, there is a space after the comma */
				echo join( __( ', ' ), $out );
			} else {
				echo '<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">' . $taxonomy_object->labels->no_terms . '</span>';
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

	function prepare_items() {
		$per_page = 20;

		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();


		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array( $columns, $hidden, $sortable );


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

			$query = new \WP_Query;
			$this->items = $query->query( $args );

			$this->set_pagination_args( array(
				'total_items' => $query->found_posts,
				'per_page'    => $per_page,
			) );
		} else {
			$es = ES::instance();

			// Setup base filters
			// @todo remove post_types that the current user can't access
			$post_types = array_values( get_post_types( [ 'public' => true ] ) );
			$post_statuses = array_values( get_post_stati( [ 'exclude_from_search' => true ] ) );

			$filters = [
				$es->dsl_terms( $es->map_field( 'post_type' ), $post_types ),
				[ 'not' => $es->dsl_terms( $es->map_field( 'post_status' ), $post_statuses ) ],
			];

			// Build the ES args
			$args = [
				'filter' => [
					'and' => $filters,
				],
				'fields' => [
					'post_id',
				],
				'from' => 0,
				'size' => $per_page,
			];

			// Build the search query
			if ( ! empty( $_GET['s'] ) ) {
				$args['query'] = $es->search_query( sanitize_text_field( wp_unslash( $_GET['s'] ) ) );
			}

			// Setup pagination
			$page = $this->get_pagenum();
			if ( ! $page ) {
				$page = 1;
			}
			$args['from'] = ( $page - 1 ) * $per_page;

			// Build the sorting
			if ( ! empty( $_GET['orderby'] ) ) {
				$order = ( ! empty( $_GET['order'] ) && 'desc' === strtolower( $_GET['order'] ) ) ? 'desc' : 'asc';
				switch ( $_GET['orderby'] ) {
					case 'date' :
						$orderby = 'post_date';
						break;
				}
				$args['sort'] = [
					[ $es->map_field( $orderby ) => $order ],
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

			$this->items = [];

			$es_response = $es->query( $args );
			if ( empty( $es_response['hits']['hits'] ) ) {
				$this->items = [];
				return;
			}

			$post_ids = array();
			foreach ( $es_response['hits']['hits'] as $hit ) {
				if ( empty( $hit['fields'][ $es->map_field( 'post_id' ) ] ) ) {
					continue;
				}

				$post_id = (array) $hit['fields'][ $es->map_field( 'post_id' ) ];
				$post_ids[] = absint( reset( $post_id ) );
			}

			$post_ids = array_filter( $post_ids );
			if ( empty( $post_ids ) ) {
				$this->items = [];
				return;
			}

			$query = new \WP_Query;
			$this->items = $query->query( [
				'post_type' => get_post_types(),
				'post_status' => 'any',
				'posts_per_page' => $per_page,
				'ignore_sticky_posts' => true,
				'perm' => 'readable',
				'post__in' => $post_ids,
				'orderby' => 'post__in',
			] );

			if ( isset( $es_response['hits']['total'] ) ) {
				$this->set_pagination_args( [
					'total_items' => absint( $es_response['hits']['total'] ),
					'per_page'    => $per_page,
				] );
			}
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
	 * @return string The formatted link string.
	 */
	protected function get_edit_link( $args, $label, $class = '' ) {
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

		return sprintf(
			'<a href="%s"%s>%s</a>',
			esc_url( $url ),
			$class_html,
			$label
		);
	}

	public function single_row( $post ) {
		$post = get_post( $post );

		$GLOBALS['post'] = $post;
		setup_postdata( $post );
		return parent::single_row( $post );
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
				esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $title ) ),
				__( 'Edit' )
			);
		}

		if ( current_user_can( 'delete_post', $post->ID ) ) {
			if ( 'trash' === $post->post_status ) {
				$actions['untrash'] = sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Restore &#8220;%s&#8221; from the Trash' ), $title ) ),
					__( 'Restore' )
				);
			} elseif ( EMPTY_TRASH_DAYS ) {
				$actions['trash'] = sprintf(
					'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
					get_delete_post_link( $post->ID ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Move &#8220;%s&#8221; to the Trash' ), $title ) ),
					_x( 'Trash', 'verb' )
				);
			}
			if ( 'trash' === $post->post_status || ! EMPTY_TRASH_DAYS ) {
				$actions['delete'] = sprintf(
					'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
					get_delete_post_link( $post->ID, '', true ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Delete &#8220;%s&#8221; permanently' ), $title ) ),
					__( 'Delete Permanently' )
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
						esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;' ), $title ) ),
						__( 'Preview' )
					);
				}
			} elseif ( 'trash' != $post->post_status ) {
				$actions['view'] = sprintf(
					'<a href="%s" rel="permalink" aria-label="%s">%s</a>',
					get_permalink( $post->ID ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $title ) ),
					__( 'View' )
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
			 * @param WP_Post $post The post object.
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
			 * @param WP_Post $post The post object.
			 */
			$actions = apply_filters( 'post_row_actions', $actions, $post );
		}

		return $this->row_actions( $actions );
	}
}
