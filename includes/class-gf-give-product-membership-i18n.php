<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://morningstardigital.com.au/
 * @since      1.0.0
 *
 * @package    Gf_Give_Product_Membership
 * @subpackage Gf_Give_Product_Membership/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Gf_Give_Product_Membership
 * @subpackage Gf_Give_Product_Membership/includes
 * @author     Morningstar Digital <https://morningstardigital.com.au/>
 */
class Gf_Give_Product_Membership_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'gf-give-product-membership',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
