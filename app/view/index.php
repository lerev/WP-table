<?php defined('ABSPATH') || exit;

/**
 * Table
 */
if ( ! class_exists('WP_List_Table')) {
    require_once ABSPATH.'wp-admin/includes/class-wp-list-table.php';
}

class Plg_Table_View_Admin_Data_Index extends WP_List_Table
{
    public $per_page;

    /**
     * Prepare columns of table for showing
     */
    public function prepare_items()
    {
        global $wpdb;

        /** Determine the total number of records in the database */
        $total_items = $wpdb->get_var("
			SELECT COUNT(`id`)
			FROM `".$wpdb->prefix."debtors`
			{$this -> _getSqlWhere()}
		");

        /** Sets */
        $per_page = $this->get_items_per_page($this->per_page, 10);

        /** Set the data for pagination */
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ));

        /** Get the data to form a table */
        $data = $this->table_data();

        $this->_column_headers = $this->get_column_info();

        /** Set the table data */
        $this->items = $data;
    }

    /**
     * Title of columns of the table
     *
     * @return array
     */
    public function get_columns()
    {
        return array(
            'id'          => '№',
            'name'        => 'Имя',
            'phone'       => 'Телефонный номер',
            'email'       => 'Email',
            'country'     => 'Страна',
            'company'     => 'Компания',
            'contract_id' => 'ID Договора',
            'payment_sum' => 'Сумма платежа',
            'currency'    => 'Валюта',
            'token'       => 'Токен',
            'link'        => 'Ссылка',
            'status'      => 'Статус',
            'date'        => 'Дата',

        );
    }

    /**
     * An array of column names for which sorting is performed
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        return array(
            'id'          => array('id', false),
            'name'        => array('name', false),
            'country'     => array('country', false),
            'company'     => array('company', false),
            'contract_id' => array('contract_id', false),
            'payment_sum' => array('payment_sum', false),
            'currency'    => array('currency', false),
            'status'      => array('status', false),
            'date'        => array('date', false),
        );
    }

    /**
     * Table data
     *
     * @return array
     */
    private function table_data()
    {
        global $wpdb;

        /** Sets */
        $per_page = $this->get_pagination_arg('per_page');
        $order_ar = $this->get_sortable_columns();

        $get_orderby = filter_input(INPUT_GET, 'orderby');
        $order       = filter_input(INPUT_GET, 'order') == 'asc' ? 'asc' : 'desc';
        $orderby     = key_exists($get_orderby, $order_ar) ? $get_orderby : 'date';

        $sql = "SELECT *
			FROM `".$wpdb->prefix."debtors`
			{$this -> _getSqlWhere()}
			ORDER BY `{$orderby}` {$order}
			LIMIT ".(($this->get_pagenum() - 1) * $per_page).", {$per_page}
		";

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Displays if there is no data
     */
    public function no_items()
    {
        echo __('Data not found', 'garnet');
    }

    /**
     * Returns the contents of the column
     *
     * @param  array  $item  item data array
     * @param  string  $column_name  the name of the current column
     *
     * @return mixed
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
//            case 'id':
//            case 'name':
//                return $item[$column_name] ? esc_attr($item[$column_name]) : '-';
            default:
                return $item[$column_name] ? esc_attr($item[$column_name]) : '-';
        }
    }

    /**
     * Returns data from a custom column
     *
     * @param  string  $item
     *
     * @return string
     */
    public function column_name($item)
    {
        return esc_attr($item['name']).$this->row_actions(array(
                'edit'   => '<a href="'.add_query_arg(array('action' => 'edit', 'id' => $item['id'])).'">'.__('edit', 'garnet').'</a>',
                'delete' => '<a href="'.add_query_arg(array('action' => 'delete', 'id' => $item['id'])).'" onclick="return confirm(\''.__('Delete?', 'garnet').'\')">'.__('delete',
                        'garnet').'</a>',
            ));
    }

    /**
     * Returns data from a custom column
     *
     * @param  string  $item
     *
     * @return string
     */
    public function column_date($item)
    {
        return date_i18n(get_option('date_format', 'd.m.Y'), $item['date']);
    }

    /********************************************************************************************************************/
    /************************************************* PRIVATE METHODS **************************************************/
    /********************************************************************************************************************/

    /**
     * Get "where" for sql
     *
     * @return string
     * @global wpdb $wpdb
     */
    private function _getSqlWhere()
    {
        global $wpdb;

        $where = '';
        $get_s = filter_input(INPUT_GET, 's');

        if ($get_s) {
            $where = 'WHERE '.join(' OR ', array(
                    "`name` LIKE  '%".$wpdb->_real_escape($get_s)."%'",
                    "`phone` LIKE  '%".$wpdb->_real_escape($get_s)."%'",
                    "`email` LIKE  '%".$wpdb->_real_escape($get_s)."%'",
                    "`country` LIKE  '%".$wpdb->_real_escape($get_s)."%'",
                    "`company` LIKE  '%".$wpdb->_real_escape($get_s)."%'",
                    "`contract_id` LIKE  '%".$wpdb->_real_escape($get_s)."%'",
                    "`payment_sum` LIKE  '%".$wpdb->_real_escape($get_s)."%'",
                    "`currency` LIKE  '%".$wpdb->_real_escape($get_s)."%'",
                    "`token` LIKE  '%".$wpdb->_real_escape($get_s)."%'",
                    "`link` LIKE  '%".$wpdb->_real_escape($get_s)."%'",
                    "`status` LIKE  '%".$wpdb->_real_escape($get_s)."%'",
                ));
        }

        return $where;
    }
}