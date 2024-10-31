<?php
/**
 * The loader file that will be use for load things such as controllers, views, etc...
 * 
 * @author Vee W.
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace RdPostOrder\App\Libraries;

if (!class_exists('\\RdPostOrder\\App\\Libraries\\Loader')) {
    /**
     * The loader class that will be use for load controllers, views, and anything...
     */
    class Loader
    {


        /**
         * Automatic look into those controllers and register to the main App class to make it works.<br>
         * The controllers that will be register must implement RdPostOrder\App\Controllers\ControllerInterface to have registerHooks() method in it, otherwise it will be skipped.
         */
        public function autoRegisterControllers()
        {
            $this_plugin_dir = dirname(RDPOSTORDER_FILE);
            $di = new \RecursiveDirectoryIterator($this_plugin_dir . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . 'Controllers', \RecursiveDirectoryIterator::SKIP_DOTS);
            $it = new \RecursiveIteratorIterator($di);
            unset($di);

            foreach ($it as $file) {
                $this_file_classname = '\\RdPostOrder' . str_replace([$this_plugin_dir, '.php', '/'], ['', '', '\\'], $file);
                if (class_exists($this_file_classname)) {
                    $testController = new \ReflectionClass($this_file_classname);
                    if (!$testController->isAbstract()) {
                        $ControllerClass = new $this_file_classname();
                        if (property_exists($ControllerClass, 'Loader')) {
                            $ControllerClass->Loader = $this;
                        }
                        if (method_exists($ControllerClass, 'registerHooks')) {
                            $ControllerClass->registerHooks();
                        }
                        unset($ControllerClass);
                    }
                    unset($testController);
                }
                unset($this_file_classname);
            }// endforeach;

            unset($file, $it, $this_plugin_dir);
        }// autoRegisterControllers


        /**
         * Load and display (echo'ing) the views file.<br>
         * The views file it self must use echo or write out HTML content. Do not use return in the views file.
         * 
         * @param string $view_name The views file name refer from app/Views folder.
         * @param array $data Array data for send its key as variable into view.
         * @param boolean $require_once Use include or include_once? If true, use include_once.
         * @return boolean Return true if success loading, or return false if failed to load.
         */
        public function loadView($view_name, array $data = [], $require_once = false)
        {
            $view_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR;

            if ($view_name != null && file_exists($view_dir . $view_name . '.php') && is_file($view_dir . $view_name . '.php')) {
                if (is_array($data)) {
                    extract($data, EXTR_PREFIX_SAME, 'dupvar_');
                }

                if ($require_once === true) {
                    include_once $view_dir . $view_name . '.php';
                } else {
                    include $view_dir . $view_name . '.php';
                }

                unset($view_dir);
                return true;
            }

            unset($view_dir);
            return false;
        }// loadView


        /**
         * Get load view contents by return, not display it.
         * 
         * @since 1.0.3
         * @see `loadView()` method for more details.
         * @param string $view_name
         * @param array $data
         * @param string $require_once
         * @return string
         */
        public function getLoadView($view_name, array $data = [], $require_once = false)
        {
            ob_start();
            $this->loadView($view_name, $data, $require_once);
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }// getLoadView


    }
}