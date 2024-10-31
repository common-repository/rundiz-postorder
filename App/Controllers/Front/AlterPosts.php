<?php


namespace RdPostOrder\App\Controllers\Front;

if (!class_exists('\\RdPostOrder\\App\\Controllers\\Front\\AlterPosts')) {
    /**
     * This controller will be working on front end to alter list post query.
     */
    class AlterPosts implements \RdPostOrder\App\Controllers\ControllerInterface
    {


        use \RdPostOrder\App\AppTrait;


        /**
         * Alter list post query.
         * 
         * @param \WP_Query $query
         */
        public function alterListPostAction($query)
        {
            if (is_admin()) {
                if (isset($query->query['post_type']) && $query->query['post_type'] == 'post' && !isset($_GET['orderby']) && !isset($_GET['order'])) {
                    $rd_postorder_admin_is_working = apply_filters('rd_postorder_admin_is_working', true);

                    if (isset($rd_postorder_admin_is_working) && $rd_postorder_admin_is_working === true) {
                        $query->set('orderby', 'menu_order');
                        $query->set('order', 'DESC');
                    }

                    unset($rd_postorder_admin_is_working);
                }
            } else {
                if (!$query->is_main_query()) {
                    // if not main query (such as widget recent posts).
                    return ;
                }

                $is_disable_customorder = $this->isDisableCustomOrder();

                if (isset($is_disable_customorder) && $is_disable_customorder !== true) {
                    $query->set('orderby', 'menu_order');
                    $query->set('order', 'DESC');
                }

                unset($is_disable_customorder);
            }
        }// alterListPostAction


        /**
         * Alter next post sort.<br>
         * This is working from single post page.
         * 
         * @see wp-includes/link-template.php at get_{$adjacent}_post_sort
         * @param string $order_by The `ORDER BY` clause in the SQL.
         * @param \WP_Post $post WP_Post object.
         * @return string Return the modified `order by`.
         */
        public function alterNextPostSort($order_by, $post)
        {
            $is_disable_customorder = $this->isDisableCustomOrder();

            if (isset($is_disable_customorder) && $is_disable_customorder !== true) {
                if (isset($post->post_type) && $post->post_type == 'post') {
                    $orderby = 'ORDER BY p.menu_order ASC LIMIT 1';
                }
            }

            unset($is_disable_customorder);
            return $order_by;
        }// alterNextPostSort


        /**
         * Alter next post where.<br>
         * This is working from single post page.
         * 
         * @see wp-includes/link-template.php at get_{$adjacent}_post_where
         * @param string $where The `WHERE` clause in the SQL.
         * @param boolean $in_same_term Whether post should be in a same taxonomy term.
         * @param array $excluded_terms Array of excluded term IDs.
         * @param string $taxonomy Taxonomy. Used to identify the term used when `$in_same_term` is true.
         * @param \WP_Post $post WP_Post object.
         * @return string Return the modified where from default to `menu_order` field.
         */
        public function alterNextPostWhere($where, $in_same_term, $excluded_terms, $taxonomy, $post)
        {
            $is_disable_customorder = $this->isDisableCustomOrder();

            if (isset($is_disable_customorder) && $is_disable_customorder !== true) {
                if (isset($post->post_type) && $post->post_type == 'post') {
                    $where = str_replace('p.post_date > \''.$post->post_date.'\'', 'p.menu_order > \''.$post->menu_order.'\'', $where);
                }
            }

            unset($is_disable_customorder);
            return $where;
        }// alterNextPostWhere


        /**
         * Alter previous post sort.<br>
         * This is working from single post page.
         * 
         * @see wp-includes/link-template.php at get_{$adjacent}_post_sort
         * @param string $order_by The `ORDER BY` clause in the SQL.
         * @param \WP_Post $post WP_Post object.
         * @return string Return the modified `order by`.
         */
        public function alterPreviousPostSort($order_by, $post)
        {
            $is_disable_customorder = $this->isDisableCustomOrder();

            if (isset($is_disable_customorder) && $is_disable_customorder !== true) {
                if (isset($post->post_type) && $post->post_type == 'post') {
                    $orderby = 'ORDER BY p.menu_order DESC LIMIT 1';
                }
            }

            unset($is_disable_customorder);
            return $order_by;
        }// alterPreviousPostSort


        /**
         * Alter previous post where.<br>
         * This is working from single post page.
         * 
         * @see wp-includes/link-template.php at get_{$adjacent}_post_where
         * @param string $where The `WHERE` clause in the SQL.
         * @param boolean $in_same_term Whether post should be in a same taxonomy term.
         * @param array $excluded_terms Array of excluded term IDs.
         * @param string $taxonomy Taxonomy. Used to identify the term used when `$in_same_term` is true.
         * @param \WP_Post $post WP_Post object.
         * @return string Return the modified where from default to `menu_order` field.
         */
        public function alterPreviousPostWhere($where, $in_same_term, $excluded_terms, $taxonomy, $post)
        {
            $is_disable_customorder = $this->isDisableCustomOrder();

            if (isset($is_disable_customorder) && $is_disable_customorder !== true) {
                if (isset($post->post_type) && $post->post_type == 'post') {
                    $where = str_replace('p.post_date < \''.$post->post_date.'\'', 'p.menu_order < \''.$post->menu_order.'\'', $where);
                }
            }

            unset($is_disable_customorder);
            return $where;
        }// alterPreviousPostWhere


        /**
         * Check that is there any filter hooks to disable custom order.<br>
         * Also check that is this plugin was set to disable in settings page.
         * 
         * @return boolean Return true if it was set to disable, otherwise return false.
         */
        protected function isDisableCustomOrder()
        {
            $rd_postorder_is_working = apply_filters('rd_postorder_is_working', true);

            if ($rd_postorder_is_working !== true) {
                // disable by plugin hooks (filter).
                return true;
            }

            unset($rd_postorder_is_working);

            $plugin_options = get_option($this->main_option_name);

            if (is_array($plugin_options)) {
                if (is_front_page() || is_home()) {
                    if (
                        array_key_exists('disable_customorder_frontpage', $plugin_options) && 
                        $plugin_options['disable_customorder_frontpage'] == '1'
                    ) {
                        return true;
                    }
                } elseif (is_category()) {
                    // get current category id.
                    $this_category = get_the_category();
                    if (!is_object($this_category) && is_array($this_category)) {
                        $this_category = array_shift($this_category);
                    }
                    $this_category_id = (isset($this_category->term_id) ? $this_category->term_id : 0);
                    unset($this_category);

                    if ($this_category_id === 0) {
                        // if found no category.
                        // @link https://wordpress.stackexchange.com/questions/59476/get-current-category-id-php In case that website post don't select any category then this is the last chance.
                        $this_category = get_queried_object();
                        if (isset($this_category->term_id)) {
                            $this_category_id = $this_category->term_id;
                        }
                    }
                    unset($this_category);

                    if (
                        array_key_exists('disable_customorder_categories', $plugin_options) && 
                        is_array($plugin_options['disable_customorder_categories']) &&
                        in_array($this_category_id, $plugin_options['disable_customorder_categories'])
                    ) {
                        return true;
                    }
                }
            }

            unset($plugin_options);
            return false;
        }// isDisableCustomOrder


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            add_action('pre_get_posts', [$this, 'alterListPostAction'], 20);

            add_filter('get_previous_post_where', [$this, 'alterPreviousPostWhere'], 10, 5);
            add_filter('get_previous_post_sort', [$this, 'alterPreviousPostSort'], 10, 2);
            add_filter('get_next_post_where', [$this, 'alterNextPostWhere'], 10, 5);
            add_filter('get_next_post_sort', [$this, 'alterNextPostSort'], 10, 2);
        }// registerHooks


    }
}