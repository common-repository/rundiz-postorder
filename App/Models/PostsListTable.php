<?php


namespace RdPostOrder\App\Models;

if (!class_exists('\\RdPostOrder\\App\\Models\\PostsListTable')) {
    /**
     * List data into table.
     * Warning! Do not modify method name because they are extended from WP_List_Table class of WordPress. Changing the method name may cause program error.
     * Warning! this parent class is marked as private. Please read at wordpress source.
     * 
     * This class copy from wp-admin/includes/class-wp-posts-list-table.php
     * 
     * @link http://wpengineer.com/2426/wp_list_table-a-step-by-step-guide/ tutorial about how to list table data.
     * @link http://www.sitepoint.com/using-wp_list_table-to-create-wordpress-admin-tables/ another tutorial
     * @link https://codex.wordpress.org/Class_Reference/WP_List_Table wordpress list table class source.
     */
    class PostsListTable extends WPListTable
    {


        use \RdPostOrder\App\AppTrait;


        /**
         * Class constructor.
         * 
         * @param array $args
         */
        public function __construct($args = [])
        {
            parent::__construct($args);

            $this->screen->post_type = 'post';
        }// __construct


        /**
         * Column checkbox.
         * 
         * @param object $item
         * @return string
         */
        public function column_cb($item)
        {
            return '<i class="reorder-handle fa fa-sort fa-fw" title="' . __('Drag to re-order', 'rd-orderpost') . '"></i>';
        }// column_cb


        /**
         * Get column date result.
         * 
         * @param object $item
         * @return string
         */
        public function column_date($item)
        {
            global $mode;
            $output = '';

            if (isset($item->post_date)) {
                if ($item->post_date == '0000-00-00 00:00:00') {
                    $t_time = __('Unpublished');
                    $h_time = $t_time;
                    $time_diff = 0;
                } else {
                    $t_time = get_the_time(__('Y/m/d g:i:s a'), $item);
                    $m_time = $item->post_date;

                    $time = get_post_time('G', true, $item);
                    $time_diff = time() - $time;

                    if ($time_diff > 0 && $time_diff < DAY_IN_SECONDS) {
                        $h_time = sprintf(__( '%s ago' ), human_time_diff($time));
                    } else {
                        $h_time = mysql2date(__('Y/m/d'), $m_time);
                    }
                }
            }

            if (isset($item->post_status)) {
                if ($item->post_status == 'publish') {
                    $output .= __('Published');
                } elseif ($item->post_status == 'future') {
                    if ($time_diff > 0) {
                        $output .= '<strong class="error-message">' . __('Missed schedule') . '</strong>';
                    } else {
                        $output .= __('Scheduled');
                    }
                } else {
                    $output .= __('Last Modified');
                }
                $output .= '<br>';
                $output .= '<abbr title="' . $t_time . '">' . apply_filters('post_date_column_time', $h_time, $item, 'date', 'list') . '</abbr>';
            }

            unset($h_time, $m_time, $t_time, $time, $time_diff);
            return $output;
        }// column_date


        /**
         * Get column data.
         * 
         * @param object $item
         * @param string $column_name
         * @return string
         */
        public function column_default($item, $column_name)
        {
            switch ($column_name) {
                case 'author':
                    $user = get_userdata($item->post_author);
                    $displayName = (isset($user->display_name) ? $user->display_name : $item->post_author);
                    unset($user);
                    return '<a href="' . admin_url('user-edit.php?user_id=' . $item->post_author) . '">' . $displayName . '</a>';
                case 'categories':
                    $taxonomy = 'category';
                    return $this->columnTaxonomyLink($taxonomy, $item);
                case 'tags':
                    $taxonomy = 'post_tag';
                    return $this->columnTaxonomyLink($taxonomy, $item);
                case 'order':
                    return '<input id="menu_order_' . $item->ID . '" class="menu_order_value" type="number" name="menu_order[' . $item->ID . ']" value="' . $item->menu_order . '" step="1">';
                default:
                    if (isset($item->$column_name)) {
                        return $item->$column_name;
                    }
            }
        }// column_default


        /**
         * Handles the title column output.
         * 
         * @param object $item
         * @return string
         */
        public function column_title($item)
        {
            $output = '<strong>'
                    . '<a class="row-title" href="' . admin_url('post.php?post=' . $item->ID . '&amp;action=edit') . '" title="' . esc_attr(stripslashes($item->post_title)) . '">'
                        . esc_html(mb_strimwidth(stripslashes($item->post_title), 0, 70, '...'))
                    . '</a>'
                    . $this->columnTitleDisplayPostStatus($item)
                    . '</strong>';
            $output .= '<div class="row-actions">'
                    . '<a href="#move-up,' . $item->ID . '" onclick="return RdPostOrderReOrder.ajaxReOrder(\'up\', \'' . $item->ID . '\');"><i class="fa fa-sort-asc fa-fw"></i> ' . __('Move up', 'rd-postorder') . '</a>'
                    . ' | <a href="#move-down,' . $item->ID . '" onclick="return RdPostOrderReOrder.ajaxReOrder(\'down\', \'' . $item->ID . '\');"><i class="fa fa-sort-desc fa-fw"></i> ' . __('Move down', 'rd-postorder') . '</a>'
                    . ' | <a href="' . admin_url('post.php?post=' . $item->ID . '&amp;action=edit') . '">' . __('Edit') . '</a>'
                    . ' | <a href="' . home_url('?p=' . $item->ID) . '">' . __('View') . '</a>'
                    ;
            $output .= '</div>';
            return $output;
        }// column_title


        /**
         * Generate a link for taxonomy such as categories, tags.
         * 
         * @param string $taxonomy
         * @param object $item
         * @return string
         */
        private function columnTaxonomyLink($taxonomy, $item)
        {
            $terms = get_the_terms($item->ID, $taxonomy);
            $output = '&mdash;';

            if (is_array($terms)) {
                $outlink = [];
                foreach ($terms as $term) {
                    $label = esc_html(sanitize_term_field('name', $term->name, $term->term_id, $taxonomy, 'display'));
                    $outlink[] = '<a href="' . admin_url('term.php?taxonomy=' . $taxonomy . '&amp;tag_ID=' . $term->term_id . '&amp;post_type=post') . '">' . $label . '</a>';
                }// endforeach;
                unset($term);

                $output = join(__(', '), $outlink);
                unset($outlink);
            }

            unset($taxonomy, $terms);
            return $output;
        }// columnTaxonomyLink


        /**
         * Get post status to display append after the title.
         * 
         * @param object $item
         * @return string
         */
        private function columnTitleDisplayPostStatus($item)
        {
            $post_states = [];
            if (isset($item->post_password) && !empty($item->post_password)) {
                $post_states['protected'] = __('Password protected');
            }
            if (isset($item->post_status)) {
                if ($item->post_status == 'future') {
                    $post_states['future'] = __('Scheduled');
                }
                if ($item->post_status == 'draft') {
                    $post_states['draft'] = __('Draft');
                }
                if ($item->post_status == 'pending') {
                    $post_states['pending'] = __('Pending');
                }
                if ($item->post_status == 'private') {
                    $post_states['private'] = __('Private');
                }
                if ($item->post_status == 'trash') {
                    $post_states['trash'] = __('Trash');
                }
                if (is_sticky($item->ID)) {
                    $post_states['sticky'] = __('Sticky');
                }
            }

            if (!empty($post_states)) {
                $i = 0;
                $total_states = count($post_states);
                $output = ' &mdash; ';
                foreach ($post_states as $state) {
                    $output .= '<span class="post-state">' . $state . '</span>';
                    $i++;
                    if ($i < $total_states) {
                        $output .= __(', ');
                    }
                }// endforeach;
                unset($i, $post_states, $state, $total_states);
                return $output;
            }
        }// columnTitleDisplayPostStatus


        /**
         * Display buttons at bulk actions position.
         * 
         * @param strnig $which
         */
        /*protected function bulk_actions($which = '')
        {
            echo '<a class="button re-number-all" onclick="return ajaxReNumberAll();" title="' . __('Click on this button to re-number all the posts in current listing order.', 'rd-postorder') . '"><i class="fa fa-sort-numeric-desc"></i> ' . __('Re-number all posts', 'rd-postorder') . '</a>';
            echo ' <a class="button reset-all-posts" onclick="return ajaxResetAllPostsOrder();" title="' . __('Click on this button to reset all the posts order by date.', 'rd-postorder') . '"><i class="fa fa-refresh"></i> ' . __('Reset all order', 'rd-postorder') . '</a>';
        }// bulk_actions*/


        /**
         * Get bulk actions.
         * 
         * @return array
         */
        protected function get_bulk_actions()
        {
            return [
                'renumber_all' => __('Re-number all posts', 'rd-postorder'), 
                'reset_all' => __('Reset all order', 'rd-postorder'),
                'save_all_numbers_changed' => __('Save all changes on order numbers', 'rd-postorder'),
            ];
        }// get_bulk_actions


        /**
         * get columns to display.
         * 
         * @return array
         */
        public function get_columns()
        {
            $columns = [
                'cb' => '',
                'title' => __('Title'),
                'author' => __('Author'),
                'categories' => __('Categories'),
                'tags' => __('Tags'),
                'order' => __('Order', 'rd-postorder'),
                'date' => __( 'Date' ),
            ];
            return $columns;
        }// get_columns


        /**
         * Get table classes.
         * 
         * @return array
         */
        protected function get_table_classes()
        {
            return array('widefat', 'fixed', 'striped', 'post-reorder-table');
        }// get_table_classes


        /**
         * prepare data and items
         * 
         * @global \wpdb $wpdb
         * @global \WP_Query $wp_query
         * @param integer $user_id
         */
        public function prepare_items()
        {
            // prepare columns
            $columns = $this->get_columns();
            $hidden = [];
            $this->_column_headers = [$columns, $hidden];

            global $wpdb, $wp_query;
            $wpdb->show_errors();

            wp_edit_posts_query([
                'post_type' => $this->screen->post_type,
                'orderby' => 'menu_order',
                'order' => 'DESC',
                'show_sticky' => '',
            ]);

            $post_type = $this->screen->post_type;
            $per_page = $this->get_items_per_page('edit_' . $post_type . '_per_page');
            $per_page = apply_filters('edit_posts_per_page', $per_page, $post_type);

            $total_items = $wp_query->found_posts;
            if (isset($wp_query->posts) && is_array($wp_query->posts)) {
                $results = $wp_query->posts;
            } else {
                $results = [];
            }

            // list posts for previous version.
            /*$current_page = $this->get_pagenum();

            // connect db and list data.
            // get *total* items
            $sql = 'SELECT COUNT(`ID`) FROM `' . $wpdb->posts . '`';
            $sql .= ' WHERE `post_type` = \'post\'';
            $sql .= ' AND `post_status` IN(\'' . implode('\', \'', $this->allowed_order_post_status) . '\')';
            $total_items = $wpdb->get_var($sql);
            unset($sql);

            // get items to list data.
            $sql = 'SELECT `' . $wpdb->posts . '`.*, `' . $wpdb->users . '`.`ID` AS `user_ID`, `' . $wpdb->users . '`.`user_login`, `' . $wpdb->users . '`.`user_nicename`, `' . $wpdb->users . '`.`user_email`, `' . $wpdb->users . '`.`display_name` FROM `' . $wpdb->posts . '`';
            $sql .= ' LEFT JOIN `' . $wpdb->users . '` ON `' . $wpdb->posts . '`.`post_author` = `' . $wpdb->users . '`.`ID`';
            $sql .= ' WHERE `post_type` = \'post\'';
            $sql .= ' AND `post_status` IN(\'' . implode('\', \'', $this->allowed_order_post_status) . '\')';
            $sql .= ' ORDER BY `menu_order` DESC';
            $sql .= ' LIMIT ' . (($current_page - 1) * $per_page) . ', ' . $per_page;
            $results = $wpdb->get_results($sql, OBJECT_K);
            unset($sql);*/
            // end list posts for previous version.

            // create pagination
            $this->set_pagination_args([
                'total_items' => $total_items, 
                'per_page'    => $per_page
            ]);

            $this->items = $results;
        }// prepare_items


        /**
         * Display a table row.
         * 
         * @param object $item
         */
        public function single_row($item)
        {
            echo '<tr id="postID-' . $item->ID . '" class="post-' . $item->ID . ' menu_order-' . $item->menu_order . ' post-item-row">';
            $this->single_row_columns($item);
            echo '</tr>';
        }// single_row


    }
}