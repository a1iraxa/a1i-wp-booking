<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class TB_Booking_Form
 *
 * @class TB_Booking_Form
 * @package Four/Classes/Post_Types
 * @since Four 1.0
 */
class TB_Booking_Form {
    /**
     * Initialize custom post type
     *
     * @access public
     * @return void
     */
    public static function init() {
        add_shortcode( 'TB_Booking', array( __CLASS__, 'generate_booking_form' ) );

        add_action( 'wp_ajax_nopriv_tb_save_booking', array( __CLASS__, 'booking_handler') );
        add_action( 'wp_ajax_tb_save_booking', array( __CLASS__, 'booking_handler') );

        add_action( 'wp_ajax_nopriv_tb_ajax_get_time_slots', array( __CLASS__, 'ajax_get_time_slots') );
        add_action( 'wp_ajax_tb_ajax_get_time_slots', array( __CLASS__, 'ajax_get_time_slots') );

    }
    /**
     * Compare key and value
     * @param  key and compared value
     * @return bool|int
     */
    static public function is( $key, $compare ) {
        $value = static::get( $key );
        return $value == $compare;
    }

    /**
     * Compare not equal to
     * @param  key and compared value
     * @return bool|int
     */
    static public function not( $key, $compare ) {
        $value = static::get( $key );
        return $value != $compare;
    }

    /**
     * Determine options has value
     * @param  key to get value
     * @return bool|int
     */
    static public function has( $key ) {
        $value = static::get( $key );
        return ! empty( $value );
    }

    /**
     * Determine options has value
     * @param  key to get value
     * @return value from theme options
     */
    static public function get( $key, $default='' ) {

        cmb2_get_option('techiebrigade_bookings_options', 'techiebrigade_bookings_monday_on', 'java');

        $option_name = 'techiebrigade_bookings_options';

        if ( function_exists( 'cmb2_get_option' ) ) {
            // Use cmb2_get_option as it passes through some key filters.
            return cmb2_get_option( $option_name, $key, $default );
        }

        // Fallback to get_option if CMB2 is not loaded yet.
        $opts = get_option( $option_name, $default );

        $val = $default;

        if ( 'all' == $key ) {
            $val = $opts;
        } elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
            $val = $opts[ $key ];
        }

