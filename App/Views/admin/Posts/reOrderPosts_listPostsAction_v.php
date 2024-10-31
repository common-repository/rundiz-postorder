<div class="wrap">
    <h1><?php _e('Re-order posts', 'rd-postorder'); ?></h1>


    <div class="form-result-placeholder"></div>
    <form id="re-order-posts-form" method="get">
        <input type="hidden" name="page" value="rd-postorder_reorder-posts">
        <?php 
        if (isset($PostsListTable) && is_object($PostsListTable) && method_exists($PostsListTable, 'display')) {
            $PostsListTable->display();
        }
        ?> 
    </form>
</div>