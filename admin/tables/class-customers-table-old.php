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
  /**
   * Retrieve customer’s data from the database
   *
   * @param int $per_page
   * @param int $page_number
   *
   * @return mixed
   */
  public static function get_customers($per_page = 5, $page_number = 1)
  {
    global $wpdb;
    $args = array(
      'role'    => 'customer',
      'orderby' => !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : ' ASC',
      'order'   => !empty($_REQUEST['order']) ? $_REQUEST['order'] : ' ASC',
      'offset'   => ($page_number - 1) * $per_page,
      'number'   => $per_page,
    );

    return get_users($args);
  }
  /**
   * Delete a customer record.
   *
   * @param int $id customer ID
   */
  public static function delete_customer($id)
  {
    global $wpdb;

    $wpdb->delete(
      "{$wpdb->prefix}users",
      ['ID' => $id],
      ['%d']
    );
  }
  /**
   * Returns the count of records in the database.
   *
   * @return null|string
   */
  public static function record_count()
  {
    $args = array(
      'role'    => 'customer',
      'number'   => -1,
    );
    return count(get_users($args));
  }
  /** Text displayed when no customer data is available */
  public function no_items()
  {
    _e('No customers avaliable.', 'sp');
  }
  /**
   * Method for name column
   *
   * @param array $item an array of DB data
   *
   * @return string
   */
  function column_display_name($item)
  {

    $delete_nonce = wp_create_nonce('tb_delete_customer');

    $title = '<strong>' . $item->display_name . '</strong>';

    $actions = [
      'delete' => sprintf('<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['page']), 'delete', absint($item->ID), $delete_nonce)
    ];

    return $title . $this->row_actions($actions);
  }
  /**
   * Render a column when no column specific method exists.
   *
   * @param array $item
   * @param string $column_name
   *
   * @return mixed
   */
  public function column_default($item, $column_name)
  {
    switch ($column_name) {
      case 'user_login':
        return $item->user_login;
      case 'display_name':
        return $item->user_nicename;
      case 'user_email':
        return $item->user_email;
      default:
        return print_r($item, true); //Show the whole array for troubleshooting purposes
    }
  }
  /**
   * Render the bulk edit checkbox
   *
   * @param array $item
   *
   * @return string
   */
  function column_cb($item)
  {
    return sprintf('<input type="checkbox" name="bulk-delete[]" value="%s" />', $item->ID);
  }
  /**
   *  Associative array of columns
   *
   * @return array
   */
  function get_columns()
  {
    $c = array(
      'cb'       => '<input type="checkbox" />',
      'user_login' => __('Username'),
      'display_name'     => __('Name'),
      'user_email'    => __('Email'),
    );

    if ($this->is_site_users) {
      unset($c['posts']);
    }

    return $c;
  }
  /**
   * Columns to make sortable.
   *
   * @return array
   */
  public function get_sortable_columns()
  {
    $sortable_columns = array(
      'user_login' => true,
      'display_name'     => true,
      'user_email'    => false,
    );

    return $sortable_columns;
  }
  /**
   * Returns an associative array containing the bulk action
   *
   * @return array
   */
  public function get_bulk_actions()
  {
    if (current_user_can('delete_users')) {
      $actions = ['bulk-delete' => 'Delete'];
    }
    return $actions;
  }
  /**
   * Handles data query and filter, sorting, and pagination.
   */
  public function prepare_items()
  {

    $this->_column_headers = $this->get_column_info();

    /** Process bulk action */
    $this->process_bulk_action();

    $per_page     = $this->get_items_per_page('customers_per_page', 5);
    $current_page = $this->get_pagenum();
    $total_items  = self::record_count();

    $this->set_pagination_args([
      'total_items' => $total_items, //WE have to calculate the total number of items
      'per_page'    => $per_page //WE have to determine how many items to show on a page
    ]);

    $this->items = self::get_customers($per_page, $current_page);
  }
  public function process_bulk_action()
  {
    //Detect when a bulk action is being triggered...
    if ('delete' === $this->current_action()) {

      $nonce = esc_attr($_REQUEST['_wpnonce']);

      if (!wp_verify_nonce($nonce, 'tb_delete_customer')) {
        die('Go get a life script kiddies');
      }

      self::delete_customer(absint($_GET['customer']));
      wp_redirect(esc_url(add_query_arg()));
      exit;
    }

    // If the delete bulk action is triggered
    if ((isset($_POST['action']) && $_POST['action'] == 'bulk-delete')
      || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete')
    ) {

      $delete_ids = esc_sql($_POST['bulk-delete']);

      // loop over the array of record IDs and delete them
      foreach ($delete_ids as $id) {
        self::delete_customer($id);
      }

      wp_redirect(esc_url(add_query_arg()));
      exit;
    }
  }
}