        return $val;
    }
    public static function ajax_get_time_slots() {

        $__filtered_response = array();

        $posted = array();

        parse_str( $_POST['day'], $posted );

        $low_case_day = strtolower($_POST['day']);
        $option_key = "techiebrigade_bookings_{$low_case_day}_slot";

        $slots = self::get($option_key);

        if ( !empty( $slots ) ) {
            foreach ($slots as $key => $slot) {
                $slot_start = $option_key.'_start';
                $slot_end = $option_key.'_end';
                $slot_format_sql = "$slot[$slot_start]-$slot[$slot_end]";
                $__filtered_response['all'][] = $slot_format_sql;
                if ( self::is_not_booked_slot( $slot_format_sql, $_POST['date'] ) ) {
                    $__filtered_response['available'][] = $slot_format_sql;
                }else{
                    $__filtered_response['booked'][] = $slot_format_sql;
                }
            }
        }

        $response = [
            'success' => true,
            'slots' => $__filtered_response
        ];
        wp_send_json($response);

        wp_die();
    }

    public static function is_not_booked_slot($slot, $date='')
    {
        global $wpdb;
        $wpdb_table = $wpdb->prefix . 'bookings';

        $customer_query = "SELECT * FROM $wpdb_table WHERE (book_date = '". $date ."' AND book_slot = '". $slot ."')";

        $query_results = $wpdb->get_results($customer_query, ARRAY_A);

        return ( $query_results ) ? false : true;
    }

    /**
     * Save booking
     *
     * @access public
     * @return string|void
     */
    public static function booking_handler() {

        $form_data = array();

        parse_str( $_POST['form'], $form_data );

        $msg = 'Something went wrong!';

        $response = [
            'success' => false,
            'redirect' => false,
            'redirect_to' => $form_data['redirect_to'],
            'msg' => $msg,
            'posted_data' => $form_data
        ];

        if( ! wp_verify_nonce($form_data['tb_booking_save_nonce'], $form_data['action'])){
            $response['msg'] = 'Get a life kidd';
            wp_send_json($response);
        }

        // Insert Booking
        global $wpdb;
        $booking_table = $wpdb->prefix . 'bookings';
        $booking_data = [
            'user_id' => $form_data['user_id'],
            'product_id' => $form_data['product_id'],
            'book_day' => $_POST['day'],
            'book_date' => $form_data['tb-booking__date'],
            'book_slot' => $form_data['tb-booking__slot'],
            'arrival_time' => $form_data['tb-booking__arrival-time'],
            'status' => $form_data['tb-booking__status'],
            'comment' => $form_data['tb-booking__comment'],
            'completed_at' => "0000-00-00 00:00:00",
            'created_at' => current_time('mysql', true)
        ];
        $inserted = $wpdb->insert( $booking_table, $booking_data, $format=[] );

        if ( $wpdb->insert_id != 0 ) {
            $response['msg'] = 'Thank You! Booking save successfully.';
            $response['redirect'] = false;
            $response['success'] = true;
        }else {
            $response['msg'] = $wpdb->last_error;
        }

        wp_send_json($response);

    }

    /**
     * Submission index
     *
     * @access public
     * @return string|void
     */
    public static function generate_booking_form( $atts ) {
        $__logged_in = false;
        ?>
        <div class="container">
            <div class="row">
                <div class="col-sm-10">
                        <?php if ( is_user_logged_in() ): ?>

                            <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" id="tb-booking-form" class="tb-booking tb-booking-form" name="tb-booking-form" enctype="multipart/form-data" encoding="multipart/form-data">
                                <?php wp_nonce_field('tb_save_booking', 'tb_booking_save_nonce'); ?>
                                <input type="hidden" name="product_id" value="<?php echo get_the_ID(); ?>">
                                <input type="hidden" name="product_name" value="<?php echo get_the_title(); ?>">
                                <input type="hidden" name="redirect_to" value="<?php echo get_permalink( self::get('techiebrigade_bookings__redirect_to') ); ?>">
                                <input type="hidden" name="action" value="tb_save_booking">
                                <input type="hidden" name="tb-booking__status" value="pending">
                                <?php $__logged_in = true; ?>
                                <?php $__user = wp_get_current_user(); ?>

                                <input type="hidden" name="user_id" value="<?php echo $__user->ID; ?>">
                                <div class="form-group">
                                    <label for="tb-booking__first-name"><?php esc_html_e( 'First Name', 'tb' ); ?></label>
                                    <input type="text" class="form-control" name="tb-booking__first-name" id="tb-booking__first-name" value="<?php echo Customers_Table::get_user_data( $__user->ID, 'first_name' ); ?>" <?php echo ($__logged_in) ? 'disabled' : ''; ?>>
                                </div>

                                <div class="form-group">
                                    <label for="tb-booking__last-name"><?php esc_html_e( 'Last Name', 'tb' ); ?></label>
                                    <input type="text" class="form-control" name="tb-booking__last-name" id="tb-booking__last-name" value="<?php echo Customers_Table::get_user_data( $__user->ID, 'last_name' ); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="tb_booking_date_time"><?php esc_html_e( 'Select Gender', 'tb' ); ?></label>
                                    <select name="tb-booking__gender" id="tb-booking__gender" class="tb-booking__gender">
                                        <option value="" selected="selected" disabled="disabled">Select Gender</option>
                                        <option value="male" <?php selected( Customers_Table::get_user_data( $__user->ID, 'gender' ), $current = 'male', $echo = true ) ?>>Male</option>
                                        <option value="female" <?php selected( Customers_Table::get_user_data( $__user->ID, 'gender' ), $current = 'female', $echo = true ) ?>>Female</option>
                                        <option value="other" <?php selected( Customers_Table::get_user_data( $__user->ID, 'gender' ), $current = 'other', $echo = true ) ?>>Other</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="tb-booking__email"><?php esc_html_e( 'Email', 'tb' ); ?></label>
                                    <input type="text" class="form-control" name="tb-booking__email" id="tb-booking__email" value="<?php echo $__user->data->user_email; ?>" disabled="disabled">
                                </div>

                                <div class="form-group">
                                    <label for="tb-booking__phone"><?php esc_html_e( 'Phone', 'tb' ); ?></label>
                                    <input type="text" class="form-control" name="tb-booking__phone" id="tb-booking__phone" value="<?php echo Customers_Table::get_user_data( $__user->ID, 'phone' ); ?>" disabled="disabled">
                                </div>

                                <div class="form-group">
                                    <label for="tb-booking__address"><?php esc_html_e( 'Address', 'tb' ); ?></label>
                                    <textarea name="tb-booking__address" id="tb-booking__address" cols="30" rows="10" class="form-control" disabled="disabled"><?php echo Customers_Table::get_user_data( $__user->ID, 'address' ); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="tb-booking__date"><?php esc_html_e( 'Day', 'tb' ); ?></label>
                                    <select name="tb-booking__date" id="tb-booking__date" class="tb-booking__date">
                                        <option value="" selected="selected" disabled="disabled">Select Day</option>
                                        <?php foreach (TB_Helpers::get_next_dates(6) as $day){
                                            $low_case_day = strtolower($day->format('l'));
                                            $option_key = "techiebrigade_bookings_{$low_case_day}_on";
                                            if (self::has( $option_key ) && self::is( $option_key , 'on')){
                                                printf( '<option value="%s" data-day="%s">%s</option>', esc_attr($day->format("Y-m-d")), esc_attr($day->format("l")), esc_html( strtoupper( $day->format('l') ) ) );
                                            }
                                        }?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="tb-booking__slot"><?php esc_html_e( 'Time Slot', 'tb' ); ?></label>
                                    <select name="tb-booking__slot" id="tb-booking__slot" class="tb-booking__slot">
                                        <option value="" selected="selected" disabled="disabled">Firstly Select Day</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="tb-booking__arrival-time"><?php esc_html_e( 'Arrival Time', 'tb' ); ?></label>
                                    <input type="text" class="form-control tb-booking_timepicker" name="tb-booking__arrival-time" id="tb-booking__arrival-time" value="">
                                </div>

                                <div class="form-group">
                                    <label for="tb-booking__comment"><?php esc_html_e( 'Comment', 'tb' ); ?></label>
                                    <textarea name="tb-booking__comment" id="tb-booking__comment" cols="30" rows="10" class="form-control"></textarea>
                                </div>

                                <input type="submit" name="tb-booking__submit-btn" id="tb-booking__submit-btn" class="tb-booking__submit-btn" value="<?php _e( 'Book Now', 'tb' ); ?>" />
                            </form>

                        <?php else: ?>

                            <?php echo do_shortcode( '[nextend_social_login provider="google" style="icon" redirect="'. get_permalink( get_the_ID() ) .'"]' ); ?>
                            <?php echo do_shortcode( '[nextend_social_login provider="facebook" style="icon" redirect="'. get_permalink( get_the_ID() ) .'"]' ); ?>
                            <?php echo do_shortcode( '[nextend_social_login provider="twitter" style="icon" redirect="'. get_permalink( get_the_ID() ) .'"]' ); ?>

                        <?php endif ?>
                </div>
            </div>
        </div>

        <?php


    }
}

TB_Booking_Form::init();
