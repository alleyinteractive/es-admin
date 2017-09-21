<?php
/**
 * Template header.
 *
 * @package ES Admin
 */

namespace ES_Admin;
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Content Search', 'es-admin' ); ?></h1>

	<?php if ( ! empty( $_GET['s'] ) ) : ?>
		<h3>
			<?php
			/* translators: %s: search keywords */
			echo esc_html( sprintf( __( 'Search results for &#8220;%s&#8221;' ), sanitize_text_field( wp_unslash( $_GET['s'] ) ) ) );
			?>
		</h3>
		<?php
	endif;

/* </div> found in footer.php. */
