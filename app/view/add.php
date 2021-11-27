<?php defined('ABSPATH') || exit;

$editArr = array(
    'name',
    'phone',
    'email',
    'country',
    'company',
//    'contract_id',
    'payment_sum',
    'currency',
//    'token',
//    'link',
    'status',
); ?>

<div class="wrap">
    <h2>
        <?php echo $page_title ?>
        <a href="<?php echo remove_query_arg(array('action', 'id', 'orderby', 'order')) ?>" class="page-title-action"><?php echo __('List data', 'garnet') ?></a>
    </h2>
    <form method="post" action="<?php echo $form_actiion ?>" class="xyz-form-create">
        <table class="form-table">
            <?php foreach ($editArr as $elm) : ?>
                <tr>
                    <th scope="row"><?php echo $Validate->getLabel($elm) ?></th>
                    <td>
                        <input name="<?php echo $elm; ?>" type="text" class="f-text" value="<?php echo esc_attr($Validate->getData($elm)) ?>">
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php submit_button(); ?>
    </form>
</div>