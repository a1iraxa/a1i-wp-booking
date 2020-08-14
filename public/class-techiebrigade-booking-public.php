<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/a1iraxa
 * @since      1.0.0
 *
 * @package    Techiebrigade_Booking
 * @subpackage Techiebrigade_Booking/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Techiebrigade_Booking
 * @subpackage Techiebrigade_Booking/public
 * @author     Ali Raza <aligcs324@gmail.com>
 */
class Techiebrigade_Booking_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->includes();

	}
	/**
	 * Include classes
	 *
	 * @access public
	 * @return void
	 */
	public function includes() {
		require_once plugin_dir_path( __FILE__ ) . 'class-booking-form.php';
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Techiebrigade_Booking_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Techiebrigade_Booking_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/techiebrigade-booking-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name.'bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( $this->plugin_name.'timepicker', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Techiebrigade_Booking_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Techiebrigade_Booking_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name.'timepicker', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js', array( 'jquery' ), $this->version, false );

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/techiebrigade-booking-public.js', array( 'jquery' ), $this->version, false );
		$_TB_AJAX_DATA = array(
		    'ajax_url' => admin_url( 'admin-ajax.php' ),
		    'current_obj' => get_queried_object()
		);
		wp_localize_script( $this->plugin_name, 'TB_AJAX', $_TB_AJAX_DATA );

	}

}
