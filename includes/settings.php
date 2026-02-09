<?php
/**
 * Add a settings field to the Payment Settings page for API Keys and other settings.
 * This uses a @deprecated hook that is no longer supported in Paid Memberships Pro <V3 class="5">
 * 
 */

/**
 * Loads the relevant settings for the Local Pricing Add On based on PMPro core version.
 * This helps support Paid Memberships Pro V3.5+ and older versions.
 * Note: This function can be removed and deprecated in a future version of the plugin.
 *
 * @since 1.1
 */
function pmpro_local_backwards_compatibility_settings() {
	if ( defined( 'PMPRO_VERSION' ) && version_compare( PMPRO_VERSION, '3.5', '<' ) ) {
		// The previous version of loading the setting has been adjusted. Keeping this function for any custom code (such as function exist checks etc.)
		function pmpro_local_payment_option_fields( $options, $gateway ) {
			echo pmpro_local_show_option_fields();
		}
		add_action( 'pmpro_payment_option_fields', 'pmpro_local_payment_option_fields', 10, 2 );
	}
}
add_action( 'init', 'pmpro_local_backwards_compatibility_settings' );

/**
 * Show the App ID in the Payment Settings, this helps resolve backwards compatibility issues for Paid Memberships Pro V3.5+
 * 
 * @since 1.1
 * 
 */
function pmpro_local_show_option_fields() {
	$app_id = get_option( 'pmpro_local_pricing_app_id' );
?>
<table class="form-table">
	<tr>
		<th>
			<label for="pmpro_local_app_id"><?php esc_html_e( 'Local Pricing APP ID', 'pmpro-local-pricing' ); ?></label>
		</th>
		<td>
			<input type="text" name="pmpro_local_app_id" id="pmpro_local_app_id" value="<?php echo esc_attr( $app_id ); ?>"/>
			<p class="description"><?php echo esc_html__( 'Manage your APP ID here: ', 'pmpro-local-pricing' ) . '<a href="https://openexchangerates.org/" target="_blank">https://openexchangerates.org/</a>. ' . esc_html__( 'Please note that a paid account is required if your site currency is not USD.', 'pmpro-local-pricing' ); ?></p>
		</td>
	</tr>
</table>
<?php
}
add_action( 'pmpro_after_payment_settings', 'pmpro_local_show_option_fields' );

/**
 * Save settings for the App ID.
 * @since 1.0
 */
function pmpro_local_payment_options_save( $values ) {
    update_option( 'pmpro_local_pricing_app_id', sanitize_text_field( $_REQUEST['pmpro_local_app_id'] ) );
}
add_action( 'pmpro_after_saved_payment_options', 'pmpro_local_payment_options_save' );