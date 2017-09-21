<?php
/**
 * Search bar template.
 *
 * @package ES Admin
 */

namespace ES_Admin;
?>

<form method="get" id="es-admin-search-bar">
	<label class="screen-reader-text" for="es-admin-search-input"><?php esc_html_e( 'Search:', 'es-admin' ); ?></label>
	<input type="search" id="es-admin-search-input" name="s" value="<?php _admin_search_query(); ?>" />
	<?php if ( ! empty( $_REQUEST['page'] ) ) : ?>
		<input type="hidden" name="page" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ); ?>" />
	<?php endif ?>

	<?php
	if ( ! empty( $_GET['orderby'] ) ) {
		echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) ) . '" />';
	}
	if ( ! empty( $_GET['order'] ) ) {
		echo '<input type="hidden" name="order" value="' . esc_attr( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) . '" />';
	}
	?>

	<?php submit_button( __( 'Search', 'es-admin' ), 'button', '', false ); ?>
</form>
