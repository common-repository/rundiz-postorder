<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace RdPostOrder\App\Controllers\Admin\Posts;


if (!class_exists('\\RdPostOrder\\App\\Controllers\\Admin\\Posts\\AbstractReOrderPosts')) {
    abstract class AbstractReOrderPosts implements \RdPostOrder\App\Controllers\ControllerInterface
    {


        use \RdPostOrder\App\AppTrait;


        /**
         * @var string Admin menu slug.
         */
        const MENU_SLUG = 'rd-postorder_reorder-posts';


    }
}
