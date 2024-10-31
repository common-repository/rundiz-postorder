<?php


namespace RdPostOrder\App\Controllers\Admin\Plugin;

if (!class_exists('\\RdPostOrder\\App\\Controllers\\Admin\\Plugin\\Uninstall')) {
    /**
     * The controller that will be working on uninstall (delete) the plugin.
     */
    class Uninstall implements \RdPostOrder\App\Controllers\ControllerInterface
    {


        use \RdPostOrder\App\AppTrait;


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            // register uninstall hook
            register_uninstall_hook(RDPOSTORDER_FILE, ['\\RdPostOrder\\App\\Controllers\\Admin\\Plugin\\Uninstall', 'uninstallAction']);
        }// registerHooks


        /**
         * Do the uninstallation action
         * 
         * - Reset all values to its default.<br>
         * - Remove option related to this plugin.
         */
        private function doUninstallAction()
        {
            // reset data in the table matched as in activate process.
            // see App\Controllers\Admin\Activate.php `activateAction()` method.

            // reset order number in `posts` table.
            $PostOrder = new \RdPostOrder\App\Models\PostOrder();
            $PostOrder->resetPosts();
            unset($PostOrder);

            // remove option related to this plugin.
            delete_option($this->main_option_name);
        }// doUninstallAction


        /**
         * Uninstall the plugin.<br>
         * Do the same way as activate the plugin but set the order number to 0 which is its default value.
         * 
         * @global \wpdb $wpdb
         */
        public static function uninstallAction()
        {
            global $wpdb;
            $ThisClass = new self;

            \RdPostOrder\App\Libraries\Debug::writeLog('Debug: RundizPostOrder uninstallAction() method was called.');

            if (is_multisite()) {
                $blog_ids = $wpdb->get_col('SELECT blog_id FROM '.$wpdb->blogs);
                $original_blog_id = get_current_blog_id();

                if (is_array($blog_ids)) {
                    // loop thru each sites to do uninstall action (reset data to its default value).
                    foreach ($blog_ids as $blog_id) {
                        switch_to_blog($blog_id);
                        $ThisClass->doUninstallAction();
                    }
                }

                // switch back to current site.
                switch_to_blog($original_blog_id);
                unset($blog_id, $blog_ids, $original_blog_id);
            } else {
                $ThisClass->doUninstallAction();
            }
        }// uninstallAction


    }
}