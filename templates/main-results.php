<?php
/**
 * Main results template.
 *
 * @package ES Admin
 */

namespace ES_Admin;

if ( empty( $_GET['s'] ) ) {
	$search_query = '';
} else {
	$search_query = sanitize_text_field( wp_unslash( $_GET['s'] ) );
}
?>

<form id="es-admin-search-results" method="get">
	<?php if ( ! empty( $_REQUEST['page'] ) ) : ?>
		<input type="hidden" name="page" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ); ?>" />
	<?php endif ?>
	<input type="hidden" name="s" value="<?php echo esc_attr( $search_query ); ?>" />

	<?php $results->display(); ?>
</form>
