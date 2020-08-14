<?php
class Customers_List {

	// class instance
	static $instance;

	// customer WP_List_Table object
	public $customers_obj;

	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	// class constructor
	public function __construct() {
		add_filter( 'set-screen-option', [ __CLASS__, 'customers_set_screen' ], 10, 3 );
		add_action( 'admin_menu', [ $this, 'customer_menu' ] );
	}
	public static function customers_set_screen( $status, $option, $value ) {
		return $value;
	}
	public function customer_menu() {

		$hook = add_submenu_page(
			'techiebrigade_bookings',
			'Customers',
			'Customers',
			'manage_options',
			'techiebrigade_customers',
			[ $this, 'customers_page' ]
		);

		add_action( "load-$hook", [ $this, 'customers_screen_option' ] );

	}
	/**
	* Plugin settings page
	*/
	public function customers_page() {
		?>
		<div class="wrap">
			<h2>All Customers</h2>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="get">
								<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
								<?php

								$this->customers_obj->prepare_items();
								$this->customers_obj->search_box('search', 'search_id');
								$this->customers_obj->display(); ?>
							</form>
						</div>
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
	public function customers_screen_option() {

		$option = 'per_page';
		$args   = [
			'label'   => 'Customers',
			'default' => 5,
			'option'  => 'customers_per_page'
		];

		add_screen_option( $option, $args );

		$this->customers_obj = new Customers_Table();

	}
}
