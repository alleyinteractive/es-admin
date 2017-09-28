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

		<h2><?php esc_html_e( 'Filter Results', 'es-admin' ); ?></h2>

		<?php foreach ( $search->facets() as $facet ) : ?>
			<?php if ( $facet->has_buckets() ) : ?>
				<div class="es-admin-facet-section">
					<?php $facet->the_ui(); ?>
				</div>
			<?php endif ?>
		<?php endforeach ?>

		<?php submit_button( __( 'Update Results', 'es-admin' ), 'secondary', 'submit', true, [ 'id' => 'es-admin-filter-results' ] ); ?>
	</form>
</aside>
