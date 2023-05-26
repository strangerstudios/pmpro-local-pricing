<?php
/**
 * Plugin Name: Paid Memberships Pro - Localized Pricing
 * Description: Show a localized price based on your visitors location.
 * Version: 1.0
 */

require_once( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/currencies.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/settings.php' );

use GeoIp2\Database\Reader;

/**
 * Get the user's location from their IP address and store it in a session.
 */
function pmpro_local_get_users_location_from_IP() {

    // We've already got it in the session, bail.
    if (  pmpro_get_session_var( 'pmpro_local_country' ) ) {
        return;
    }

    // Get the user's country from IP
    $user_ip = $_SERVER['REMOTE_ADDR'];

    //Local server, just bail.
    if ( $user_ip === '127.0.0.1' ) {
        return;
    }

    $geolocate = new Reader( plugin_dir_path( __FILE__ ) . 'includes/GeoLite2-Country.mmdb' );
    $results = $geolocate->country( $user_ip );

    // Let's get the country code now.
    $country = $results->country->isoCode;

    // Unable to get country from IP.
    if ( empty( $country ) ) {
        return;
    }

    // Set the session now.
    pmpro_set_session_var( 'pmpro_local_country', $country );
}
add_action( 'pmpro_checkout_preheader_before_get_level_at_checkout', 'pmpro_local_get_users_location_from_IP' );

/**
 * Get the user's exchange rate based on their location
 *
 * @return string $exchange_rate The exchange rate for the user's currency based on their location. It is returned as a string for more accurate rounding.
 */
function pmpro_local_get_currency_based_on_location() {

    // Get the user's location from the session or try to get it again.
    $user_location = pmpro_get_session_var( 'pmpro_local_country' ) ? pmpro_get_session_var( 'pmpro_local_country' ) : false;

    // If the user's location is not set, bail.
    if ( ! $user_location ) {
        return;
    }

    // Get the site currency and the user's currency.
    $site_currency = get_option( 'pmpro_currency' );
    $currency = pmpro_local_get_currency_from_country( $user_location );

    // We don't want to show a difference if the user's currency is the same as the site's currency.
    if ( $currency === $site_currency ) {
        return;
    }

    return $currency;
}

/**
 * Get the exchange rate between the site currency and user currency via the API and return the rate.
 *
 * @param float $site_currency The merchant's base currency.
 * @param float $currency The currency we need to check the exchange rate for.
 * @return float $exchange_rate The exchange rate value for the currency against the base currency.
 */
function pmpro_local_exchange_rate( $site_currency, $currency ) {

    // Get transient for this currency ( 1 hour )
    $exchange_rate = get_transient( 'pmpro_local_exchange_rate_' . $site_currency );

    // If the transient is set, return it.
    if ( isset( $exchange_rate->$currency ) ) {
        return $exchange_rate->$currency;
    }

    $raw_url = "https://openexchangerates.org/api/latest.json";
    
    // Make a remote request to openexchangerate.org
    $params = array(
        'app_id' => esc_attr( get_option( 'pmpro_local_app_id' ) ),
        'base' => $site_currency
    );

    //add query args
    $url = add_query_arg( $params, $raw_url );

    // Let's get the exchange data now.
    $response = wp_remote_get( $url );

    // Get the $response information now.
    $response_code = wp_remote_retrieve_response_code( $response );

    // If the response code is not 200, bail.
    if ( $response_code !== 200 ) {
        /// $response_body = json_decode( wp_remote_retrieve_body( $response ) );
        return false;
    }

    // Get the body of the response.
    $response_body = json_decode( wp_remote_retrieve_body( $response ) );

    // Get the exchange rate for the currency.
    $exchange_rate = $response_body->rates;

    // Set the transient for this currency.
    set_transient( 'pmpro_local_exchange_rate_' . $site_currency, $exchange_rate, HOUR_IN_SECONDS );

    // Return the exchange rate for the user's currency
    if ( isset( $exchange_rate->$currency ) ) {
        return $exchange_rate->$currency;
    } else {
        return false;
    } 
}

/// Let's adjust the cost_text now
function pmpro_local_show_local_cost_text( $cost, $level, $tags, $short ) {
    global $discount_code;

    if ( ! pmpro_is_checkout() || pmpro_isLevelFree( $level ) ) {
        return $cost;
    }

    $currency = pmpro_local_get_currency_based_on_location();

    // If there's no difference in the currency between the user, or unable to get the currency just bail.
    if ( ! $currency ) {
        return $cost;
    }

    $country = pmpro_get_session_var( 'pmpro_local_country' );
    $discounts = pmpro_local_pricing_discounted_countries();

    $exchange_rate = pmpro_local_exchange_rate( get_option( 'pmpro_currency' ), $currency );

    // Let's show the local pricing now.
    $local_initial = $level->initial_payment * $exchange_rate;
    $local_billing = $level->billing_amount * $exchange_rate;

    $cost .= '<br/>';
    // Let's figure out the output of the text right now.
    if ( $local_initial === $local_billing ) {
        $cost .= '<small>(You will be charged around <strong> ' . $currency . pmpro_round_price_as_string( $local_initial ) . '~</strong> - exchange rate: ' . pmpro_round_price_as_string( $exchange_rate ) . '/' . get_option( 'pmpro_currency' ) . ')</small>';
    } elseif ( $local_initial > 0 && $local_billing > 0 ) {
        $cost .= '<small>(The localized rate is ' . $currency . pmpro_round_price_as_string( $local_initial ) . '~ for the initial payment and ' . $currency . pmpro_round_price_as_string( $local_billing ) . ' for the recurring payment.)</small>';

    } elseif ( $local_initial > 0 && $local_billing === 0 ) {
        $cost .= '<small>(The localized rate is ' . $currency . pmpro_round_price_as_string( $local_initial ) . '~ for the initial payment and ' . $currency . pmpro_round_price_as_string( $local_billing ) . ' for the recurring payment.)</small>';

    } elseif ( $local_initial === 0 && $local_billing > 0 ) {
        $cost .= '<small>(The localized rate is ' . $currency . pmpro_round_price_as_string( $local_initial ) . '~ for the initial payment and ' . $currency . pmpro_round_price_as_string( $local_billing ) . ' for the recurring payment.)</small>';

    }

    // If the country has a discount code let's show it at checkout.
    if ( isset( $discounts[$country] ) && ! $discount_code ) {
        $cost .= '<p>' . sprintf( 'Use the discount code <strong>%s</strong> to receive a discount.', $discounts[$country] ) .'</p>';
    }

    /// $cost = str_replace( pmpro_formatPrice( $level->initial_payment ) , pmpro_formatPrice( $level->initial_payment ) . ' (' . $currency . pmpro_round_price_as_string( $local_initial ) . '+/-)', $cost );
    
    return $cost;
}
add_filter( 'pmpro_level_cost_text', 'pmpro_local_show_local_cost_text', 50, 4 );

/**
 * Registration checks to ensure that the code used is allowed by the customer based on their location.
 * @return bool $allowed Allow or deny registration.
 */
function pmpro_local_registration_checks( $okay ) {
    global $pmpro_msg, $pmpro_msgt;
   // Something else set the registration check to not be okay, so bail.
    if ( ! $okay ) {
        return $okay;
    }

    // If a discount code is not set, just return.
    if ( ! isset( $_REQUEST['discount_code'] ) ) {
        return $okay;
    }

    // Check discount code against the list of allowed codes.
    $discount_code = $_REQUEST['discount_code']; //Don't sanitize here, we're just checking against an array and not storing it anywhere.
    $country_discount = pmpro_local_pricing_discounted_countries();
    $country = pmpro_get_session_var( 'pmpro_local_country' );
    
   // Discount code entered is part of the discounted countries.
    if ( in_array( $discount_code, $country_discount ) ) {
        // Most likely the user is not from the discounted country for the discount code, it's not okay.
        // No discount code found for that country.
        if ( empty( $country_discount[$country] ) ) {
            $okay = false;
        } elseif ( $discount_code === $country_discount[$country]  ) { // If the discounted code enter matches the user's location then it's okay.
            $okay = true;
        } else {
            $okay = false; // Probably not okay? ///Check this.
        }
    }

    // Show an error message that things aren't okay.
    if ( ! $okay ) {
        $pmpro_msg = esc_html__( 'Sorry, you do not qualify to redeem this discount code.', 'pmpro-local-pricing' );
	    $pmpro_msgt = 'pmpro_error';
    }

    return $okay;

}
add_filter( 'pmpro_registration_checks', 'pmpro_local_registration_checks', 100, 1 );

/** 
 * Clear session after checkout
 * 
 */
function pmpro_local_pricing_after_checkout( $user_id, $order ) {
    // Clear the session after checkout.
    pmpro_unset_session_var( 'pmpro_local_country' );
}
add_action( 'pmpro_after_checkout', 'pmpro_local_pricing_after_checkout', 10, 2 );

/**
 * Add content to Privacy Policy page for WordPress.
 *
 * @return void
 */
function pmpro_local_pricing_add_privacy_policy() {	
	// Check for support.
	if ( ! function_exists( 'wp_add_privacy_policy_content') ) {
		return;
	}

	$content = '';
	$content .= '<h2>' . esc_html__( 'Data collected to show localized pricing at checkout.', 'pmpro-local-pricing' ) . '</h2>';
	$content .= '<p>' . esc_html__( 'At checkout, we will use your IP address to find your general location to show a localized rate in your local currency for your convenience. This information is stored temporarily during checkout and clears after checkout is completed.', 'pmpro-local-pricing' ) . '</p>';

	wp_add_privacy_policy_content( 'Paid Memberships Pro - Localized Pricing Add On', $content );
}
add_action( 'admin_init', 'pmpro_local_pricing_add_privacy_policy' );

/**
 * Function to get the country code and discount code associated with that country.
 *
 * @return array $country_discount_code An array of country codes and their discount codes that are linked to that location.
 */
function pmpro_local_pricing_discounted_countries() {
    // Country Code => Discount code
    $country_discount_code = array(
        // 'CA' => 'CANADA10',
        // 'ZA' => 'LEKKER',
        // 'GB' => 'INIT'
    );

    return apply_filters( 'pmpro_local_pricing_discounted_countries', $country_discount_code );
}
