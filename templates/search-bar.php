<?php
if ( empty( $_GET['s'] ) && !$this->has_items() ) {
	return;
}
?>
<form method="get">
	<label class="screen-reader-text" for="es-admin-search-input"><?php esc_html_e( 'Search:', 'es-admin' ); ?></label>
	<input type="search" id="es-admin-search-input" name="s" value="<?php _admin_search_query(); ?>" />
	<input type="hidden" name="page" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ) ?>" />

	<?php
	if ( ! empty( $_GET['orderby'] ) ) {
		echo '<input type="hidden" name="orderby" value="' . esc_attr( $_GET['orderby'] ) . '" />';
	}
	if ( ! empty( $_GET['order'] ) ) {
		echo '<input type="hidden" name="order" value="' . esc_attr( $_GET['order'] ) . '" />';
	}
	?>

	<?php submit_button( __( 'Search', 'es-admin' ), 'button', '', false ) ?>
</form>
