<div class="wrap">
    <h1><?php _e('Rundiz PostOrder settings', 'rd-postorder'); ?></h1>

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
                    <th scope="row"><?php _e('Disable on front page', 'rd-postorder'); ?></th>
                    <td>
                        <label for="disable_customorder_frontpage">
                            <input id="disable_customorder_frontpage" type="checkbox" name="disable_customorder_frontpage" value="1"<?php checked((isset($options['disable_customorder_frontpage']) ? $options['disable_customorder_frontpage'] : null), '1'); ?>>
                            <?php _e('Disable custom post order on front page.', 'rd-postorder'); ?> 
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Disable on categories', 'rd-postorder'); ?></th>
                    <td>
                        <p><?php _e('Please tick on the check box of category you want to disable custom post order.', 'rd-postorder'); ?></p>
                        <fieldset>
                            <legend class="screen-reader-text"><?php _e('Disable on categories', 'rd-postorder'); ?></legend>
                            <?php
                            if (isset($categories) && is_array($categories) && !empty($categories)) { 
                                foreach ($categories as $id => $name) {
                                    if (
                                        isset($options['disable_customorder_categories']) && 
                                        is_array($options['disable_customorder_categories']) &&
                                        in_array($id, $options['disable_customorder_categories'])
                                    ) {
                                        $checked = ' checked="checked"';
                                    }
                            ?> 
                            <label>
                                <input type="checkbox" name="disable_customorder_categories[]" value="<?php echo $id; ?>"<?php if (isset($checked)) {echo $checked;} ?>>
                                <?php echo $name; ?> 
                            </label>
                            <br>
                            <?php
                                    unset($checked);
                                }// endforeach;
                                unset($id, $name);
                            }// endif $categories
                            unset($categories);
                            ?> 
                        </fieldset>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php submit_button(); ?> 
    </form>
</div>