<?php


namespace RdPostOrder\App;

if (!trait_exists('\\RdPostOrder\\App\\AppTrait')) {
    /**
     * Main application trait for common works.
     */
    trait AppTrait
    {


        /**
         * Loader class into the property.
         * @var \RdPostOrder\App\Libraries\Loader 
         */
        public $Loader;


        /**
         * @var array Allowed post status that can be change order.<br>
         * These post status can be convert into publish or private but *auto-draft* and *inherit* is not (trash status can also be revert to publish or private).
         * @link https://wordpress.org/support/article/post-status/ Reference
         */
        protected $allowed_order_post_status = ['publish', 'future', 'draft', 'pending', 'private', 'trash'];


        /**
         * @var string Main options name for use with add_option() or get_option().
         */
        public $main_option_name = 'rundiz_postorder_options';


    }
}