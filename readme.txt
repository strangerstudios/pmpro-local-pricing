=== Paid Memberships Pro - Local Pricing ===
Contributors: strangerstudios
Tags: pricing, geolocation, currency
Requires at least: 5.4
Tested up to: 6.6
Requires PHP: 7.0
Stable tag: 1.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Dynamically convert level pricing at checkout to the approximate rate in the visitor's local currency.

== Description ==

Show an approximate localized currency rate during checkout for visitors based on their IP address and geolocation. Use the geolocation features of this Add On to offer a discount code to visitors from a specific country.

== Installation ==

1. Upload the `pmpro-local-pricing` directory to the `/wp-content/plugins/` directory of your site.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to Memberships > Settings > Payment Gateway.
4. Enter your App ID and save settings.

Note: currency conversions are processed using the [https://openexchangerates.org/](https://openexchangerates.org/) API. If your site's default currency is USD, you can use an API key linked to a free account at [https://openexchangerates.org/](https://openexchangerates.org/). All other currencies will require a paid plan with the API provider.

Refer to the [Local Pricing Add On documentation page](https://www.paidmembershipspro.com/add-ons/local-pricing/) for more information on how to set up this Add On or for a code recipe to offer unique discount codes based on location.

== Frequently Asked Questions ==
= I found a bug in the plugin.
Please post it in the issues section of GitHub and we'll fix it as soon as we can. Thanks for helping. https://github.com/strangerstudios/pmpro-local-pricing/issues

== Changelog ==
= 1.0 - TBD =
* Initial release
