<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace RdPostOrder\App\Models;


if (!class_exists('\\RdPostOrder\\App\\Models\\PostOrder')) {
    class PostOrder
    {


        use \RdPostOrder\App\AppTrait;


        /**
         * Get latest menu order number.
         * 
         * @global \wpdb $wpdb WordPress DB class.
         * @return int Return latest menu order number that is exists in DB. If not found then return zero. This method will not increase the latest order.
         */
        public function getLatestMenuOrder()
        {
            global $wpdb;

            // get new menu_order number (new post is latest menu_order+1).
            $sql = 'SELECT `post_status`, `menu_order`, `post_type` FROM `' . $wpdb->posts . '`'
                . ' WHERE `post_type` = \'post\''
                . ' AND `post_status` IN(\'' . implode('\', \'', $this->allowed_order_post_status) . '\')'
                . ' ORDER BY `menu_order` DESC LIMIT 0, 1';
            $LastPost = $wpdb->get_row($sql);
            unset($sql);

            if (is_object($LastPost) && isset($LastPost->menu_order)) {
                return (int) $LastPost->menu_order;
            }
            unset($LastPost);
            return (int) 0;
        }// getLatestMenuOrder


        /**
         * Reset `menu_order` column on `posts` table to zero (its default value).
         * 
         * This will be use on uninstall or reset all posts order on multi-site admin settings.
         * 
         * @global \wpdb $wpdb WordPress DB class.
         */
        public function resetPosts()
        {
          global $wpdb;

          $results = $wpdb->get_results(
                'SELECT ' . 
                    '`ID`, ' . 
                    '`post_date`, ' . 
                    '`post_name`, ' . 
                    '`post_status`, ' . 
                    '`post_type`' . 
                    ' FROM `' . $wpdb->posts . '`' . 
                    ' WHERE `' . $wpdb->posts . '`.`post_type` = \'post\'' . 
                    ' AND `' . $wpdb->posts . '`.`post_status` IN(\'' . implode('\', \'', $this->allowed_order_post_status) . '\')' . 
                    ' ORDER BY `' . $wpdb->posts . '`.`post_date` ASC',
                OBJECT
            );

            if (is_array($results)) {
                foreach ($results as $row) {
                    $wpdb->update(
                        $wpdb->posts,
                        ['menu_order' => 0],
                        ['ID' => $row->ID],
                        ['%d'],
                        ['%d']
                    );
                }// endforeach;
                unset($row);
            }
            unset($results);
        }// resetPosts


        /**
         * Set new post order number on new post created.
         * 
         * This will also update shceduled posts to latest number that newer than the latest number updated by this method.<br>
         * See `updateScheduledPostsOrderToLatest()` method for more info.
         * 
         * @global \wpdb $wpdb WordPress DB class.
         * @param int $post_id
         * @return false|array Return `false` on failure, `array` on success.<br>
         *      The associative array keys are:<br>
         *          `menu_order` (int) post order number.<br>
         *          `updated` (int) number of rows updated.<br>
         *          `updatedScheduled` (int) number of rows updated for scheduled posts.<br>
         */
        public function setNewPostOrderNumber($post_id)
        {
            if (!is_numeric($post_id)) {
                return false;
            }

            global $wpdb;

            // get new `menu_order` number (new post is latest `menu_order`+1).
            $latestMenuOrder = $this->getLatestMenuOrder();
            $menu_order = ($latestMenuOrder + 1);
            unset($latestMenuOrder);

            $result = $wpdb->update($wpdb->posts, ['menu_order' => $menu_order], ['ID' => $post_id], ['%d'], ['%d']);

            if (false === $result) {
                return false;
            }

            $updateScheduledPosts = $this->updateScheduledPostsOrderToLatest();

            return [
                'menu_order' => $menu_order,
                'updated' => $result,
                'updatedScheduled' => $updateScheduledPosts,
            ];
        }// setNewPostOrderNumber


        /**
         * Update scheduled posts order to latest/newest (largest `menu_order` number++) that is newer than the new one updated on `setNewPostOrderNumber()` method.
         * 
         * Example: scheduled post had `menu_order` 13.<br>
         *      New post created and was set `menu_order` to 14 in `setNewPostOrderNumber()`.<br>
         *      This will be update the scheduled post `menu_order` to 15.
         * 
         * This method was called from `setNewPostOrderNumber()`.
         * 
         * @global \wpdb $wpdb WordPress DB class.
         * @return int Return number of rows updated.
         */
        protected function updateScheduledPostsOrderToLatest()
        {
            global $wpdb;

            // prepare latest post order number.
            $latestMenuOrder = $this->getLatestMenuOrder();

            $gmtDateTime = gmdate('Y-m-d H:i:s');
            $dateTime = get_date_from_gmt($gmtDateTime);

            // get scheduled posts by order ascending (for increase from latest order +1 each).
            $sql = 'SELECT `ID`, `post_date`, `post_date_gmt`, `post_status`, `menu_order`, `post_type` FROM `' . $wpdb->posts . '`'
                . ' WHERE `post_type` = \'post\''
                . ' AND `post_status` IN(\'' . implode('\', \'', $this->allowed_order_post_status) . '\')'
                . ' AND (`post_date` > \'%s\' OR `post_date_gmt` > \'%s\')'
                . ' ORDER BY `menu_order` ASC';
            $sql = $wpdb->prepare($sql, [$dateTime, $gmtDateTime]);
            unset($dateTime, $gmtDateTime);
            $Posts = $wpdb->get_results($sql);
            unset($sql);

            $updated = 0;
            if (is_array($Posts)) {
                foreach ($Posts as $row) {
                    $latestMenuOrder = ($latestMenuOrder + 1);
                    $result = $wpdb->update(
                        $wpdb->posts, 
                        ['menu_order' => $latestMenuOrder], 
                        ['ID' => $row->ID], 
                        ['%d'], 
                        ['%d']
                    );
                    if (is_numeric($result) && false !== $result) {
                        $updated = ($updated + $result);
                    }
                }// endforeach;
                unset($result, $row);
            }
            unset($latestMenuOrder, $Posts);

            return $updated;
        }// updateScheduledPostsOrderToLatest


    }
}