<?php
/**
 * Facets template.
 *
 * @package ES Admin
 */

namespace ES_Admin;

$search = ES::instance()->main_search();
if ( ! $search->has_facets() ) {
	return;
}
?>

<aside id="es-admin-facets">
	<form method="get">
		<?php if ( ! empty( $_REQUEST['page'] ) ) : ?>
			<input type="hidden" name="page" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ); ?>" />
		<?php endif ?>
		<?php if ( ! empty( $_GET['s'] ) ) : ?>
			<input type="hidden" name="s" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['s'] ) ) ); ?>" />
		<?php endif ?>
		<?php if ( ! empty( $additional_blog_ids ) ) : ?>
			<?php foreach( $additional_blog_ids as $blog_id ) : ?>
				<input type="hidden" name="additional_blog_ids[]" value="<?php echo esc_attr( $blog_id ); ?>" />
			<?php endforeach; ?>
		<?php endif ?>
		<?php if ( ! empty( $_GET['checkall'] ) ) : ?>
			<?php $checkall = $_GET['checkall']; ?>
			<?php foreach ( $checkall as $brand ): ?>
				<input type="hidden" name="checkall[]" value="<?php echo esc_attr( $brand ); ?>" />
			<?php endforeach; ?>
		<?php endif ?>

		<h2><?php esc_html_e( 'Filter Results', 'es-admin' ); ?></h2>

		<?php foreach ( $search->facets() as $facet ) : ?>
			<?php if ( $facet->has_buckets() ) : ?>
				<div class="es-admin-facet-section">
					<h3><?php echo esc_html( $facet->title() ); ?></h3>
					<ol>
						<?php foreach ( $facet->buckets() as $bucket ) : ?>
							<li>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( $facet->field_name() ); ?>" value="<?php echo esc_attr( $facet->field_value( $bucket['key'] ) ); ?>" <?php $facet->checked( $bucket['key'] ); ?> />
									<?php echo esc_html( $facet->get_label_for_bucket( $bucket ) ); ?>
									<span class="es-facet-count">(<?php echo esc_html( number_format_i18n( $bucket['doc_count'] ) ); ?>)</span>
								</label>
							</li>
						<?php endforeach ?>
					</ol>
				</div>
			<?php endif ?>
		<?php endforeach ?>

		<?php submit_button( __( 'Update Results', 'es-admin' ), 'secondary', 'submit', true, [ 'id' => 'es-admin-filter-results' ] ); ?>
	</form>
</aside>
