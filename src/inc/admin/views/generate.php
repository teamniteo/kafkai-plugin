<?php
/**
 * Generate page for the admin.
 */

use Niteo\Kafkai\Plugin\Config;

?>

<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Generate Article', 'kafkai-wp' ); ?>
	</h1>

	<a href="<?php echo self_admin_url( 'admin.php?page=kafkaiwp_import' ); ?>" class="page-title-action">
		<?php esc_html_e( 'Import Articles', 'kafkai-wp' ); ?>
	</a>

	<hr class="wp-header-end">

	<?php

		// Show errors as notification
	if ( ! empty( $articles->error ) ) {
		$this->add_notice( $articles->code, $articles->error );
	}

	?>

	<form method="post">
		<input type="hidden" name="_<?php echo Config::PLUGIN_PREFIX; ?>nonce" id="_<?php echo Config::PLUGIN_PREFIX; ?>nonce" value="<?php echo esc_attr( wp_create_nonce( Config::PLUGIN_SLUG . '-nonce' ) ); ?>">

		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="<?php echo Config::PLUGIN_PREFIX; ?>niche">
							<?php esc_html_e( 'Niche', 'kafkai-wp' ); ?>
						</label>
					</th>
					<td>
						<select name="<?php echo Config::PLUGIN_PREFIX; ?>niche" id="<?php echo Config::PLUGIN_PREFIX; ?>niche" class="regular-text">
							<?php

								// Article niches
							foreach ( $articles->niches as $key => $niche ) {
								echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $niche ) . '</option>\n';
							}

							?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="<?php echo Config::PLUGIN_PREFIX; ?>title">
							<?php esc_html_e( 'Give us a short sentence to start', 'kafkai-wp' ); ?>
						</label>
					</th>
					<td>
						<textarea name="<?php echo Config::PLUGIN_PREFIX; ?>title" id="<?php echo Config::PLUGIN_PREFIX; ?>title" class="large-text" rows="4"></textarea>
					</td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<input type="submit" name="<?php echo Config::PLUGIN_PREFIX; ?>generate" value="<?php esc_attr_e( 'Generate Article', 'kafkai-wp' ); ?>" class="button button-primary">
		</p>
	</form>
</div>
