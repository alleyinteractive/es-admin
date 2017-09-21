<?php
/**
 * Admin settings screen.
 *
 * @package ES Admin
 */

?>
<div class="wrap">
	<h1><?php esc_html_e( 'Admin Search Settings', 'es-admin' ); ?></h1>

	<?php if ( ! empty( $_GET['saved'] ) ) : ?>
		<div id="message" class="updated notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings Updated', 'es-admin' ); ?></p></div>
	<?php endif ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="es_admin_settings" />
		<?php wp_nonce_field( 'es_admin_settings', 'es_admin_nonce' ); ?>

		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable Core Integration', 'es-admin' ); ?></th>
				<td>
					<label for="es-admin-enable-integration">
						<input name="enable_integration" id="es-admin-enable-integration" type="checkbox" value="1" <?php checked( true, $this->get_settings( 'enable_integration' ) ); ?> />
						<?php esc_html_e( 'Replace core admin searches with Elasticsearch', 'es-admin' ); ?>
					</label>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>
	</form>
</div>
