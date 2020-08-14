<?php
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class Customers_Table extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => __('customer', 'tb'),
            'plural'   => __('customers', 'tb'),
            'ajax'     => false //should this table support ajax?

        ]);
    }
    public function get_columns()
    {
        $table_columns = array(
            'cb'    => '<input type="checkbox" />',
            'ID'    => __('ID', 'tb'),
            'first_name'   => __('First Name', 'tb'),
            'last_name'   => __('Last Name', 'tb'),
            'gender'  => __('Gender', 'tb'),
            'email'  => __('Email', 'tb'),
            'phone'  => __('Phone', 'tb'),
            'login_name'  => __('Username', 'tb'),
            'address'  => __('Address', 'tb'),
            'registered'  => __('Registered', 'tb'),
            'status'  => __('Status', 'tb'),
        );
        return $table_columns;
    }
    public function no_items()
    {
        _e('No customer avaliable.', 'tb');
    }
    public function prepare_items()
    {
        $customer_search_key = isset($_REQUEST['s']) ? wp_unslash(trim($_REQUEST['s'])) : '';

        $this->_column_headers = $this->get_column_info();

        $this->handle_table_actions();

        $table_data = $this->fetch_table_data();
        if ($customer_search_key) {
          $table_data = $this->filter_table_data($table_data, $customer_search_key);
        }

        $this->items = $table_data;

        $customers_per_page = $this->get_items_per_page('customers_per_page');
        $table_page = $this->get_pagenum();

        $this->items = array_slice($table_data, (($table_page - 1) * $customers_per_page), $customers_per_page);

        // set the pagination arguments
        $total_customers = count($table_data);
        $this->set_pagination_args(array(
            'total_items' => $total_customers,
            'per_page'    => $customers_per_page,
            'total_pages' => ceil($total_customers / $customers_per_page)
        ));
    }
    public function fetch_table_data()
    {
        global $wpdb;
        $wpdb_table = $wpdb->prefix . 'users';
        $wpdb_meta_table = $wpdb->prefix . 'usermeta';
        $orderby = (isset($_GET['orderby'])) ? esc_sql($_GET['orderby']) : 'ID';
        $order = (isset($_GET['order'])) ? esc_sql($_GET['order']) : 'ASC';
        $customer_query = "SELECT * FROM $wpdb_table INNER JOIN wp_usermeta ON wp_users.ID = wp_usermeta.user_id WHERE wp_usermeta.meta_key = 'wp_capabilities' AND (wp_usermeta.meta_value LIKE '%customer%') ORDER BY $orderby $order";
        // query output_type will be an associative array with ARRAY_A.
        $query_results = $wpdb->get_results($customer_query, ARRAY_A);

        // return result array to prepare_items.
        return $query_results;
    }
    public function column_default($item, $column_name)
    {
        $user_id = $item['ID'];
        switch ($column_name) {
            case 'ID':
                return $item[$column_name];
            case 'first_name':
                return self::get_user_data( $user_id, 'first_name' );
            case 'last_name':
                return self::get_user_data( $user_id, 'last_name' );
            case 'gender':
                return self::get_user_data( $user_id, 'gender' );
            case 'email':
                return $item['user_email'];
            case 'phone':
                return self::get_user_data( $user_id, 'phone' );
            case 'login_name':
                return $item['user_login'];
            case 'address':
                return self::get_user_data( $user_id, 'address' );
            case 'registered':
                return $item['user_registered'];
            case 'status':
                return ( $item[ 'user_status' ] ) ? 'Blocked' : 'Active';
            default:
                return $item[$column_name];
        }
    }
    public static function get_user_data($user_id, $meta_key){
        return get_user_meta( $user_id, $meta_key, true );
    }
    protected function column_cb($item)
    {
        return sprintf(
            "<input type='checkbox' name='customers[]' ID='customer_{$item['ID']}' value='{$item['ID']}' />"
        );
    }
    protected function get_sortable_columns()
    {
        $sortable_columns = array(
            'ID'    => array('user_registered', true),
            'first_name'   => false,
            'last_name'   => false,
            'gender'  => false,
            'email'  => false,
            'phone'  => false,
            'login_name'  => false,
            'address'  => false,
            'registered'  => array('user_registered', true),
            'status'  => false,
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
            'bulk-block' => 'Block',
            'bulk-unblock' => 'Unblock',
        );
        return $actions;
    }
    public function handle_table_actions()
    {
        // Update status to completed
        if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'status_completed' ) {
            $nonce = wp_unslash($_REQUEST['_wpnonce']);
            if (!wp_verify_nonce($nonce, 'status_completed_nonce')) {
                die('Go get a life script kiddies');
            }
        }
        // Bulk update status to completed
        if ( isset($_REQUEST['action'] ) || isset($_REQUEST['action2'] ) ) {
          if (
            $_REQUEST['action'] == 'bulk-block' ||
            $_REQUEST['action2'] == 'bulk-block' ||
            $_REQUEST['action'] == 'bulk-unblock' ||
            $_REQUEST['action2'] == 'bulk-unblock'
           ){
              $customers = esc_sql($_REQUEST['customers']);

              $_action = ( $_REQUEST['action'] != -1 ) ? $_REQUEST['action'] : $_REQUEST['action2'];
              $_action = ( $_action == 'bulk-block' ) ? 1 : 0;

              if ( $_action != -1 ) {
                  global $wpdb;

                  $table_name = $wpdb->prefix . 'users';

                  foreach ($customers as $id) {

                      $data = [ 'user_status' => $_action ];

                      $updated = $wpdb->update( $table_name, $data, [ 'ID' => $id] );
                      if ( false === $updated ) {
                          die( 'Something went wrong!' );
                      }
                  }

              }

          }
        }

        if (isset($_REQUEST['_wpnonce'])) {
            $nonce = wp_unslash($_REQUEST['_wpnonce']);
            if (!wp_verify_nonce($nonce, 'bulk-customers')) { // verify the nonce.
                $this->invalid_nonce_redirect();
            } else {
                $this->graceful_exit();
            }
        }
    }
}
