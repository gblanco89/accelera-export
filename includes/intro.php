<?php
function accelera_export_intro() {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( ! function_exists( 'plugins_api' ) ) {
		  require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
	}

	// Removing other annoying notices
	echo '<style>.update-nag, .notice-info:not(.accelera-notice), .updated, .error, .is-dismissible, .ngg_admin_notice { display: none !important; }</style>';

	?>
	<div class="wrap">
		<h1>Accelera Export</h1>

		<?php // Checking for conflicting plugins - NitroPack
		if ( in_array( 'nitropack/main.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		?>
		<div class="notice notice-warning accelera-notice">
			<p><?php _e( 'Hey! Looks like you have Nitropack installed and active. Please go to <a href="options-general.php?page=nitropack">Nitropack\'s settings</a> and enable Safe Mode. Then, return here and continue with the process.' ); ?></p>
		</div>
		<?php
		}
		?>

		<div>
			<p>
			<?php _e( 'This is just the first step before exporting your site information. Please follow the next steps:<br />
				<ol>
					<li>Click on the button "CSV Export" below and wait a few seconds until the plugin collects all the information. <strong>Do not refresh or exit this page.</strong></li>
					<li>Download the CSV in the next page.</li>
					<li>Send the CSV, with no modification, to <a href="mailto:support@accelera.site">support@accelera.site</a>.</li>
				</ol>', 'accelera-export' );
			?>
			</p>
		</div>

		<a href="tools.php?page=accelera-export-csv" class="button button-primary">CSV Export</a>
	</div>
	<?php
}