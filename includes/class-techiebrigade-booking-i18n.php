<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/a1iraxa
 * @since      1.0.0
 *
 * @package    Techiebrigade_Booking
 * @subpackage Techiebrigade_Booking/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Techiebrigade_Booking
 * @subpackage Techiebrigade_Booking/includes
 * @author     Ali Raza <aligcs324@gmail.com>
 */
class Techiebrigade_Booking_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'techiebrigade-booking',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
