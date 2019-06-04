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

	<h2>Choose Sites</h2>
	<div class="es-admin-site-section">
		<?php
			$additional_blog_ids = array_filter(
				$_GET['additional_blog_ids'],
				function( $id ) {
					return is_numeric( $id );
				}
			);

			$req = new \WP_REST_Request( \WP_REST_Server::READABLE, '/nbc/v1/sites' );
			$response = ( new \SML\REST_Client() )->request_to_data( $req );

			if ( empty( $additional_blog_ids ) ) {
				$additional_blog_ids = wp_list_pluck( $response, 'jetpack_blog_id' );
				$additional_blog_ids = array_diff( $additional_blog_ids, [ $blog_id ] );
				$checkall = true;
			}

			$brands = [
				'nbc' => __( 'NBC', 'nbc' ),
				'telemundo' => __( 'Telemundo', 'nbc' ),
				'microsite' => __( 'Microsites', 'nbc' ),
			];
			foreach( $brands as $brand => $brand_name ):
				$brand_sites = array_filter( $response, function( $site ) use ( $brand ) {
					return ( $brand === $site['brand'] && $site['id'] !== get_current_blog_id() );
				});
				if ( $checkall || in_array( $brand, $_GET['checkall'] ) ) {
					$checked = ' checked="checked"';
				} else {
					$checked = "";
				}
				echo '<div>';
				printf(
					'<label><h3><input type="checkbox" name="checkall[]" class="es-admin-checkall" value="%s" %s />%s</h3></label>',
					$brand,
					$checked,
					$brand_name
				);
			?>
			<ol>
				<?php foreach( $brand_sites as $site ) {
					if ( in_array( $site['jetpack_blog_id'], $additional_blog_ids ) ) {
						$checked = ' checked="checked"';
					} else {
						$checked = '';
					}
					?>
				<li>
					<label>
						<?php if ( null !== $site['jetpack_blog_id'] ) { ?>
							<input class="additional_blog_ids <?php echo $brand; ?>" type="checkbox" name="additional_blog_ids[]" value="<?php echo $site['jetpack_blog_id']?>" <?php echo $checked; ?> />
						<?php } ?>
						<?php echo $site['name']; ?>
					</label>
				</li>
				<?php } ?>
			</ol>
			<?php
			echo '</div>';
			endforeach; ?>
	</div>
</form>
