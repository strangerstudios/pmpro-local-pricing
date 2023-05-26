<?php

// exit if uninstall/delete not called
if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// Delete the option on plugin uninstall.
delete_option( 'pmpro_local_pricing_settings' );

// Delete the transient that may exist.
delete_transient( 'pmpro_local_exchange_rate_' . get_option( 'pmpro_currency' ) );