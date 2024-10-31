<?php


namespace RdPostOrder\App\Controllers\Admin\Posts;

if (!class_exists('\\RdPostOrder\\App\\Controlers\\Admin\\Posts\\HookNewPost')) {
    class HookNewPost implements \RdPostOrder\App\Controllers\ControllerInterface
    {


        use \RdPostOrder\App\AppTrait;


        /**
         * Admin users saving the post.
         * 
         * @link https://codex.wordpress.org/Plugin_API/Action_Reference/wp_insert_post Reference.
         * @param int $post_id
         * @param object $post
         * @param bool $update
         */
        public function hookInsertPostAction($post_id, $post, $update)
        {
            if (
                is_numeric($post_id)
                && is_object($post) 
                && isset($post->post_status) && in_array($post->post_status, $this->allowed_order_post_status) 
                && isset($post->menu_order) && $post->menu_order == '0' 
                && isset($post->post_type) && $post->post_type == 'post' 
            ) {
                // if this save is first time, whatever it status is.
                $PostOrder = new \RdPostOrder\App\Models\PostOrder();
                $result = $PostOrder->setNewPostOrderNumber($post_id);
                unset($PostOrder);

                if (is_array($result)) {
                    $menu_order = $result['menu_order'];
                    $updated = $result['updated'];
                    $updatedScheduled = $result['updatedScheduled'];
                }
                unset($result);

                \RdPostOrder\App\Libraries\Debug::writeLog(
                    'Debug: RundizPostOrder hookInsertPostAction() method was called. Admin is saving new post. The new `menu_order` value is ' . $menu_order . 
                        ' and the post `ID` is ' . $post_id . '.' .
                        ' updated: ' . var_export($updated, true) . '; updated scheduled posts: ' . var_export($updatedScheduled, true) . '.'
                );
                unset($menu_order, $updated, $updatedScheduled);
            }
        }// hookInsertPostAction


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            add_action('wp_insert_post', [$this, 'hookInsertPostAction'], 10, 3);
        }// registerHooks


    }
}