<?php defined('ABSPATH') || exit;

class Plg_Table_Controller_Data
{
    /**
     * Table class
     */
    private $Table;

    /**
     * Validate form
     */
    private $Validate;

    //===========================================================
    // Light version [START]
    //===========================================================

    public function __construct()
    {
        add_action('admin_head', array($this, 'styleIndex'));
    }

    public function action()
    {
        switch (filter_input(INPUT_GET, 'action')) {
            case 'add':
                $this->actionAdd();
                break;
            case 'edit':
                $this->actionEdit();
                break;
            case 'delete':
                $this->actionDelete();
                break;
            default:
                $this->actionIndex();
                break;
        }
    }

    public function view()
    {
        switch (filter_input(INPUT_GET, 'action')) {
            case 'add':
                $this->viewAdd();
                break;
            case 'edit':
                $this->viewEdit();
                break;
            default:
                $this->viewIndex();
                break;
        }
    }


    //===========================================================
    // Actions
    //===========================================================
    /**
     * List data
     */
    public function actionIndex()
    {
        $this->Table           = new Plg_Table_View_Admin_Data_Index;
        $this->Table->per_page = 25;
    }

    /**
     * Data create
     *
     * @global wpdb $wpdb
     */
    public function actionAdd()
    {
        global $wpdb;

        $this->Validate = $this->_validate();

        if (Plg_Table_Helpers::isRequestPost() && $this->Validate->validate()) {
            $data_ar = $this->Validate->getData();

            $wpdb->insert(
                $wpdb->prefix.'debtors',
                array(
                    'name'        => $data_ar['name'],
                    'phone'       => $data_ar['phone'],
                    'email'       => $data_ar['email'],
                    'country'     => $data_ar['country'],
                    'company'     => $data_ar['company'],
                    'payment_sum' => $data_ar['payment_sum'],
                    'currency'    => $data_ar['currency'],
                    'status'      => $data_ar['status'],
                    'date'        => time(),
                ),
                array('%s', '%d', '%s', '%s', '%s', '%d', '%s', '%s')
            );

            Plg_Table_Helpers::flashRedirect(add_query_arg(array('action' => 'add')), __('Data created', 'garnet'));
        }

        if ($this->Validate->isErrors()) {
            Plg_Table_Helpers::flashShow('error', $this->Validate->getErrors());
        }
    }

    /**
     * Data update
     *
     * @global wpdb $wpdb
     */
    public function actionEdit()
    {
        global $wpdb;

        //Sets
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $this->Validate = $this->_validate();

        if (Plg_Table_Helpers::isRequestPost() && Plg_Table_Helpers::isRequestAjax() == false && $this->Validate->validate()) {
            $data_ar = $this->Validate->getData();

            $wpdb->update(
                $wpdb->prefix.'debtors',
                array(
                    'name'        => $data_ar['name'],
                    'phone'       => $data_ar['phone'],
                    'email'       => $data_ar['email'],
                    'country'     => $data_ar['country'],
                    'company'     => $data_ar['company'],
                    'payment_sum' => $data_ar['payment_sum'],
                    'currency'    => $data_ar['currency'],
                    'status'      => $data_ar['status'],
                ),
                array('id' => $id),
                array('%s', '%d', '%s', '%s', '%s', '%d', '%s', '%s'),
                array('%d')
            );

            Plg_Table_Helpers::flashRedirect(add_query_arg(array('action' => 'edit', 'id' => $id)), __('Data updated', 'garnet'));
        } elseif (Plg_Table_Helpers::isRequestPost() == false) {
            $data_ar = $wpdb->get_row("SELECT *
				FROM `".$wpdb->prefix."debtors`
				WHERE `id` = ".$id."
				LIMIT 1",
                ARRAY_A);

            if ($data_ar === null) {
                wp_die(__('Page not found', 'garnet'));
            }

            $this->Validate->setData($data_ar);
        }

        if ($this->Validate->isErrors()) {
            Plg_Table_Helpers::flashShow('error', $this->Validate->getErrors());
        }
    }

    /**
     * Delete
     *
     * @global wpdb $wpdb
     */
    public function actionDelete()
    {
        global $wpdb;

        $wpdb->delete(
            $wpdb->prefix.'debtors',
            array('id' => filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT)),
            array('%d')
        );

