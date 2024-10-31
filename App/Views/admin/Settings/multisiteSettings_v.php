<div class="wrap">
    <h1><?php _e('Rundiz PostOrder settings', 'rd-postorder'); ?></h1>
    <p><?php _e('This settings page is for manage all sites.', 'rd-postorder'); ?></p>

    <?php if (isset($form_result_class) && isset($form_result_msg)) { ?> 
    <div class="<?php echo $form_result_class; ?> notice is-dismissible">
        <p>
            <strong><?php echo $form_result_msg; ?></strong>
        </p>
        <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e('Dismiss this notice.'); ?></span></button>
    </div>
    <?php } ?> 

    <form id="rd-postorder-settings-form" method="post">
        <?php wp_nonce_field(); ?> 

        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php _e('Remove order numbers', 'rd-postorder'); ?></th>
                    <td>
                        <label>
                            <input id="rd-postorder-remove-order-numbers" type="checkbox" name="rd-postorder-remove-order-numbers" value="1">
                            <?php _e('Check this box to remove all post orders number and reset them to zero.', 'rd-postorder'); ?> 
                        </label>
                        <p style="background-color: #fff; color: #f00; padding: 2px 3px;"><?php _e('Warning! This will be affect on all sites.', 'rd-postorder'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php submit_button(); ?> 
    </form>
</div>