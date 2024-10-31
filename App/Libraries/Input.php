<?php
/**
 * Input class.
 * 
 * @since 1.0.3
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace RdPostOrder\App\Libraries;


if (!class_exists('\\RdPostOrder\\App\\Libraries\\Input')) {
    /**
     * Input class.
     * 
     * @method static Input static_setPaged() Set pagination paged number
     */
    class Input
    {


        /**
         * Call method from static;
         * 
         * @param string $name
         * @param mixed $arguments
         * @return mixed
         */
        public static function __callStatic($name, $arguments)
        {
            $thisClass = new static();
            $name = preg_replace('/^static_/iu', '', $name);
            if (method_exists($thisClass, $name)) {
                return $thisClass->{$name}($arguments);
            }
        }// __callStatic


        /**
         * Set pagination paged number on all global variables ($_GET, $_POST, $_REQUEST) to be integer or `null`.
         */
        public function setPaged()
        {
            if (isset($_POST['paged']) && is_numeric($_POST['paged'])) {
                $paged = (int) $_POST['paged'];
            } else {
                $paged = null;
            }
            $_POST['paged'] = $paged;
            $_GET['paged'] = $paged;
            $_REQUEST['paged'] = $paged;
            unset($paged);
        }// setPaged


    }
}