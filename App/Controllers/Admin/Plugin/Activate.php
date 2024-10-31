<?php


namespace RdPostOrder\App\Controllers\Admin\Plugin;

if (!class_exists('\\RdPostOrder\\App\\Controllers\\Admin\\Plugin\\Activate')) {
    /**
     * The controller that will be working on activate the plugin.
     */
    class Activate implements \RdPostOrder\App\Controllers\ControllerInterface
    {


        use \RdPostOrder\App\AppTrait;


        /**
         * Activate the plugin.
         * 
         * @global \wpdb $wpdb WordPress DB class.
         */
        public function activateAction()
        {
            global $wpdb;

            \RdPostOrder\App\Libraries\Debug::writeLog('Debug: RundizPostOrder activateAction() method was called.');

            if (is_multisite()) {
                $blog_ids = $wpdb->get_col('SELECT blog_id FROM '.$wpdb->blogs);
                $original_blog_id = get_current_blog_id();

                if (is_array($blog_ids)) {
                    // loop thru each sites to do activate action.
                    foreach ($blog_ids as $blog_id) {
                        switch_to_blog($blog_id);
                        $this->doActivateAction();
                    }
                }

                // switch back to current site.
                switch_to_blog($original_blog_id);
                unset($blog_id, $blog_ids, $original_blog_id);
            } else {
                $this->doActivateAction();
            }
        }// activateAction


        /**
         * Do the activate plugin action. 
         * 
         * - Add order number into `posts` table.<br>
         * - Add option related to this plugin (if not exists).
         * 
         * @global \wpdb $wpdb WordPress DB class.
         */
        protected function doActivateAction()
        {
            global $wpdb;

            // it is not supported manual order per category.
            // if doing that, the single post will still load previous & next post from home posts listing.
            // example code to add order number in `table_relationships` table.
            /*$results = $wpdb->get_results(
                'SELECT ' . 
                    '`' . $wpdb->term_relationships . '`.`object_id`, ' . 
                    '`' . $wpdb->term_relationships . '`.`term_taxonomy_id`, ' . 
                    '`' . $wpdb->term_taxonomy . '`.`term_taxonomy_id`, ' . 
                    '`' . $wpdb->term_taxonomy . '`.`term_id`, ' . 
                    '`' . $wpdb->term_taxonomy . '`.`taxonomy`, ' . 
                    '`' . $wpdb->posts . '`.`ID`, ' . 
                    '`' . $wpdb->posts . '`.`post_date`, ' . 
                    '`' . $wpdb->posts . '`.`post_name`, ' . 
                    '`' . $wpdb->posts . '`.`post_status`' . 
                    ' FROM `' . $wpdb->term_relationships . '`' . 
                    ' LEFT JOIN `' . $wpdb->term_taxonomy . '` ON `' . $wpdb->term_relationships . '`.`term_taxonomy_id` = `' . $wpdb->term_taxonomy . '`.`term_taxonomy_id`' . 
                    ' LEFT JOIN `' . $wpdb->posts . '` ON `' . $wpdb->term_relationships . '`.`object_id` = `' . $wpdb->posts . '`.`ID`' . 
                    ' WHERE `' . $wpdb->term_taxonomy . '`.`taxonomy` = \'category\'' . 
                    ' AND `' . $wpdb->posts . '`.`post_status` IN(\'' . implode('\', \'', $this->allowed_order_post_status) . '\')' . 
                    ' ORDER BY `' . $wpdb->posts . '`.`post_date` ASC',// get the oldest for number 1st to display at last page.
                OBJECT
            );
            if (is_array($results)) {
                $i_count = [];
                foreach ($results as $row) {
                    if (isset($i_count[$row->term_taxonomy_id])) {
                        $i_count[$row->term_taxonomy_id] ++;
                    } else {
                        $i_count[$row->term_taxonomy_id] = 1;
                    }
                    $wpdb->update(
                        $wpdb->term_relationships, 
                        ['term_order' => $i_count[$row->term_taxonomy_id]], 
                        ['object_id' => $row->object_id, 'term_taxonomy_id' => $row->term_taxonomy_id],
                        ['%d'],
                        ['%d', '%d']
                    );
                }// endforeach;
                unset($i_count, $row);
            }
            unset($results);*/
            // the example code above will not be use as explained.

            // add order number into `posts` table.
            $results = $wpdb->get_results(
                'SELECT ' . 
                    '`ID`, ' . 
                    '`post_date`, ' . 
                    '`post_name`, ' . 
                    '`post_status`, ' .
                    '`menu_order`, ' .
                    '`post_type`' . 
                    ' FROM `' . $wpdb->posts . '`' . 
                    ' WHERE `' . $wpdb->posts . '`.`post_type` = \'post\'' . 
                    ' AND `' . $wpdb->posts . '`.`post_status` IN(\'' . implode('\', \'', $this->allowed_order_post_status) . '\')' . 
                    ' ORDER BY `' . $wpdb->posts . '`.`post_date` ASC',// get the oldest for number 1st to display at last page.
                    // the scheduled for future post has the `post_date` as scheduled date.
                OBJECT
            );
            if (is_array($results)) {
                $i_count = 1;
                foreach ($results as $row) {
                    if ($row->menu_order == '0') {
                        $wpdb->update(
                            $wpdb->posts,
                            ['menu_order' => $i_count],
                            ['ID' => $row->ID],
                            ['%d'],
                            ['%d']
                        );
                    }
                    $i_count++;
                }// endforeach;
                unset($i_count, $row);
            }
            unset($results);

            // add option related to this plugin (if not exists).
            $plugin_option = get_option($this->main_option_name);
            if ($plugin_option === false) {
                // not exists, add new.
                add_option($this->main_option_name, []);
            }
            unset($plugin_option);
            // finished activate the plugin.
        }// doActivateAction


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            // register activate hook
            register_activation_hook(RDPOSTORDER_FILE, [$this, 'activateAction']);
            // on update/upgrade plugin
            add_action('upgrader_process_complete', [$this, 'updatePlugin'], 10, 2);
        }// registerHooks


        /**
         * Works on update plugin.
         * 
         * @link https://developer.wordpress.org/reference/hooks/upgrader_process_complete/ Reference.
         * @param \WP_Upgrader $upgrader
         * @param array $hook_extra
         */
        public function updatePlugin(\WP_Upgrader $upgrader, array $hook_extra)
        {
            if (is_array($hook_extra) && array_key_exists('action', $hook_extra) && array_key_exists('type', $hook_extra) && array_key_exists('plugins', $hook_extra)) {
                if ($hook_extra['action'] == 'update' && $hook_extra['type'] == 'plugin' && is_array($hook_extra['plugins']) && !empty($hook_extra['plugins'])) {
                    $this_plugin = plugin_basename(RDPOSTORDER_FILE);
                    foreach ($hook_extra['plugins'] as $key => $plugin) {
                        if ($this_plugin == $plugin) {
                            $this_plugin_updated = true;
                            break;
                        }
                    }// endforeach;
                    unset($key, $plugin, $this_plugin);

                    if (isset($this_plugin_updated) && $this_plugin_updated === true) {
                        \RdPostOrder\App\Libraries\Debug::writeLog('Debug: RundizPostOrder updatePlugin() method was called.');

                        global $wpdb;
                        // do the update plugin task.
                        // leave this for the future use, if not then this code inside next update cannot working.
                    }// endif; $this_plugin_updated
                }// endif update plugin and plugins not empty.
            }// endif; $hook_extra
        }// updatePlugin


    }
}