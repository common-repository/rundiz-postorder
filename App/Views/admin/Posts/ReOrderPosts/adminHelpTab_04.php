<p>
    <?php 
    printf(
        /* translators: %1$s re-number all posts text. */
        __('The %1$s action will be re-arrange numbers respectively on all posts in current listing order.', 'rd-postorder'),
        '<strong>' . __('Re-number all posts', 'rd-postorder') . '</strong>'
    );
    ?> 
    <br>
    <?php 
    printf(
        /* translators: %1$s reset all order  */
        __('The %1$s action will be reset the number on all posts order by date. It is the default order by WordPress core.', 'rd-postorder'),
        '<strong>' . __('Reset all order', 'rd-postorder') . '</strong>'
    );
    ?>
</p>