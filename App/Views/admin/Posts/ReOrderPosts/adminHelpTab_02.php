<p>
    <?php 
    printf(
        /* translators: %1$s Move up command; %2$s Move down command. */
        __('To re-order a post over next or previous pages, move your cursor on the row you want to re-order and click on %1$s or %2$s.', 'rd-postorder'),
        '<strong>' . __('Move up', 'rd-postorder') . '</strong>',
        '<strong>' . __('Move down', 'rd-postorder') . '</strong>'
    );
    ?>
    <br>
    <?php _e('The post that is on top of the list will be move up to previous page, the post that is on bottom of the list will be move down to next page.', 'rd-postorder'); ?>
</p>