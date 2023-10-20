<?php
/**
 * Add a settings field to the Payment Settings page for API Keys and other settings.
 */
function pmpro_local_payment_option_fields( $options, $gateway ) {
    global $pmpro_countries;
    $app_id = get_option( 'pmpro_local_pricing_app_id' );
?>
    <tr class="pmpro_settings_divider">
        <td colspan="2">
            <hr>
            <h3><?php esc_html_e( 'Localized Pricing Settings', 'pmpro-local-pricing' ); ?></h3>
        </td>
    </tr>
    <tr>
        <th><label for="pmpro_local_app_id"><?php esc_html_e( 'App ID:', 'pmpro-local-pricing' ); ?></label></th>
        <td>
            <input type="text" name="pmpro_local_app_id" id="pmpro_local_app_id" value="<?php echo esc_attr( $app_id ); ?>"/>
            <p class="description"><?php echo esc_html__( 'Manage your APP ID here: ', 'pmpro-local-pricing' ) . '<a href="https://openexchangerates.org/" target="_blank">https://openexchangerates.org/</a>. ' . esc_html__( 'Please note that a paid account is required if your site currency is not USD.', 'pmpro-local-pricing' ); ?></p>
        </td>
    </tr>
<?php
}
add_action( 'pmpro_payment_option_fields', 'pmpro_local_payment_option_fields', 10, 2 );

/**
 * Save settings for the App ID.
 * @since 1.0
 */
function pmpro_local_payment_options_save( $values ) {
    update_option( 'pmpro_local_pricing_app_id', sanitize_text_field( $_REQUEST['pmpro_local_app_id'] ) );
}
add_action( 'pmpro_after_saved_payment_options', 'pmpro_local_payment_options_save' );