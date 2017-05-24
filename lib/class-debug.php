<?php
/**
 * Temporary debugging class to figure out why this isn't working on VIP.
 *
 * @package ES Admin
 */

namespace ES_Admin;

class Debug {
	public static $log = [];

	public static function log( $data ) {
		if ( is_string( $data ) || is_numeric( $data ) ) {
			self::$log[] = $data;
		} elseif ( is_array( $data ) ) {
			self::$log[] = wp_json_encode( $data, JSON_PRETTY_PRINT );
		} else {
			self::$log[] = print_r( $data, 1 );
		}
	}

	public static function output() {
		self::output_script();
		self::output_log();
		self::output_console();
	}

	protected static function output_log() {
		?>
		<div id="es_log_wrap" style="display:none">
			<h2>Debug Log</h2>
			<ol>
				<?php foreach ( self::$log as $entry ) : ?>
					<li><pre style="width:95%;height:200px;background:#444;color:#fff;padding:10px;overflow:scroll;"><?php echo esc_html( $entry ) ?></pre></li>
				<?php endforeach ?>
			</ol>
		</div>
		<?php
	}

	protected static function output_script() {
		?>
		<p>
			<a class="button-secondary es-debug-toggle" id="show_es_log" href="#es_log_wrap">Debug Log</a>
			<a class="button-secondary es-debug-toggle" id="show_es_console" href="#es_console_wrap">Debug Console</a>
		</p>
		<script type="text/javascript">
		jQuery( function( $ ) {
			$('.es-debug-toggle').click( function( e ) {
				$( $(this).attr( 'href' ) ).slideDown();
				$(this).remove();
			});
			$('#es_submit').click( function( e ) {
				e.preventDefault();
				$.post(
					<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ) ?>,
					{action: 'es_console', es_input: $('#es_input').val()},
					function(data) {
						$('#es_response').text( data.data.response ).slideDown();
					}
				);
			});
		});
		</script>
		<?php
	}

	protected static function output_console() {
		?>
		<div id="es_console_wrap" style="display:none">
			<h2>Debug Console</h2>
			<textarea id="es_input" rows="15" cols="100"></textarea>
			<?php submit_button( 'Query', 'primary', 'submit', false, [ 'id' => 'es_submit' ] ) ?>
			<pre id="es_response" style="width:95%;height:400px;background:#444;color:#fff;padding:10px;display:none;overflow:scroll;"></pre>
		</div>
		<?php
	}

	public static function es_query() {
		if ( empty( $_POST['es_input'] ) ) {
			wp_send_json_error( [ 'message' => 'Missing input' ] );
		}

		// Validate the JSON
		$json = wp_unslash( sanitize_text_field( $_POST['es_input'] ) );
		$json = json_decode( $json, true );
		if ( empty( $json ) ) {
			wp_send_json_error( [ 'message' => 'Invalid JSON' ] );
		}

		wp_send_json_success( [ 'response' => wp_json_encode( ES::instance()->query( $json ), JSON_PRETTY_PRINT ) ] );
	}
}
