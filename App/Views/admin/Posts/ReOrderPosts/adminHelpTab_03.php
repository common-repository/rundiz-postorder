<p>
    <?php _e('The manually change order number is very useful if you have many posts and you want to re-order the number manually.', 'rd-postorder'); ?>
    <br>
    <?php _e('You can just enter the number you want. The lowest number (example: 1) will be display on the last while the highest number will be display first.', 'rd-postorder'); ?>
    <br>
    <?php 
    printf(
        /* translators: %1$s The word save all changes on order numbers on select action; %2$s Apply text on button. */
        __('Once you okay with those numbers, please select %1$s from bulk actions and click %2$s.', 'rd-postorder'),
        '<strong>' . __('Save all changes on order numbers', 'rd-postorder') . '</strong>',
        '<strong>' . __('Apply') . '</strong>'
    );
    ?>
</p>