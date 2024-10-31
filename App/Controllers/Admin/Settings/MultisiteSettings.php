<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace RdPostOrder\App\Controllers\Admin\Settings;


if (!class_exists('\\RdPostOrder\\App\\Controllers\\Admin\\Settings\\MultisiteSettings')) {
    class MultisiteSettings implements \RdPostOrder\App\Controllers\ControllerInterface
    {


        use \RdPostOrder\App\AppTrait;


        /**
         * Add menu to network admin page.
         */
        public function adminMenuAction()
        {
            add_submenu_page('settings.php', __('Rundiz PostOrder', 'rd-postorder'), __('Rundiz PostOrder', 'rd-postorder'), 'manage_network_plugins', 'rd-postorder-networksettings', [$this, 'networkSettingsPageAction'], 10);
        }// adminMenuAction


        /**
         * Display network settings page.
         * 
         * @global \wpdb $wpdb WordPress DB class.
         */
        public function networkSettingsPageAction()
        {
            // check permission.
            if (!current_user_can('manage_network_plugins')) {
                wp_die(__('You do not have permission to access this page.'));
                exit();
            }
            if (!is_multisite()) {
                wp_die(__('You do not have permission to access this page.'));
                exit();
            }

            $output = [];

            if ($_POST) {
                // if form submitted.
                if (!wp_verify_nonce((isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : ''))) {
                    wp_nonce_ays('-1');
                }

                $resetPostOrders = filter_input(INPUT_POST, 'rd-postorder-remove-order-numbers', FILTER_SANITIZE_NUMBER_INT);
                if ($resetPostOrders == '1') {
                    global $wpdb;

                    $blog_ids = $wpdb->get_col('SELECT blog_id FROM '.$wpdb->blogs);
                    $original_blog_id = get_current_blog_id();

                    if (is_array($blog_ids)) {
                        // loop thru each sites to do activate action.
                        $PostOrder = new \RdPostOrder\App\Models\PostOrder();
                        foreach ($blog_ids as $blog_id) {
                            switch_to_blog($blog_id);
                            // reset post order.
                            $PostOrder->resetPosts();
                        }// endforeach;
                        unset($PostOrder);
                    }

                    // switch back to current site.
                    switch_to_blog($original_blog_id);
                    unset($blog_id, $blog_ids, $original_blog_id);

                    $output['form_result_class'] = 'notice-success';
                    $output['form_result_msg'] =  __('Post order has been reset successfully.', 'rd-postorder');
                }// endif reset post order.
                unset($resetPostOrders);
            }

            // get all options
            $output['options'] = get_option($this->main_option_name);

            $Loader = new \RdPostOrder\App\Libraries\Loader();
            $Loader->loadView('admin/Settings/multisiteSettings_v', $output);
            unset($Loader, $output);
        }// networkSettingsPageAction


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            add_action('network_admin_menu', [$this, 'adminMenuAction']);
        }// registerHooks


    }
}
