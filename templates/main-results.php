<?php
/**
 * Main results template.
 *
 * @package ES Admin
 */

if ( empty( $_GET['s'] ) ) {
	return;
}
?>

<form id="es-admin-search-results" method="get">
	<?php if ( ! empty( $_REQUEST['page'] ) ) : ?>
		<input type="hidden" name="page" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ) ?>" />
	<?php endif ?>

	<?php $results->display() ?>
</form>
