<?php


namespace RdPostOrder\App\Controllers\Admin\Plugin;

if (!class_exists('\\RdPostOrder\\App\\Controllers\\Admin\\Plugin\\PluginMetaAndLinks')) {
    class PluginMetaAndLinks implements \RdPostOrder\App\Controllers\ControllerInterface
    {


        /**
         * Add links to plugin actions area
         * 
         * @param array $actions Current plugin actions. (including deactivate, edit).
         * @param string $plugin_file The plugin file for checking.
         * @return array Return modified links
         */
        public function actionLinks($actions, $plugin_file)
        {
            static $plugin;

            if (!isset($plugin)) {
                $plugin = plugin_basename(RDPOSTORDER_FILE);
            }

            if ($plugin == $plugin_file) {
                $link = [];
                if (current_user_can('manage_options') && !is_network_admin()) {
                    $link['settings'] = '<a href="'.  esc_url(get_admin_url(null, 'options-general.php?page=rd-postorder-settings')).'">'.__('Settings').'</a>';
                    $actions = array_merge($link, $actions);
                }
                if (current_user_can('manage_network_plugins') && is_network_admin()) {
                    $link['networksettings'] = '<a href="'.  esc_url(network_admin_url('settings.php?page=rd-postorder-networksettings')).'">'.__('Settings').'</a>';
                    $actions = array_merge($link, $actions);
                }
                //$actions['after_actions'] = '<a href="#" onclick="return false;">'.__('After Actions', 'rd-yte').'</a>';
                unset($link);
            }

            return $actions;
        }// actionLinks


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            // add filter action links. this will be displayed in actions area of plugin page. for example: xxxActionLinksBefore | Activate | Edit | Delete | xxxActionLinksAfter
            add_filter('plugin_action_links_' . plugin_basename(RDPOSTORDER_FILE), [$this, 'actionLinks'], 10, 4);
            add_filter('network_admin_plugin_action_links_' . plugin_basename(RDPOSTORDER_FILE), [$this, 'actionLinks'], 10, 4);
            // add filter to row meta. (in plugin page below description) Version xx | By xxx | View details | xxxRowMetaxxx | xxxRowMetaxxx
            add_filter('plugin_row_meta', [$this, 'rowMeta'], 10, 2);
        }// registerHooks


        /**
         * add links to row meta that is in plugin page under plugin description.
         * 
         * @staticvar string $plugin the plugin file name.
         * @param array $links current meta links
         * @param string $file the plugin file name for checking.
         * @return array return modified links.
         */
        public function rowMeta($links, $file)
        {
            static $plugin;

            if (!isset($plugin)) {
                $plugin = plugin_basename(RDPOSTORDER_FILE);
            }

            if ($plugin === $file) {
                //$new_link[] = '<a href="" target=""></a>';
                //$links = array_merge($links, $new_link);
                //unset($new_link);
            }

            return $links;
        }// rowMeta


    }
}