<?php
class Bookings_List {

	// class instance
	static $instance;

	// customer WP_List_Table object
	public $books_obj;

	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	// class constructor
	public function __construct() {
		add_filter( 'set-screen-option', [ __CLASS__, 'bookings_set_screen' ], 10, 3 );
		add_action( 'admin_menu', [ $this, 'booking_menu' ] );
	}
	public static function bookings_set_screen( $status, $option, $value ) {
		return $value;
	}
	public function booking_menu() {

		$hook = add_menu_page(
			'Bookings',
			'Bookings',
			'manage_options',
			'techiebrigade_bookings',
			[ $this, 'bookings_page' ]
		);

		add_action( "load-$hook", [ $this, 'bookings_screen_option' ] );

	}
	/**
	* Plugin settings page
	*/
	public function bookings_page() {
		?>
		<div class="wrap">
			<h2> <?php echo ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'view_booking' ) ? 'Booking' : 'All Bookings' ?> </h2>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder">
					<div id="post-body-content">
						<?php if ( isset( $_REQUEST['message'] ) && !empty( $_REQUEST['message'] ) ): ?>
							<div class="updated notice">
							    <p>Status Updated Successfully!</p>
							</div>
						<?php endif ?>
						<?php if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'view_booking' ): ?>

							<?php $booking_user_obj = TB_Helpers::get_booking($_REQUEST['booking_id']); ?>

							<div id="booking-page" class="booking-page postbox">
								<h2 class="hndle "><span>Booking # <?php echo $booking_user_obj['booking']->ID; ?></span></h2>
								<div class="inside">
									<table class="widefat fixed striped">
										<tr>
											<td><strong>Name: </strong><?php echo $booking_user_obj['user']->display_name; ?></td>
											<td><strong>Email: </strong><?php echo $booking_user_obj['user']->user_email; ?></td>
											<td><strong>Phone: </strong><?php echo get_user_meta( $booking_user_obj['user']->ID, 'phone',true ); ?></td>
										</tr>
										<tr>
											<td><strong>Booking Day: </strong><?php echo $booking_user_obj['booking']->book_day; ?></td>
											<td><strong>Booking Date: </strong><?php echo $booking_user_obj['booking']->book_date; ?></td>
											<td><strong>Booking Time: </strong><?php echo $booking_user_obj['booking']->book_slot; ?></td>
										</tr>
										<tr>
											<td colspan="3"><strong>Address: </strong><?php echo get_user_meta( $booking_user_obj['user']->ID, 'address',true ); ?></td>
										</tr>
										<tr>
											<td><strong>Created At: </strong><?php echo $booking_user_obj['booking']->created_at; ?></td>
											<td><strong>Completed At: </strong><?php echo $booking_user_obj['booking']->completed_at; ?></td>
											<td><strong>Status: </strong><?php echo $booking_user_obj['booking']->status; ?></td>
										</tr>
										<tr>
											<td colspan="3"><strong>Comments: </strong><?php echo $booking_user_obj['booking']->comment; ?></td>
										</tr>
										<?php if ( 'completed' != trim( TB_Helpers::get_booking_status( $_REQUEST['booking_id'] ) ) ): ?>
											<tr>
												<td><a href="<?php echo TB_Helpers::get_status_update_url( $_REQUEST['booking_id'] ); ?>" class="button button-primary button-large" aria-label="Please mark Booking completed">Please mark completed!</a></td>
											</tr>
										<?php endif ?>
									</table>
								</div>
							</div>
						<?php else: ?>
							<div class="meta-box-sortables ui-sortable">
								<form id="bookings-list-form" method="get">
									<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
									<?php
									$this->books_obj->prepare_items();
									$this->books_obj->search_box( __( 'Find', 'tb' ), 'booking-find');
									$this->books_obj->display(); ?>
								</form>
							</div>
						<?php endif ?>

					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	<?php
	}
	/**
	* Screen options
	*/
	public function bookings_screen_option() {

		$option = 'per_page';
		$args   = [
			'label'   => 'Bookings',
			'default' => 5,
			'option'  => 'bookings_per_page'
		];

		add_screen_option( $option, $args );

		$this->books_obj = new Bookings_Table();

	}
}
