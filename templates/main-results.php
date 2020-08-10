<?php
/**
 * Main results template.
 *
 * @package ES Admin
 */

namespace ES_Admin;

if ( empty( $_GET['s'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return;
}
?>

<form id="es-admin-search-results" method="get">
	<?php if ( ! empty( $_REQUEST['page'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<input type="hidden" name="page" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>" />
	<?php endif ?>
	<input type="hidden" name="s" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['s'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>" />

	<?php $results->display(); ?>
</form>
