<?php
/**
 * Hook in and register a metabox to handle a booking customers
 */
function techiebrigade_bookings_options() {
	/**
	 * Registers main options page menu item and form.
	 */
	$__prefix = 'techiebrigade_bookings_';
	$main_options = new_cmb2_box( array(
		'id'           => $__prefix. '_options_page',
		'object_types' => array( 'options-page' ),
		'parent_slug'  => 'techiebrigade_bookings',
		'option_key'      => $__prefix. 'options',
		'menu_title'      => esc_html__( 'Booking Options', 'cmb2' ),
		'save_button'     => esc_html__( 'Save Booking\'s Options', 'cmb2' ),
	));
	$days = [
		'monday' => 'Monday',
		'tuesday' => 'Tuesday',
		'wednesday' => 'Wednesday',
		'thursday' => 'thursday',
		'friday' => 'Friday',
		'saturday' => 'Saturday',
		'sunday' => 'Sunday',
	];
	foreach ($days as $key => $day) {
		$main_options->add_field( array(
			'name' => $day,
			'desc' => "Generate Time Slots For $day",
			'type' => 'title',
			'id'   => $__prefix. $key .'_title'
		));
		$main_options->add_field( array(
			'name' => "$day ON",
			'desc' => "Check this for {$day} ON",
			'id'   => $__prefix. $key .'_on',
			'type' => 'checkbox',
		));
		$monday_time_slot_id = $main_options->add_field( array(
			'id'   		  => $__prefix. $key .'_slot',
			'type'        => 'group',
			'options'     => array(
				'group_title'       => __( "$day Slot {#}", 'cmb2' ),
				'add_button'        => __( "Add New $day Slot", 'cmb2' ),
				'remove_button'     => __( "Remove $day Slot", 'cmb2' ),
				'sortable'          => true,
				'closed'          => true,
			),
		));
		$main_options->add_group_field( $monday_time_slot_id, array(
			'name' => 'Slot Start From: ',
			'id'   => $__prefix. $key .'_slot_start',
			'type' => 'text_time',
		));
		$main_options->add_group_field( $monday_time_slot_id, array(
			'name' => 'Slot End To: ',
			'id'   => $__prefix. $key .'_slot_end',
			'type' => 'text_time',
		));
	}
	$main_options->add_field( array(
		'name' => 'Others',
		'desc' => "Others ",
		'type' => 'title',
		'id'   => $__prefix. 'others_title'
	));
	$main_options->add_field( array(
		'name' => "Page ID",
		'desc' => "Please Enter Thank you page ID",
		'id'   => $__prefix. '_redirect_to',
		'type' => 'text',
	));

}
add_action( 'cmb2_admin_init', 'techiebrigade_bookings_options' );
