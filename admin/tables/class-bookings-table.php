<?php
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Bookings_Table extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => __('booking', 'tb'),
            'plural'   => __('bookings', 'tb'),
            'ajax'     => false //should this table support ajax?

        ]);
    }
    public function get_columns()
    {
        $table_columns = array(
            'cb'    => '<input type="checkbox" />', // to display the checkbox.
            'ID'    => __('ID', 'tb'),
            'book_day'   => __('Day', 'tb'),
            'book_date'   => __('Date', 'tb'),
            'book_slot'  => __('Slot', 'tb'),
            'arrival_time'  => __('Arrival time', 'tb'),
            'user_id'  => __('Customer', 'tb'),
            'status'  => __('Status', 'tb'),
            'comment'  => __('Comment', 'tb'),
            'completed_at'  => __('Completed At', 'tb'),
            'created_at'  => __('Created At', 'tb'),
        );
        return $table_columns;
    }
    public function no_items()
    {
        _e('No booking avaliable.', 'tb');
    }
    public function prepare_items()
    {
        $booking_search_key = isset($_REQUEST['s']) ? wp_unslash(trim($_REQUEST['s'])) : '';

        $this->_column_headers = $this->get_column_info();

        $this->handle_table_actions();

        $table_data = $this->fetch_table_data();
        if ($booking_search_key) {
          $table_data = $this->filter_table_data($table_data, $booking_search_key);
        }

        $this->items = $table_data;

        $bookings_per_page = $this->get_items_per_page('bookings_per_page');
        $table_page = $this->get_pagenum();

        $this->items = array_slice($table_data, (($table_page - 1) * $bookings_per_page), $bookings_per_page);

        // set the pagination arguments
        $total_bookings = count($table_data);
        $this->set_pagination_args(array(
            'total_items' => $total_bookings,
            'per_page'    => $bookings_per_page,
            'total_pages' => ceil($total_bookings / $bookings_per_page)
        ));
    }
    public function fetch_table_data()
    {
        global $wpdb;
        $wpdb_table = $wpdb->prefix . 'bookings';
        $orderby = (isset($_GET['orderby'])) ? esc_sql($_GET['orderby']) : 'ID';
        $order = (isset($_GET['order'])) ? esc_sql($_GET['order']) : 'ASC';
        $booking_query = "SELECT * FROM $wpdb_table ORDER BY $orderby $order";
        // query output_type will be an associative array with ARRAY_A.
        $query_results = $wpdb->get_results($booking_query, ARRAY_A);

        // return result array to prepare_items.
        return $query_results;
    }
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'ID':
            case 'book_day':
            case 'book_date':
            case 'book_slot':
            case 'arrival_time':
            case 'user_id':
            case 'status':
            case 'comment':
            case 'completed_at':
            case 'created_at':
                return $item[$column_name];
            default:
                return $item[$column_name];
        }
    }
    protected function column_cb($item)
    {
        return sprintf(
            "<input type='checkbox' name='bookings[]' ID='booking_{$item['ID']}' value='{$item['ID']}' />"
        );
    }
    protected function get_sortable_columns()
    {
        $sortable_columns = array(
            'ID' => array('ID', true),
            'book_day' => false,
            'book_date' => array('book_date', true),
            'book_slot' => false,
            'user_id' => false,
            'status' => array( 'status', true),
            'comment' => false,
            'completed_at' => false,
            'created_at' => array('created_at', true),
        );
        return $sortable_columns;
    }
    public function filter_table_data($table_data, $search_key)
    {
        $filtered_table_data = array_values(array_filter($table_data, function ($row) use ($search_key) {
            foreach ($row as $row_val) {
                if (stripos($row_val, $search_key) !== false) {
                    return true;
                }
            }
        }));
        return $filtered_table_data;
    }
    public function get_bulk_actions()
    {
        $actions = array(
            'bulk-pending' => 'Pending',
            'bulk-awaiting' => 'Awaiting Payment',
            'bulk-paid' => 'Paid',
            'bulk-completed' => 'Completed',
        );
        return $actions;
    }
    public function handle_table_actions()
    {
        // echo "<pre>";print_r( $_REQUEST );echo "</pre>";die;
        // Update status to completed
        if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'status_completed' ) {
            $nonce = wp_unslash($_REQUEST['_wpnonce']);
            if (!wp_verify_nonce($nonce, 'status_completed_nonce')) {
                die('Go get a life script kiddies');
            }

            TB_Helpers::update_status($_REQUEST['booking_id'], 'completed');
            wp_redirect( TB_Helpers::get_view_booking_url( $_REQUEST['booking_id'], 'updated' ) );
            exit;
        }
        // Bulk update status to completed
        if ( isset($_REQUEST['action'] ) || isset($_REQUEST['action2'] ) ) {
          if (
            $_REQUEST['action'] == 'bulk-awaiting' ||
            $_REQUEST['action2'] == 'bulk-awaiting' ||
            $_REQUEST['action'] == 'bulk-paid' ||
            $_REQUEST['action2'] == 'bulk-paid' ||
            $_REQUEST['action'] == 'bulk-completed' ||
            $_REQUEST['action2'] == 'bulk-completed' ||
            $_REQUEST['action'] == 'bulk-pending' ||
            $_REQUEST['action2'] == 'bulk-pending'
           ){
              $bookings = esc_sql($_REQUEST['bookings']);

              $_action = ( $_REQUEST['action'] != -1 ) ? $_REQUEST['action'] : $_REQUEST['action2'];

              if ( $_action != -1 ) {

                  $status = str_replace("bulk-","", $_action);

                  foreach ($bookings as $id) {
                      TB_Helpers::update_status( $id, $status);
                  }

                  wp_redirect( TB_Helpers::get_bookings_url( 'Updated Successfully!' ) );
                  exit;
              }

          }
        }

        if (isset($_REQUEST['_wpnonce'])) {
            $nonce = wp_unslash($_REQUEST['_wpnonce']);
            if (!wp_verify_nonce($nonce, 'bulk-bookings')) { // verify the nonce.
                $this->invalid_nonce_redirect();
            } else {
                $this->graceful_exit();
            }
        }
    }
    protected function column_id($item)
    {
      $view_booking_link = TB_Helpers::get_view_booking_url($item['ID']);
      $actions['view_booking'] = '<a href="' . $view_booking_link . '">' . __('View', 'tb') . '</a>';
      $row_value = '<strong><a href="' . $view_booking_link . '">Booking # ' . $item['ID'] . '</a></strong>';
      return $row_value . $this->row_actions($actions);
    }
    protected function column_status($item)
    {
          $actions = [];
          $view_booking_link = TB_Helpers::get_status_update_url($item['ID']);
          $booking_status = TB_Helpers::get_booking_status($item['ID']);
          if ( 'completed' != trim( $booking_status ) ) {
                $actions['completed_booking'] = '<a href="' . $view_booking_link . '">' . __('Mark Completed', 'tb') . '</a>';
          }
          $row_value = '<strong>' . ucfirst($item['status']) . '</strong>';
          return $row_value . $this->row_actions($actions);

    }
    protected function column_user_id($item)
    {
        return TB_Helpers::get_user_display_name($item['ID']);
    }


}
