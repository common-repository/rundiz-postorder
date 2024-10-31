<?php


namespace RdPostOrder\App\Controllers\Admin\Posts;

if (!class_exists('\\RdPostOrder\\App\\Controllers\\Admin\\Posts\\ReOrderPosts')) {
    /**
     * This controller will be working as re-order the posts page.
     */
    class ReOrderPosts extends AbstractReOrderPosts
    {


        /**
         * @var string The hook name (also known as the hook suffix) used to determine the screen.
         */
        protected $hookName;


        /**
         * Admin help tab.
         */
        public function adminHelpTab()
        {
            $screen = get_current_screen();

            $screen->add_help_tab([
                'id' => 'rd-postorder_reorder-posts-helptab1',
                'title' => __('Re-order by dragging', 'rd-postorder'),
                'content' => $this->Loader->getLoadView('admin/Posts/ReOrderPosts/adminHelpTab_01'),
            ]);
            $screen->add_help_tab([
                'id' => 'rd-postorder_reorder-posts-helptab2',
                'title' => __('Re-order over next/previous pages', 'rd-postorder'),
                'content' => $this->Loader->getLoadView('admin/Posts/ReOrderPosts/adminHelpTab_02'),
            ]);
            $screen->add_help_tab([
                'id' => 'rd-postorder_reorder-posts-helptab3',
                'title' => __('Manually change order number', 'rd-postorder'),
                'content' => $this->Loader->getLoadView('admin/Posts/ReOrderPosts/adminHelpTab_03'),
            ]);
            $screen->add_help_tab([
                'id' => 'rd-postorder_reorder-posts-helptab4',
                'title' => __('Re-number and reset', 'rd-postorder'),
                'content' => $this->Loader->getLoadView('admin/Posts/ReOrderPosts/adminHelpTab_04'),
            ]);

            $sidebar_html = $screen->get_help_sidebar();
            $sidebar_content = '<i class="fa fa-info-circle fa-fw"></i> ' . __('Please note that sticky post can be re-order here but the results will remain on top on the front untill it is unstick.', 'rd-postorder');
            $screen->set_help_sidebar($sidebar_html . $sidebar_content);
            unset($sidebar_content, $sidebar_html);
        }// adminHelpTab


        /**
         * Admin menu.<br>
         * Add sub menus in this method.
         */
        public function adminMenuAction()
        {
            $hook = add_posts_page(__('Re-order posts', 'rd-postorder'), __('Re-order posts', 'rd-postorder'), 'edit_others_posts', static::MENU_SLUG, [$this, 'listPostsAction']);
            $this->hookName = $hook;
            // redirect to nice URL if there are un-necessary query string in it.
            add_action('load-' . $hook, [$this, 'redirectNiceUrl']);
            // register css & js
            add_action('load-' . $hook, [$this, 'registerScripts']);
            // add help tab
            add_action('load-' . $hook, [$this, 'adminHelpTab']);

            unset($hook);
        }// adminMenuAction


        /**
         * List the posts for re-order.
         */
        public function listPostsAction()
        {
            // check permission
            if (!current_user_can('edit_others_posts')) {
                wp_die(__('You do not have permission to access this page.'));
            }

            $output = [];

            // list the posts
            $PostsListTable = new \RdPostOrder\App\Models\PostsListTable();
            $PostsListTable->prepare_items();
            $output['PostsListTable'] = $PostsListTable;
            unset($PostsListTable);

            nocache_headers();

            // load views for displaying
            $Loader = new \RdPostOrder\App\Libraries\Loader();
            $Loader->loadView('admin/Posts/reOrderPosts_listPostsAction_v', $output);
            unset($Loader, $output);
        }// listPostsAction


        /**
         * Redirect to nice URL with query string.<br>
         * This method will be filter out un-necessary query string and redirect to the new one.
         */
        public function redirectNiceUrl()
        {
            if (isset($_GET['page']) && $_GET['page'] == static::MENU_SLUG) {
                // redirect to show nice URL
                $not_showing_queries = ['_wpnonce', '_wp_http_referer', 'menu_order'];
                if (is_array($_REQUEST)) {
                    foreach ($_REQUEST as $name => $value) {
                        if (in_array($name, $not_showing_queries)) {
                            $needs_redirect = true;
                            break;
                        }
                    }// endforeach;
                    unset($name, $value);

                    if (isset($needs_redirect) && $needs_redirect === true) {
                        $new_url = admin_url('edit.php') . '?';
                        $new_query = [];
                        foreach ($_REQUEST as $name => $value) {
                            if (!in_array($name, $not_showing_queries)) {
                                $new_query[$name] = $value;
                            }
                        }// endforeach;
                        unset($name, $value);
                        $new_url .= http_build_query($new_query);
                        unset($new_query);
                        wp_redirect($new_url);
                    }
                }
                unset($needs_redirect, $not_showing_queries);
            }
        }// redirectNiceUrl


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
         * Enqueue scripts and styles here.
         */
        public function registerScripts()
        {
            $pluginData = get_plugin_data(RDPOSTORDER_FILE);
            $pluginVersion = (isset($pluginData['Version']) ? $pluginData['Version'] : false);
            unset($pluginData);

            // to name font awesome handle as `plugin-name-prefix-font-awesome4` is to prevent conflict with other plugins that maybe use older version but same handle that cause some newer icons in this plugin disappears.
            wp_enqueue_style('rd-postorder-font-awesome4', plugin_dir_url(RDPOSTORDER_FILE) . 'assets/css/font-awesome.min.css', [], '4.7.0');
            wp_enqueue_style('rd-postorder-ReOrderPosts-css', plugin_dir_url(RDPOSTORDER_FILE) . 'assets/css/ReOrderPosts.css', [], $pluginVersion);

            wp_enqueue_script('rd-postorder-ReOrderPosts-js', plugin_dir_url(RDPOSTORDER_FILE) . 'assets/js/ReOrderPosts.js', ['jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'jquery-touch-punch', 'jquery-query'], $pluginVersion, true);
            wp_localize_script(
                'rd-postorder-ReOrderPosts-js',
                'RdPostOrderObj',
                [
                    'ajaxnonce' => wp_create_nonce('rdPostOrderReOrderPostsAjaxNonce'),
                    'ajaxnonce_error_message' => __('Please reload this page and try again.', 'rd-postorder'),
                    'debug' => (defined('WP_DEBUG') && WP_DEBUG === true ? 'true' : 'false'),
                    'hookName' => $this->hookName,
                    'txtConfirm' => __('Are you sure?', 'rd-postorder'),
                    'txtConfirmReorderAll' => __('Are you sure to doing this? (This may slow down your server if you have too many posts.)', 'rd-postorder'),
                    'txtDismissNotice' => __('Dismiss this notice.'),
                    'txtPreviousXhrWorking' => __('The previous XHR is currently working, please wait few seconds and try again.', 'rd-postorder'),
                ]
            );
        }// registerScripts


    }
}