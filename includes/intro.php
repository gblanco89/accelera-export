<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Outputs the first screen on Tools > Accelera Export.
 *
 * The function gives a short description on what to do and it also warns the user of possible conflicts with the export process.
 *
 * @since 1.0.0
 */

function accelera_export_intro() {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( ! function_exists( 'plugins_api' ) ) {
		  require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
	}

	// Removing other annoying notices
	echo '<style>.update-nag, .notice-info:not(.accelera-notice), .notice-warning:not(.accelera-notice), .updated, .error, .is-dismissible, .ngg_admin_notice, .sbi_notice, .notice:not(.accelera-notice) { display: none !important; }</style>';

	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Accelera Export', 'accelera-export' ); ?></h1>

		<?php
		// If NitroPack installed, suggest to deactivate
		if ( in_array( 'nitropack/main.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		?>
		<div class="notice notice-warning accelera-notice">
			<p><?php _e( 'Hey! Looks like you have Nitropack installed and active. Please go to <a href="options-general.php?page=nitropack">Nitropack\'s settings</a> and enable Safe Mode. Then, return here and continue with the process.', 'accelera-export' ); ?></p>
		</div>
		<?php
		}
		?>

		<div>
			<p>
			<?php _e( 'This is just the first step before exporting your site information. Don\'t worry, we will only export technical information that is useful for the assessment report; no personal or private data is exported. Please follow the next steps:<br />', 'accelera-export' ); ?>

				<ol>
					<li><?php _e( 'Click on the button "TXT Export" below and wait a few seconds until the plugin collects all the technical information. <strong>Do not refresh or exit this page.</strong>' ,'accelera-export'); ?></li>
					<li><?php _e( 'Download the TXT in the next page. Feel free to take a look at it, but <strong>do not add, remove or edit anything</strong> in it.' ,'accelera-export'); ?></li>
				</ol>


			</p>
		</div>


		<form method="post" action="<?php echo admin_url( 'tools.php?page=accelera-export-txt' ); ?>">
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="hosting_provider"><?php _e( 'Hosting Provider', 'accelera-export' ); ?></label>
					</th>
					<td>
						<input type="text" id="hosting_provider" name="hosting_provider" class="regular-text" required>
						<p class="description"><?php _e( 'Enter your current hosting provider.', 'accelera-export' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'TXT Export', 'accelera-export' ) ); ?>
		</form>
	</div>
	<?php
}