        Plg_Table_Helpers::flashRedirect(remove_query_arg(array('action', 'id')), __('Data deleted', 'garnet'));
    }

    //===========================================================
    // Styles
    //===========================================================

    /**
     * List
     */
    public function styleIndex()
    {
        echo '<style type="text/css">';
        echo '.wp-list-table .column-id { width: 5%; }';
        echo '.wp-list-table .column-date  { width: 150px; }';
        echo '</style>';
    }

    //===========================================================
    // Views
    //===========================================================

    /**
     * List data
     */
    public function viewIndex()
    {
        $this->Table->prepare_items();

        $btn_add_url = http_build_query(array(
            'page'   => filter_input(INPUT_GET, 'page'),
            'action' => 'add',
        ));
        ?>
        <div class="wrap">
            <h2>
                <?php echo __('List data', 'garnet') ?>
                <a href="?<?php echo $btn_add_url ?>" class="page-title-action"><?php echo __('Add data', 'garnet') ?></a>
            </h2>
            <form method="get">
                <input type="hidden" name="page" value="<?php echo filter_input(INPUT_GET, 'page') ?>"/>
                <?php $this->Table->search_box(__('Search', 'garnet'), 'search_id'); ?>
                <?php $this->Table->display(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Data create
     */
    public function viewAdd()
    {
        echo Plg_Table_Helpers::view(PLG_TABLE__PATH.'app/view/add', array(
            'page_title'   => __('Data creating', 'garnet'),
            'form_actiion' => add_query_arg(array('action' => 'add')),
            'Validate'     => $this->Validate,
        ));
    }

    public function viewEdit()
    {
        echo Plg_Table_Helpers::view(PLG_TABLE__PATH.'app/view/add', array(
            'page_title'   => __('Data editing', 'garnet'),
            'form_actiion' => add_query_arg(array('action' => 'edit', 'id' => filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT))),
            'Validate'     => $this->Validate,
        ));
    }

    //===========================================================
    // Validate
    //===========================================================

    /**
     * Validate
     *
     * @return PlanceValidate
     */
    private function _validate()
    {
        return Plg_Table_Validate::factory(wp_unslash($_POST))
                                 ->setLabels(array(
                                     'name'        => 'Имя',
                                     'phone'       => 'Телефонный номер',
                                     'email'       => 'Email',
                                     'country'     => 'Страна',
                                     'company'     => 'Компания',
                                     'payment_sum' => 'Сумма платежа',
                                     'currency'    => 'Валюта',
                                     'status'      => 'Статус',
                                 ))
                                 ->setFilters('name', array(
                                     'trim'       => array(),
                                     'strip_tags' => array(),
                                 ))
                                 ->setRules('name', array(
                                     'required'   => array(),
                                     'max_length' => array(50),
                                 ))
                                 ->setFilters('phone', array(
                                     'trim'       => array(),
                                     'strip_tags' => array(),
                                 ))
                                 ->setRules('phone', array(
                                     'required'   => array(),
                                     'max_length' => array(20),
                                 ))
                                 ->setFilters('email', array(
                                     'trim'       => array(),
                                     'strip_tags' => array(),
                                 ))
                                 ->setRules('email', array(
                                     'required'   => array(),
                                     'max_length' => array(80),
                                 ))
                                 ->setFilters('country', array(
                                     'trim'       => array(),
                                     'strip_tags' => array(),
                                 ))
                                 ->setRules('country', array(
                                     'required'   => array(),
                                     'max_length' => array(30),
                                 ))
                                 ->setFilters('company', array(
                                     'trim'       => array(),
                                     'strip_tags' => array(),
                                 ))
                                 ->setRules('company', array(
                                     'required'   => array(),
                                     'max_length' => array(70),
                                 ))
                                 ->setFilters('payment_sum', array(
                                     'trim'       => array(),
                                     'strip_tags' => array(),
                                 ))
                                 ->setRules('payment_sum', array(
                                     'required'   => array(),
                                     'max_length' => array(255),
                                 ))
                                 ->setFilters('currency', array(
                                     'trim'       => array(),
                                     'strip_tags' => array(),
                                 ))
                                 ->setRules('currency', array(
                                     'required'   => array(),
                                     'max_length' => array(20),
                                 ))
                                 ->setFilters('status', array(
                                     'trim'       => array(),
                                     'strip_tags' => array(),
                                 ))
                                 ->setRules('status', array(
                                     'required'   => array(),
                                     'max_length' => array(255),
                                 ));
    }
}