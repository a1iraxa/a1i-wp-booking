<?php
class TB_Helpers {

	// class instance
	static $instance;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	// class constructor
	public function __construct() {
		add_filter( 'init', [ __CLASS__, 'fixed_redirect_issue' ], 10);
	}

	public static function fixed_redirect_issue()
	{
		ob_start();
	}
	public static function get_user_display_name($booking_id='')
	{
		$__return = self::get_booking($booking_id);
		return $__return['user']->display_name;
	}

    public static function get_booking_status($booking_id='')
    {
        $__return = self::get_booking($booking_id);
        return $__return['booking']->status;
    }
	public static function get_bookings_url($message=''){
		$menu_page_url =  menu_page_url('techiebrigade_bookings', false);
		$query_args_view_booking = array(
		  'page'      =>  wp_unslash($_REQUEST['page']),
		  'message'   => $message,
		);

		return add_query_arg($query_args_view_booking, $menu_page_url);
	}
	public static function get_view_booking_url($booking_id='', $message=''){

	    if ( !empty( $booking_id ) ) {

	        $menu_page_url =  menu_page_url('techiebrigade_bookings', false);
	        $query_args_view_booking = array(
	          'page'      =>  wp_unslash($_REQUEST['page']),
	          'action'    => 'view_booking',
	          'booking_id'   => absint( $booking_id ),
	          'message'   => $message,
	          '_wpnonce'  => wp_create_nonce('view_booking_nonce'),
	        );

	        return add_query_arg($query_args_view_booking, $menu_page_url);
	    }

	    return '';
	}

	public static function get_status_update_url($booking_id='')
	{
	  	if ( !empty( $booking_id ) ) {
			$menu_page_url =  menu_page_url('techiebrigade_bookings', false);
			$query_args_status_update = array(
				'page'      =>  wp_unslash($_REQUEST['page']),
				'action'    => 'status_completed',
				'booking_id'   => absint( $booking_id ),
				'_wpnonce'  => wp_create_nonce('status_completed_nonce'),
			);
			return esc_url(add_query_arg($query_args_status_update, $menu_page_url));
	  	}

	  	return '';
	}
	public static function get_booking($id='')
	{
		global $wpdb;
		$__return = [];

		$booking_table = $wpdb->prefix . 'bookings';
		$user_table = $wpdb->prefix . 'users';
		$booking_query = "SELECT * FROM $booking_table WHERE ID={$id}";

		$booking = $wpdb->get_row($booking_query);

		$__return['booking'] = $booking;

		if ( $booking != '' ) {
			$user = get_user_by('ID', $booking->user_id);
			$__return['user'] = $user->data;
		}


		return $__return;
	}
	public static function update_status($booking_id='', $status='completed')
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'bookings';

		$data = [
			'status' => $status,
			'completed_at' => current_time( 'mysql' ),
		];

		$updated = $wpdb->update( $table_name, $data, [ 'ID' => $booking_id] );
		if ( false === $updated ) {
		    die( 'Something went wrong!' );
		}
		return true;
	}
    /**
    * Get next years associative array from given number
    * @param year int
    * @return true/false
    */
    public static function get_next_dates($_days=15)
    {
        $days   = [];
        $period = new DatePeriod(
            new DateTime(),
            new DateInterval('P1D'),
            $_days
        );

        return $period;
    }

}
new TB_Helpers();

// Just for development
add_action( 'init', function () {
	global $wpdb;
  	$users_arr = array(
  		['name' => 'foo', 'email' => 'foo@test.com' ],
  		['name' => 'bar', 'email' => 'bar@test.com' ],
  		['name' => 'baz', 'email' => 'baz@test.com' ],
  		['name' => 'foobar', 'email' => 'foobar@test.com' ],
  		['name' => 'foobaz', 'email' => 'foobaz@test.com' ],
  	);
  	foreach ($users_arr as $key => $user) {
  		$username = $user['name'];
  		$password = 'password';
  		$email_address = $user['email'];
  		if ( ! username_exists( $username ) ) {
  			$user_id = wp_create_user( $username, $password, $email_address );
  			$user = new WP_User( $user_id );
  			$user->set_role( 'customer' );

  			$gender = array_rand( [ 'Male'=>'Male', 'Female'=>'Female' ] );
  			add_user_meta( $user_id, 'gender',  $gender );
  			add_user_meta( $user_id, 'address',  "This is dummy address!" );

  			// Insert Booking
  			$booking_table = $wpdb->prefix . 'bookings';
  			$booking_data = [
  				'user_id' => $user_id,
  				'product_id' => rand(1,20),
  				'book_day' => array_rand( [ 'Monday'=>'Monday', 'Tuesday'=>'Tuesday', 'Wednesday'=>'Wednesday', 'Thursday'=>'Thursday', 'Friday'=>'Friday', 'Saturday'=>'Saturday', 'Sunday'=>'Sunday' ] ),
  				'book_date' => array_rand( [ '05-08-2019'=>'05-08-2019', '06-08-2019'=>'06-08-2019', '07-08-2019'=>'07-08-2019', '08-08-2019'=>'08-08-2019' ] ),
  				'book_slot' => array_rand( [ '9:00AM-12:30PM'=>'9:00AM-12:30PM', '1:00AM-5:30PM'=>'1:00AM-5:30PM', '7:00AM-11:30PM'=>'7:00AM-11:30PM' ] ),
  				'status' => array_rand( [ 'pending'=>'pending', 'completed'=>'completed', 'awaiting'=>'awaiting', 'paid'=>'paid' ] ),
  				'comment' => "",
  				'completed_at' => current_time('mysql', true),
  				'created_at' => current_time('mysql', true)
  			];
  			$wpdb->insert( $booking_table, $booking_data, $format=[] );
  		}
  	}

} );
