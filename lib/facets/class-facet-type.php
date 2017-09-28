<?php
/**
 * Facet types abstract class
 *
 * @package ES Admin
 */

namespace ES_Admin\Facets;
use \ES_Admin\ES as ES;

/**
 * Facet types abstract class
 */
abstract class Facet_Type {
	/**
	 * The key used to identify this aggregation in the ES response.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * The logic mode this facet should use. 'and' or 'or'.
	 *
	 * @var string
	 */
	protected $logic = 'or';

	/**
	 * The query var this facet should use.
	 *
	 * @var string
	 */
	protected $query_var;

	/**
	 * A reference to the \ES_Admin\ES singleton.
	 *
	 * @var ES
	 */
	protected $es;

	/**
	 * Facet title.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * This facet's buckets (results).
	 *
	 * @var array
	 */
	protected $buckets = [];

	/**
	 * Build the facet request.
	 *
	 * @return array
	 */
	abstract public function request_dsl();

	/**
	 * Get the request filter DSL clause.
	 *
	 * @param  array $values Values to pass to filter.
	 * @return array
	 */
	abstract public function filter( $values );

	/**
	 * Build the facet type object.
	 *
	 * @param array $args {
	 *     Arguments for the facet type. This can/will differ based on the type.
	 *     For instance, a taxonomy facet might take an argument for the taxonomy.
	 *
	 *     @type string $key The key used to identify this aggregation in the ES
	 *                       response. While technically optional, this should
	 *                       always be set.
	 *     @type string $title The title to be used on the frontend.
	 * }
	 */
	public function __construct( $args = [] ) {
		$this->key = ! empty( $args['key'] ) ? $args['key'] : md5( serialize( $args ) );

		// Allow some whitelisted properties to be set on instantiation.
		foreach ( [ 'title', 'query_var', 'logic' ] as $prop ) {
			if ( isset( $args[ $prop ] ) ) {
				$this->$prop = $args[ $prop ];
			}
		}

		$this->es = ES::instance();
	}

	/**
	 * Get the logic mode for this facet.
	 *
	 * @return string 'and' or 'or'.
	 */
	public function logic() {
		return $this->logic;
	}

	/**
	 * Get the query var for this facet.
	 *
	 * @return string
	 */
	public function query_var() {
		return $this->query_var;
	}

	public function key() {
		return $this->key;
	}

	public function title() {
		return $this->title;
	}

	public function set_title( $title ) {
		$this->title = $title;
	}

	/**
	 * Get the buckets for this facet.
	 *
	 * @return array
	 */
	public function buckets() {
		return $this->buckets;
	}

	/**
	 * Set the facet buckets (response).
	 *
	 * @param array $buckets
	 */
	public function set_buckets( array $buckets ) {
		$this->buckets = $buckets;
	}

	/**
	 * Does this facet have any results?
	 *
	 * @return boolean
	 */
	public function has_buckets() {
		return ! empty( $this->buckets );
	}

	/**
	 * Customize the bucket label for this facet type.
	 *
	 * @param  array  $bucket Bucket from ES.
	 * @return string
	 */
	protected function bucket_label( $bucket ) {
		return $bucket['key'];
	}

	/**
	 * Customize the bucket value for this facet type.
	 *
	 * @param  array  $bucket Bucket from ES.
	 * @return string
	 */
	protected function bucket_value( $bucket ) {
		return $bucket['key'];
	}

	/**
	 * Checked helper for input checkboxes. Wraps `checked()` and checks $_GET.
	 *
	 * @param  mixed $value Current bucket value.
	 */
	public function checked( $value ) {
		$values = ! empty( $_GET['facets'][ $this->query_var() ] ) ? (array) $_GET['facets'][ $this->query_var() ] : []; // WPCS: sanitization ok.
		checked( in_array( $value, $values ) );
	}

	public function the_ui() {
		?>
		<h3><?php echo esc_html( $this->title() ); ?></h3>
		<ol>
			<?php foreach ( $this->buckets as $bucket ) : ?>
				<li>
					<label>
						<input type="checkbox" name="facets[<?php echo esc_attr( $this->query_var() ); ?>][]" value="<?php echo esc_attr( $this->bucket_value( $bucket ) ); ?>" <?php $this->checked( $bucket['key'] ); ?> />
						<?php echo esc_html( $this->bucket_label( $bucket ) ); ?>
						<span class="es-facet-count">(<?php echo esc_html( number_format_i18n( $bucket['doc_count'] ) ); ?>)</span>
					</label>
				</li>
			<?php endforeach ?>
		</ol>
		<?php
	}
}
