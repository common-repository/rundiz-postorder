<?php


namespace RdPostOrder\App\Controllers\Admin\Settings;

if (!class_exists('\\RdPostOrder\\App\\Controllers\\Admin\\Settings\\Settings')) {
    /**
     * This controller will be working as settings for rundiz postorder.
     */
    class Settings implements \RdPostOrder\App\Controllers\ControllerInterface
    {


        use \RdPostOrder\App\AppTrait;


        /**
         * Admin menu.<br>
         * Add sub menus in this method.
         */
        public function adminMenuAction()
        {
            $hook = add_options_page(__('Rundiz PostOrder', 'rd-postorder'), __('Rundiz PostOrder', 'rd-postorder'), 'manage_options', 'rd-postorder-settings', [$this, 'settingsPageAction']);
            unset($hook);
        }// adminMenuAction


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            if (is_admin()) {
                add_action('admin_menu', [$this, 'adminMenuAction']);
            }
        }// registerHooks


        /**
         * Display plugin settings page.
         */
        public function settingsPageAction()
        {
            // check permission.
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have permission to access this page.'));
            }

            $output = [];

            // get all categories for check box disable per category.
            $args = [
                'taxonomy' => 'category',
                'hide_empty' => false,
                'hierarchical' => true,
                'pad_counts' => false,
            ];
            $categories = get_categories($args);
            unset($args);
            if (is_array($categories)) {
                $CategoryHelper = new \RdPostOrder\App\Libraries\CategoryHelper();
                $output_tree = $CategoryHelper->buildCategoryHierarchyArray($categories);
                $output_tree_2d = [];
                static $output_tree_2d;
                $output_tree_2d = $CategoryHelper->buildCategoryNestedFlat2DArray($output_tree);
                if (is_array($output_tree_2d)) {
                    $categories = $output_tree_2d;
                }
                unset($CategoryHelper, $output_tree, $output_tree_2d);
            }
            $output['categories'] = $categories;
            unset($categories);

            if ($_POST) {
                // if form submitted.
                if (!wp_verify_nonce((isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : ''))) {
                    wp_nonce_ays('-1');
                }

                $data = [];
                $data['disable_customorder_frontpage'] = (isset($_POST['disable_customorder_frontpage']) && $_POST['disable_customorder_frontpage'] == '1' ? '1' : null);
                $data['disable_customorder_categories'] = (isset($_POST['disable_customorder_categories']) && is_array($_POST['disable_customorder_categories']) ? $_POST['disable_customorder_categories'] : []);
                // validate selected categories.
                foreach ($data['disable_customorder_categories'] as $index => $eachCategory) {
                    if (!is_numeric($eachCategory)) {
                        unset($data['disable_customorder_categories'][$index]);
                    }
                }// endforeach;
                unset($eachCategory, $index);

                update_option($this->main_option_name, $data);

                $output['form_result_class'] = 'notice-success';
                $output['form_result_msg'] =  __('Settings saved.');
            }

            // get all options
            $output['options'] = get_option($this->main_option_name);

            $Loader = new \RdPostOrder\App\Libraries\Loader();
            $Loader->loadView('admin/Settings/settings_v', $output);
            unset($Loader, $output);
        }// settingsPageAction


    }
}