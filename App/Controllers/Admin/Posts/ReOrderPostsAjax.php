<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace RdPostOrder\App\Controllers\Admin\Posts;


if (!class_exists('\\RdPostOrder\\App\\Controllers\\Admin\\Posts\\ReOrderPostsAjax')) {
    /**
     * Ajax tasks for re-order posts.
     */
    class ReOrderPostsAjax extends AbstractReOrderPosts
    {


        /**
         * Ajax re-number all posts.
         * 
         * @global \wpdb $wpdb
         */
        public function ajaxReNumberAll()
        {
            // check permission
            if (!current_user_can('edit_others_posts')) {
                status_header(403);
                wp_die(__('You do not have permission to access this page.'));
            }

            if (strtolower($_SERVER['REQUEST_METHOD']) === 'post' && isset($_POST) && !empty($_POST)) {
                if (check_ajax_referer('rdPostOrderReOrderPostsAjaxNonce', 'security', false) === false) {
                    $output['form_result_class'] = 'notice-error';
                    $output['form_result_msg'] = __('Please reload this page and try again.', 'rd-postorder');
                    wp_send_json($output, 403);
                    wp_die();
                }

                \RdPostOrder\App\Libraries\Input::static_setPaged();
                global $wpdb;

                // get all posts order by current menu_order (even it contain wrong order number but keep most of current order).
                $sql = 'SELECT `ID`, `post_status`, `menu_order`, `post_type` FROM `' . $wpdb->posts . '`'
                    . ' WHERE `post_type` = \'post\''
                    . ' AND `post_status` IN(\'' . implode('\', \'', $this->allowed_order_post_status) . '\')'
                    . ' ORDER BY `menu_order` DESC';
                $result = $wpdb->get_results($sql, OBJECT_K);
                unset($sql);
                if (is_array($result)) {
                    $i_count = count($result);
                    foreach ($result as $row) {
                        $wpdb->update($wpdb->posts, ['menu_order' => $i_count], ['ID' => $row->ID], ['%d'], ['%d']);
                        $i_count--;
                    }
                    unset($i_count, $row);
                }
                unset($result);

                // done update menu_order numbers
                $output['form_result_class'] = 'notice-success';
                $output['form_result_msg'] = __('Update completed', 'rd-postorder');
                $output['save_result'] = true;

                // get list table for re-render and client side.
                ob_start();
                $PostsListTable = new \RdPostOrder\App\Models\PostsListTable([
                    'screen' => sanitize_text_field($this->getHookName()),
                ]);
                $PostsListTable->prepare_items();
                $PostsListTable->display();
                $output['list_table_updated'] = ob_get_contents();
                unset($PostsListTable);
                ob_end_clean();

                if (isset($output)) {
                    // response
                    nocache_headers();
                    wp_send_json($output);
                }
            }

            wp_die();// required
        }// ajaxReNumberAll


        /**
         * Ajax re-order a single post (move up or down).
         * 
         * @global \wpdb $wpdb
         */
        public function ajaxReOrderPost()
        {
            // check permission
            if (!current_user_can('edit_others_posts')) {
                status_header(403);
                wp_die(__('You do not have permission to access this page.'));
            }

            if (strtolower($_SERVER['REQUEST_METHOD']) === 'post' && isset($_POST) && !empty($_POST)) {
                if (check_ajax_referer('rdPostOrderReOrderPostsAjaxNonce', 'security', false) === false) {
                    $output['form_result_class'] = 'notice-error';
                    $output['form_result_msg'] = __('Please reload this page and try again.', 'rd-postorder');
                    wp_send_json($output, 403);
                    wp_die();
                }

                \RdPostOrder\App\Libraries\Input::static_setPaged();

                $move_to = (isset($_POST['move_to']) ? $_POST['move_to'] : null);
                $postID = (isset($_POST['postID']) ? intval($_POST['postID']) : null);
                $menu_order = (isset($_POST['menu_order']) ? intval($_POST['menu_order']) : null);
                $paged = $_GET['paged'];

                if ($menu_order <= 0) {
                    $output['form_result_class'] = 'notice-error';
                    $output['form_result_msg'] = __('Error! Unable to re-order the posts due the the currently order number is incorrect. Please click on &quot;Re-number all posts&quot; button to re-number all the posts.', 'rd-postorder');
                    wp_send_json($output, 500);
                    wp_die();
                }

                if (
                    ($move_to !== 'up' && $move_to !== 'down') ||
                    empty($postID) ||
                    empty($menu_order) ||
                    empty($paged)
                ) {
                    $output['form_result_class'] = 'notice-error';
                    $output['form_result_msg'] = __('Unable to re-order the post. The js form did not send required data to re-order. Please reload the page and try again.', 'rd-postorder');
                    wp_send_json($output, 400);
                    wp_die();
                }
                unset($paged);

                // sort and save process -------------------------------------------------------
                global $wpdb;
                $data = [];
                $output = [];

                // get menu_order of selected item to make very sure that it will be correctly order.
                $sql = 'SELECT `ID`, `menu_order` FROM `' . $wpdb->posts . '` WHERE `ID` = \'%d\'';
                $sql = $wpdb->prepare($sql, $postID);
                $Posts = $wpdb->get_row($sql);
                unset($sql);
                $menu_order = $Posts->menu_order;
                unset($Posts);

                // get value of menu_order next to this selected item.
                if ($move_to === 'up') {
                    $sql = 'SELECT `ID`, `menu_order`, `post_type`, `post_status` FROM `' . $wpdb->posts . '`'
                        . ' WHERE `menu_order` > \'%d\''
                        . ' AND `post_type` = \'post\''
                        . ' AND `post_status` IN(\'' . implode('\', \'', $this->allowed_order_post_status) . '\')'
                        . ' ORDER BY `menu_order` ASC';
                    $sql = $wpdb->prepare($sql, $menu_order);
                    $Posts = $wpdb->get_row($sql);
                    unset($sql);
                } elseif ($move_to === 'down') {
                    $sql = 'SELECT `ID`, `menu_order`, `post_type`, `post_status` FROM `' . $wpdb->posts . '`'
                        . ' WHERE `menu_order` < \'%d\''
                        . ' AND `post_type` = \'post\''
                        . ' AND `post_status` IN(\'' . implode('\', \'', $this->allowed_order_post_status) . '\')'
                        . ' ORDER BY `menu_order` DESC';
                    $sql = $wpdb->prepare($sql, $menu_order);
                    $Posts = $wpdb->get_row($sql);
                    unset($sql);
                }
                if (isset($Posts) && is_object($Posts)) {
                    $data[$postID] = [
                        'ID' => $postID,
                        'menu_order' => $Posts->menu_order,
                    ];
                    $data[$Posts->ID] = [
                        'ID' => $Posts->ID,
                        'menu_order' => $menu_order,
                    ];
                    unset($Posts);
                }
                unset($menu_order, $move_to, $postID);

                // update to db. ---------------------------------
                $rowsUpdated = 0;
                if (is_array($data)) {
                    foreach ($data as $a_post_id => $item) {
                        $updateResult = $wpdb->update(
                            $wpdb->posts, 
                            ['menu_order' => $item['menu_order']], 
                            ['ID' => $item['ID']],
                            ['%d'],
                            ['%d']
                        );
                        if (is_numeric($updateResult)) {
                            $rowsUpdated = ($rowsUpdated + $updateResult);
                        }
                    }// endforeach;
                    unset($a_post_id, $item, $updateResult);
                }

                $output['form_result_class'] = 'notice-success';
                $output['form_result_msg'] = __('Update completed', 'rd-postorder');
                $output['save_result'] = true;
                $output['rows_updated'] = $rowsUpdated;

                unset($data, $rowsUpdated);
                // end update to db. ----------------------------

                // get list table for re-render and client side.
                ob_start();
                $PostsListTable = new \RdPostOrder\App\Models\PostsListTable([
                    'screen' => sanitize_text_field($this->getHookName()),
                ]);
                $PostsListTable->prepare_items();
                $PostsListTable->display();
                $output['list_table_updated'] = ob_get_contents();
                unset($PostsListTable);
                ob_end_clean();

                if (isset($output)) {
                    // response
                    nocache_headers();
                    wp_send_json($output);
                }
                // end sort and save process --------------------------------------------------
            }

            wp_die();// required
        }// ajaxReOrderPost


        /**
         * Ajax re-order multiple posts. (sortable items)
         * 
         * @global \wpdb $wpdb
         */
        public function ajaxReOrderPosts()
        {
            // check permission
            if (!current_user_can('edit_others_posts')) {
                status_header(403);
                wp_die(__('You do not have permission to access this page.'));
            }

            if (strtolower($_SERVER['REQUEST_METHOD']) === 'post' && isset($_POST) && !empty($_POST)) {
                if (check_ajax_referer('rdPostOrderReOrderPostsAjaxNonce', 'security', false) === false) {
                    $output['form_result_class'] = 'notice-error';
                    $output['form_result_msg'] = __('Please reload this page and try again.', 'rd-postorder');
                    wp_send_json($output, 403);
                    wp_die();
                }

                $postIDs = (isset($_POST['postID']) ? $_POST['postID'] : []);
                $menu_orders = (isset($_POST['menu_order']) ? $_POST['menu_order'] : []);// menu_order[post ID] = menu order number.
                $max_menu_order = (isset($_POST['max_menu_order']) ? $_POST['max_menu_order'] : 0);

                if ($max_menu_order <= 0) {
                    // max menu_order is 0 or lower. 
                    // this maybe because admin delete some middle items (not first and last) and it is not re-arrange the order numbers until it gets 0 or minus (not sure but i think it is impossible).
                    // show error to prevent the unwanted result and let the admin/author reset number of all posts order instead.
                    $output['form_result_class'] = 'notice-error';
                    $output['form_result_msg'] = __('Error! Unable to re-order the posts due the the currently order number is incorrect. Please click on &quot;Re-number all posts&quot; button to re-number all the posts.', 'rd-postorder');
                    wp_send_json($output, 500);
                    wp_die();
                }

                if ((!is_array($postIDs) || empty($postIDs)) || (!is_array($menu_orders) || empty($menu_orders))) {
                    $output['form_result_class'] = 'notice-error';
                    $output['form_result_msg'] = __('Unable to re-order the posts. The js form did not send any data to re-order. Please reload the page and try again.', 'rd-postorder');
                    wp_send_json($output, 400);
                    wp_die();
                }

                // sort and save process -------------------------------------------------------
                // sort `$menu_orders` array values reverse.
                arsort($menu_orders);
                // prepare `variable for save`
                $data = [];
                // set current menu_order at max by default.
                $menu_order = intval($max_menu_order);
                foreach ($postIDs as $postID) {
                    // set values
                    $data[$postID] = [
                        'ID' => intval($postID),
                        'menu_order' => $menu_order,
                    ];

                    // current menu_order was set. remove it from `$menu_orders` array.
                    foreach ($menu_orders as $a_post_ID => $a_menu_order) {
                        if ($menu_order == $a_menu_order) {
                            // if current `variable for save` ($data)'s menu_order is match the menu_order in `$menu_orders` array.
                            // remove this array key from `menu_orders` array.
                            unset($menu_orders[$a_post_ID]);
                            break;
                        }
                    }// endforeach; $menu_orders
                    unset($a_menu_order, $a_post_ID);

                    // get next menu order.
                    reset($menu_orders);
                    $menu_order = intval(current($menu_orders));
                }// endforeach; $postIDs
                unset($menu_order, $postID);
                unset($max_menu_order, $postIDs, $menu_orders);

                // update to db.-------------------
                if (is_array($data) && !empty($data)) {
                    global $wpdb;
                    foreach ($data as $postID => $item) {
                        $wpdb->update(
                            $wpdb->posts, 
                            ['menu_order' => $item['menu_order']], 
                            ['ID' => $item['ID']],
                            ['%d'],
                            ['%d']
                        );
                    }// endforeach;
                    unset($item, $postID);
                }
                // end update to db. -------------
                $output['form_result_class'] = 'notice-success';
                $output['form_result_msg'] = __('Update completed', 'rd-postorder');
                $output['save_result'] = true;
                $output['re_ordered_data'] = $data;
                unset($data);

                // response
                nocache_headers();
                wp_send_json($output);
                // end sort and save process --------------------------------------------------
            }

            wp_die();// required
        }// ajaxReOrderPosts


        /**
         * Ajax reset all posts order.<br>
         * Start from the beginings.
         * 
         * @global \wpdb $wpdb
         */
        public function ajaxResetAllPostsOrder()
        {
            // check permission
            if (!current_user_can('edit_others_posts')) {
                wp_die(__('You do not have permission to access this page.'));
            }

            if (strtolower($_SERVER['REQUEST_METHOD']) === 'post' && isset($_POST) && !empty($_POST)) {
                if (check_ajax_referer('rdPostOrderReOrderPostsAjaxNonce', 'security', false) === false) {
                    $output['form_result_class'] = 'notice-error';
                    $output['form_result_msg'] = __('Please reload this page and try again.', 'rd-postorder');
                    wp_send_json($output, 403);
                    wp_die();
                }

                \RdPostOrder\App\Libraries\Input::static_setPaged();
                global $wpdb;

                // get all posts order by current menu_order (even it contain wrong order number but keep most of current order).
                $sql = 'SELECT `ID`, `post_date`, `post_status`, `menu_order`, `post_type` FROM `' . $wpdb->posts . '`'
                    . ' WHERE `post_type` = \'post\''
                    . ' AND `post_status` IN(\'' . implode('\', \'', $this->allowed_order_post_status) . '\')'
                    . ' ORDER BY `post_date` DESC';
                $result = $wpdb->get_results($sql, OBJECT_K);
                unset($sql);
                if (is_array($result)) {
                    $i_count = count($result);
                    foreach ($result as $row) {
                        $wpdb->update($wpdb->posts, ['menu_order' => $i_count], ['ID' => $row->ID], ['%d'], ['%d']);
                        $i_count--;
                    }
                    unset($i_count, $row);
                }
                unset($result);

                // done update menu_order numbers
                $output['form_result_class'] = 'notice-success';
                $output['form_result_msg'] = __('Update completed', 'rd-postorder');
                $output['save_result'] = true;

                // get list table for re-render and client side.
                ob_start();
                $PostsListTable = new \RdPostOrder\App\Models\PostsListTable([
                    'screen' => sanitize_text_field($this->getHookName()),
                ]);
                $PostsListTable->prepare_items();
                $PostsListTable->display();
                $output['list_table_updated'] = ob_get_contents();
                unset($PostsListTable);
                ob_end_clean();

                if (isset($output)) {
                    // response
                    nocache_headers();
                    wp_send_json($output);
                }
            }

            wp_die();// required
        }// ajaxResetAllPostsOrder


        /**
         * Ajax save all numbers that were manually changed.
         * 
         * @global \wpdb $wpdb
         */
        public function ajaxSaveAllNumbersChanged()
        {
            // check permission
            if (!current_user_can('edit_others_posts')) {
                wp_die(__('You do not have permission to access this page.'));
            }

            if (strtolower($_SERVER['REQUEST_METHOD']) === 'post' && isset($_POST) && !empty($_POST)) {
                if (check_ajax_referer('rdPostOrderReOrderPostsAjaxNonce', 'security', false) === false) {
                    $output['form_result_class'] = 'notice-error';
                    $output['form_result_msg'] = __('Please reload this page and try again.', 'rd-postorder');
                    wp_send_json($output, 403);
                    wp_die();
                }

                \RdPostOrder\App\Libraries\Input::static_setPaged();

                $menu_orders = (isset($_POST['menu_order']) ? $_POST['menu_order'] : []);
                global $wpdb;

                if (!is_array($menu_orders) || empty($menu_orders)) {
                    $output['form_result_class'] = 'notice-error';
                    $output['form_result_msg'] = __('Unable to re-order the posts. The js form did not send any data to re-order. Please reload the page and try again.', 'rd-postorder');
                    wp_send_json($output, 400);
                    wp_die();
                }

                foreach ($menu_orders as $a_post_id => $a_menu_order) {
                    $wpdb->update(
                        $wpdb->posts, 
                        ['menu_order' => $a_menu_order], 
                        ['ID' => $a_post_id], 
                        ['%d'], 
                        ['%d']
                    );
                }// endforeach;
                unset($a_menu_order, $a_post_id, $menu_orders);

                // done update menu_order numbers
                $output['form_result_class'] = 'notice-success';
                $output['form_result_msg'] = __('Update completed', 'rd-postorder');
                $output['save_result'] = true;

                // get list table for re-render and client side.
                ob_start();
                $PostsListTable = new \RdPostOrder\App\Models\PostsListTable([
                    'screen' => sanitize_text_field($this->getHookName()),
                ]);
                $PostsListTable->prepare_items();
                $PostsListTable->display();
                $output['list_table_updated'] = ob_get_contents();
                unset($PostsListTable);
                ob_end_clean();

                if (isset($output)) {
                    // response
                    nocache_headers();
                    wp_send_json($output);
                }
            }

            wp_die();// required
        }// ajaxSaveAllNumbersChanged


        /**
         * Get hook name.
         * 
         * @return string
         */
        protected function getHookName()
        {
            if (isset($_REQUEST['hookName'])) {
                $hookName = trim($_REQUEST['hookName']);
            } else {
                $hookName = '';
            }

            if (empty($hookName)) {
                $hookName = 'posts_page_' . static::MENU_SLUG;
            }

            return $hookName;
        }// getHookName


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            if (is_admin()) {
                add_action('wp_ajax_RdPostOrderReOrderPosts', [$this, 'ajaxReOrderPosts']);// re-order multiple posts
                add_action('wp_ajax_RdPostOrderReOrderPost', [$this, 'ajaxReOrderPost']);// re-order a single post (move up or down)
                add_action('wp_ajax_RdPostOrderReNumberAll', [$this, 'ajaxReNumberAll']);
                add_action('wp_ajax_RdPostOrderResetAllPostsOrder', [$this, 'ajaxResetAllPostsOrder']);
                add_action('wp_ajax_RdPostOrderSaveAllNumbersChanged', [$this, 'ajaxSaveAllNumbersChanged']);
            }
        }// registerHooks


    }
}
