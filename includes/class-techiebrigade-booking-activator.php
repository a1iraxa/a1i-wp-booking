<?php

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/a1iraxa
 * @since      1.0.0
 *
 * @package    Techiebrigade_Booking
 * @subpackage Techiebrigade_Booking/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Techiebrigade_Booking
 * @subpackage Techiebrigade_Booking/includes
 * @author     Ali Raza <aligcs324@gmail.com>
 */
class Techiebrigade_Booking_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'bookings';

        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            ID mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id int(128) NOT NULL,
            product_id int(128) NOT NULL,
            book_day varchar(255) DEFAULT '' NOT NULL,
            book_date varchar(255) DEFAULT '' NOT NULL,
            book_slot varchar(255) DEFAULT '' NOT NULL,
            arrival_time varchar(255) DEFAULT '' NOT NULL,
            status varchar(55) DEFAULT '' NOT NULL,
            comment varchar(255) DEFAULT '' NOT NULL,
            completed_at datetime DEFAULT '0000-00-00 00:00:00' NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (ID)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

}
