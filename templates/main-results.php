<h3>
	<?php
	if ( ! empty( $_GET['s'] ) ) {
		/* translators: %s: search keywords */
		echo esc_html( sprintf( __( 'Search results for &#8220;%s&#8221;' ), sanitize_text_field( wp_unslash( $_GET['s'] ) ) ) );
	}
	?>
</h3>

<form id="movies-filter" method="get">
	<?php if ( ! empty( $_REQUEST['page'] ) ) : ?>
		<input type="hidden" name="page" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ) ?>" />
	<?php endif ?>

	<?php $results->display() ?>
</form>
