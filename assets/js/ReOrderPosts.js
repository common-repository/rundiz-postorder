/**
 * Rundiz PostOrder JS.
 */


class RdPostOrderReOrder {


    /**
     * Class constructor.
     */
    constructor() {
        // mark as XHR is working (bool).
        this.xhrIsWorking = false;
    }// constructor


    /**
     * After ajax task: replace table contents.
     * 
     * @private This function was called from `ajaxReNumberAll()`, `ajaxReOrder()`, `ajaxResetAllPostsOrder()`, `ajaxSaveAllNumbersChanged()`, `ajaxUpdateSortItems()`.
     * @param {object} response The object must contain .save_result and .list_table_updated properties.
     * @returns {undefined}
     */
    _ajaxReplaceTable(response) {
        let $ = jQuery.noConflict();

        if (response) {
            if (typeof(response.save_result) !== 'undefined' && response.save_result === true) {
                if (typeof(response.list_table_updated) !== 'undefined') {
                    let list_table_html = $(response.list_table_updated).filter('.post-reorder-table')[0].outerHTML;
                    $('.post-reorder-table').replaceWith(list_table_html);
                    this.reActiveTableToggleRow();
                }
            }
        }
    }// _ajaxReplaceTable


    /**
     * Make notice popup auto hide.
     * 
     * @private This method was called from `displayNoticeElement()`.
     * @returns {undefined}
     */
    _autoHideNoticePopup() {
        let noticeElement = document.querySelector('.rd-postorder-notice-popup');
        let timeout = 7000;

        if (noticeElement) {
            if (RdPostOrderObj.debug === 'true') {
                console.log('Notice popup will be remove in ' + timeout + ' milli seconds.');
            }

            setTimeout(() => {
                noticeElement.remove();
            }, timeout);
        }
    }// _autoHideNoticePopup


    /**
     * AJAX re-number all posts.
     * 
     * @private This function was called from `listenFormSubmit()`.
     * @returns false
     */
    ajaxReNumberAll() {
        let $ = jQuery.noConflict();
        let thisClass = this;

        if (this.xhrIsWorking === true) {
            alert(RdPostOrderObj.txtPreviousXhrWorking);
            return false;
        }

        let confirmed_val = confirm(RdPostOrderObj.txtConfirmReorderAll);

        if (confirmed_val === true) {
            thisClass.xhrIsWorking = true;
            thisClass.disablePostSortable();
            $('.form-result-placeholder').html('');

            let formData = {
                'action': 'RdPostOrderReNumberAll',
                'security': RdPostOrderObj.ajaxnonce,
                '_wp_http_referer': $('input[name="_wp_http_referer"]').val(),
                'paged': ($.query.get('paged') ? $.query.get('paged') : 1),
                'hookName': RdPostOrderObj.hookName,
            }

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                dataType: 'json'
            })
            .done((response, textStatus, jqXHR) => {
                // displaying result to the page.
                thisClass.displayNoticeElement(response, response, 'notice-error');

                if (typeof(response) !== 'undefined') {
                    thisClass._ajaxReplaceTable(response);
                }
            })
            .fail((jqXHR, textStatus, errorThrown) => {
                let errResponse = jqXHR.responseJSON;
                let errResponseText = jqXHR.responseText;

                thisClass.displayNoticeElement(errResponse, errResponseText, 'notice-error');
            })
            .always((jqXHR, textStatus, errorThrown) => {
                let response;
                if (textStatus === 'success') {
                    response = jqXHR;
                } else {
                    response = jqXHR.responseJSON;
                }
                // mark XHR is not working.
                thisClass.xhrIsWorking = false;
                // re-activate sortable
                thisClass.enablePostSortable();
            });
        }// endif; confirmed

        return false;
    }// ajaxReNumberAll


    /**
     * Re-order by move up or down.
     * 
     * This function was called from html event attribute.
     * 
     * @param {string} move_to
     * @param {int|number} postID
     * @returns false
     */
    static ajaxReOrder(move_to, postID) {
        let $ = jQuery.noConflict();
        let thisClass = new this;

        if (thisClass.xhrIsWorking === true) {
            alert(RdPostOrderObj.txtPreviousXhrWorking);
            return false;
        }

        if (typeof(move_to) === 'undefined') {
            move_to = 'up';
        }
        // the menu_order will be get it directly from list table. that is the most up to date (updated on sorted).

        thisClass.xhrIsWorking = true;
        thisClass.disablePostSortable();
        $('.form-result-placeholder').html('');

        let formData = {
            'action': 'RdPostOrderReOrderPost',
            'security': RdPostOrderObj.ajaxnonce,
            '_wp_http_referer': $('input[name="_wp_http_referer"]').val(),
            'move_to': move_to,
            'postID': postID,
            'menu_order': $('#menu_order_'+postID).val(),
            'paged': ($.query.get('paged') ? $.query.get('paged') : 1),
            'hookName': RdPostOrderObj.hookName,
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            dataType: 'json'
        })
        .done((response, textStatus, jqXHR) => {
            // displaying result to the page.
            thisClass.displayNoticeElement(response, response, 'notice-error');

            if (typeof(response) !== 'undefined') {
                thisClass._ajaxReplaceTable(response);
            }
        })
        .fail((jqXHR, textStatus, errorThrown) => {
            let errResponse = jqXHR.responseJSON;
            let errResponseText = jqXHR.responseText;

            thisClass.displayNoticeElement(errResponse, errResponseText, 'notice-error');
        })
        .always((jqXHR, textStatus, errorThrown) => {
            let response;
            if (textStatus === 'success') {
                response = jqXHR;
            } else {
                response = jqXHR.responseJSON;
            }
            // mark XHR is not working.
            thisClass.xhrIsWorking = false;
            // re-activate sortable
            thisClass.enablePostSortable();
        });

        return false;
    }// ajaxReOrder


    /**
     * Reset all post order by use DB order by `post_date` ascending.
     * 
     * @private This function was called from `listenFormSubmit()`.
     * @returns false
     */
    ajaxResetAllPostsOrder() {
        let $ = jQuery.noConflict();
        let thisClass = this;

        if (this.xhrIsWorking === true) {
            alert(RdPostOrderObj.txtPreviousXhrWorking);
            return false;
        }

        let confirmed_val = confirm(RdPostOrderObj.txtConfirmReorderAll);

        if (confirmed_val === true) {
            thisClass.xhrIsWorking = true;
            thisClass.disablePostSortable();
            $('.form-result-placeholder').html('');

            let formData = {
                'action': 'RdPostOrderResetAllPostsOrder',
                'security': RdPostOrderObj.ajaxnonce,
                '_wp_http_referer': $('input[name="_wp_http_referer"]').val(),
                'paged': ($.query.get('paged') ? $.query.get('paged') : 1),
                'hookName': RdPostOrderObj.hookName,
            }

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                dataType: 'json'
            })
            .done((response, textStatus, jqXHR) => {
                // displaying result to the page.
                thisClass.displayNoticeElement(response, response, 'notice-error');

                if (typeof(response) !== 'undefined') {
                    thisClass._ajaxReplaceTable(response);
                }
            })
            .fail((jqXHR, textStatus, errorThrown) => {
                let errResponse = jqXHR.responseJSON;
                let errResponseText = jqXHR.responseText;

                thisClass.displayNoticeElement(errResponse, errResponseText, 'notice-error');
            })
            .always((jqXHR, textStatus, errorThrown) => {
                let response;
                if (textStatus === 'success') {
                    response = jqXHR;
                } else {
                    response = jqXHR.responseJSON;
                }
                // mark XHR is not working.
                thisClass.xhrIsWorking = false;
                // re-activate sortable
                thisClass.enablePostSortable();
            });
        }// endif; confirmed

        return false;
    }// ajaxResetAllPostsOrder


    /**
     * Save all numbers input that was made change.
     * 
     * @private This function was called from `listenFormSubmit()`.
     * @returns false
     */
    ajaxSaveAllNumbersChanged() {
        let $ = jQuery.noConflict();
        let thisClass = this;

        if (this.xhrIsWorking === true) {
            alert(RdPostOrderObj.txtPreviousXhrWorking);
            return false;
        }

        let confirmed_val = confirm(RdPostOrderObj.txtConfirm);

        if (confirmed_val === true) {
            thisClass.xhrIsWorking = true;
            thisClass.disablePostSortable();
            $('.form-result-placeholder').html('');

            let formData = $('.menu_order_value').serialize();
            let additionalFormData = {
                'action': 'RdPostOrderSaveAllNumbersChanged',
                'security': RdPostOrderObj.ajaxnonce,
                '_wp_http_referer': $('input[name="_wp_http_referer"]').val(),
                'paged': ($.query.get('paged') ? $.query.get('paged') : 1),
                'hookName': RdPostOrderObj.hookName,
            }
            formData += '&' + $.param(additionalFormData);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                dataType: 'json'
            })
            .done((response, textStatus, jqXHR) => {
                // displaying result to the page.
                thisClass.displayNoticeElement(response, response, 'notice-error');

                if (typeof(response) !== 'undefined') {
                    thisClass._ajaxReplaceTable(response);
                }
            })
            .fail((jqXHR, textStatus, errorThrown) => {
                let errResponse = jqXHR.responseJSON;
                let errResponseText = jqXHR.responseText;

                thisClass.displayNoticeElement(errResponse, errResponseText, 'notice-error');
            })
            .always((jqXHR, textStatus, errorThrown) => {
                let response;
                if (textStatus === 'success') {
                    response = jqXHR;
                } else {
                    response = jqXHR.responseJSON;
                }
                // mark XHR is not working.
                thisClass.xhrIsWorking = false;
                // re-activate sortable
                thisClass.enablePostSortable();
            });
        }// endif; confirmed

        return false;
    }// ajaxSaveAllNumbersChanged


    /**
     * Re-order post by drag & drop.
     * 
     * @private This function was called from `enablePostSortable()`.
     * @param {string} sorted_items_serialize_values
     * @param {int} max_menu_order
     * @returns {undefined}
     */
    ajaxUpdateSortItems(sorted_items_serialize_values, max_menu_order) {
        let $ = jQuery.noConflict();
        let thisClass = this;

        if (this.xhrIsWorking === true) {
            alert(RdPostOrderObj.txtPreviousXhrWorking);
            return false;
        }

        this.xhrIsWorking = true;
        this.disablePostSortable();
        $('.form-result-placeholder').html('');

        let formData = sorted_items_serialize_values + '&' + $('.menu_order_value').serialize();
        let additionalFormData = {
            'action': 'RdPostOrderReOrderPosts',
            'security': RdPostOrderObj.ajaxnonce,
            '_wp_http_referer': $('input[name="_wp_http_referer"]').val(),
            'max_menu_order': max_menu_order,
            'hookName': RdPostOrderObj.hookName,
        }
        formData += '&' + $.param(additionalFormData);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            dataType: 'json'
        })
        .done((response, textStatus, jqXHR) => {
            // displaying result to the page.
            thisClass.displayNoticeElement(response, response, 'notice-error');

            if (typeof(response) !== 'undefined') {
                if (typeof(response.save_result) !== 'undefined' && response.save_result === true) {
                    if (typeof(response.re_ordered_data) !== 'undefined') {
                        // loop get data from saved and set into html spans and inputs.
                        $.each(response.re_ordered_data, function() {
                            $('#menu_order_'+this.ID).val(this.menu_order);
                        });
                    }
                }
            }
        })
        .fail((jqXHR, textStatus, errorThrown) => {
            let errResponse = jqXHR.responseJSON;
            let errResponseText = jqXHR.responseText;

            thisClass.displayNoticeElement(errResponse, errResponseText, 'notice-error');
        })
        .always((jqXHR, textStatus, errorThrown) => {
            let response;
            if (textStatus === 'success') {
                response = jqXHR;
            } else {
                response = jqXHR.responseJSON;
            }
            // mark XHR is not working.
            thisClass.xhrIsWorking = false;
            // re-activate sortable
            thisClass.enablePostSortable();
        });
    }// ajaxUpdateSortItems


    /**
     * Disable sortable ability on data table.
     * 
     * @private This function was called from `ajaxReNumberAll()`, `ajaxReOrder()`, `ajaxResetAllPostsOrder()`, `ajaxSaveAllNumbersChanged()`, `ajaxUpdateSortItems()`.
     * @returns {Boolean}
     */
    disablePostSortable() {
        let $ = jQuery.noConflict();

        if (RdPostOrderObj.debug === 'true') {
            console.log('Disable table sortable.');
        }

        if (!$('.post-reorder-table tbody').hasClass('ui-sortable')) {
            if (RdPostOrderObj.debug === 'true') {
                console.log('  This list table is currently not activate for sortable. exit function.');
            }
            return false;
        }

        $('.post-reorder-table tbody').sortable('destroy');
    }// disablePostSortable


    /**
     * Get notice element based on response and display it on the page.
     * 
     * @private This function was called from `ajaxReNumberAll()`, `ajaxReOrder()`, `ajaxResetAllPostsOrder()`, `ajaxSaveAllNumbersChanged()`, `ajaxUpdateSortItems()`.
     * @param {object} responseJSON
     * @param {string} responseText
     * @param {string} default_notice_class
     * @returns {undefined}
     */
    displayNoticeElement(responseJSON, responseText, default_notice_class) {
        let $ = jQuery.noConflict();

        if (
            typeof(default_notice_class) === 'undefined' || 
            (typeof(default_notice_class) !== 'undefined' && (default_notice_class === '' || default_notice_class === null))
        ) {
            default_notice_class = 'notice-error';
        }

        let form_result_html;
        if (typeof(responseJSON) !== 'undefined' && typeof(responseJSON) === 'object' && typeof(responseJSON.form_result_class) !== 'undefined' && typeof(responseJSON.form_result_msg) !== 'undefined') {
            let form_result_class;
            if (typeof(responseJSON.form_result_class) === 'undefined') {
                form_result_class = default_notice_class;
            } else {
                form_result_class = responseJSON.form_result_class;
            }
            form_result_html = this.getNoticeElement(form_result_class, responseJSON.form_result_msg);
            $('.form-result-placeholder').html(form_result_html);
        } else if (typeof(responseText) !== 'undefined' && typeof(responseText) === 'string') {
            if (responseText === '-1') {
                form_result_html = this.getNoticeElement(default_notice_class, RdPostOrderObj.ajaxnonce_error_message);
                $('.form-result-placeholder').html(form_result_html);
            } else if (responseText !== '' && responseText !== null) {
                form_result_html = this.getNoticeElement(default_notice_class, responseText);
                $('.form-result-placeholder').html(form_result_html);
            }
        }

        // make notice popup auto hide.
        this._autoHideNoticePopup();
        // re-activate alert dismissable.
        this.reActiveDismissable();
    }// displayNoticeElement


    /**
     * Enable posts sortable.
     * 
     * @returns {Boolean}
     */
    enablePostSortable() {
        let $ = jQuery.noConflict();
        let thisClass = this;

        if (RdPostOrderObj.debug === 'true') {
            console.log('Enable table sortable.');
        }

        if ($('.post-reorder-table tbody').hasClass('ui-sortable')) {
            if (RdPostOrderObj.debug === 'true') {
                console.log('The list table sortable is already activate. exit function.');
            }
            return true;
        }

        $('.post-reorder-table tbody').sortable({
            handle: '.reorder-handle',
            placeholder: 'ui-placeholder',
            revert: true,
            start: function(event, ui) {
                // fixed height for table row.
                ui.placeholder.height(ui.item.height());
                // colspan the table cells for placeholder. this is for nice rendering in mobile or small screen.
                ui.placeholder.html('<td class="check-column"></td><td class="column-primary" colspan="6"></td>');
            },
            update: function(event, ui) {
                // on stopped sorting and position has changed.
                // get sorted items serialize values.
                let sorted_items_serialize_values = $('.post-reorder-table tbody').sortable('serialize');
                // get max value of menu_order
                let max_menu_order = -Infinity;
                $('.menu_order_value').each(function () {
                    max_menu_order = Math.max(max_menu_order, parseFloat(this.value));
                });

                thisClass.ajaxUpdateSortItems(sorted_items_serialize_values, max_menu_order);
            }
        });
    }// enablePostSortable


    /**
     * Get notice HTML element.
     * 
     * @private This function was called from `displayNoticeElement()`.
     * @param {string} notice_class
     * @param {stirng} notice_message
     * @returns {String}
     */
    getNoticeElement(notice_class, notice_message) {
        return '<div class="'+notice_class+' notice rd-postorder-notice-popup is-dismissible">'
            +'<p><strong>'+notice_message+'</strong></p>'
            +'<button type="button" class="notice-dismiss"><span class="screen-reader-text">' + RdPostOrderObj.txtDismissNotice + '</span></button>'
            +'</div>';
    }// getNoticeElement


    /**
     * Listen on button action clicked and modify form method.
     * 
     * @returns {undefined}
     */
    listenButtonActionClick() {
        let $ = jQuery.noConflict();

        // use event delegation.
        $('body').on('click', '.button.action', function(event) {
            if (RdPostOrderObj.debug === 'true') {
                console.log('Button action were clicked');
            }

            let action_selector;
            let action_selector_top = $('#bulk-action-selector-top').val();
            let action_selector_bottom = $('#bulk-action-selector-bottom').val();

            if (action_selector_top != '-1') {
                action_selector = action_selector_top;
            } else if (action_selector_bottom != '-1') {
                action_selector = action_selector_bottom;
            }

            if (typeof(action_selector) === 'undefined' || action_selector == '-1') {
                // if not found any action select box or user select nothing.
                $('#re-order-posts-form').attr('method', 'get');
            } else {
                // if user selected somehting.
                // change form method to post.
                $('#re-order-posts-form').attr('method', 'post');
            }
        });
    }// listenButtonActionClick


    /**
     * Listen enter key press on current page input.<br>
     * This will be reset all select box action to nothing because it is going to next/previous page, not submit action.
     * 
     * @returns {undefined}
     */
    listenEnterKeyPressOnPageNumberInput() {
        let $ = jQuery.noConflict();

        // use event delegation.
        $('body').on('keyup keypress', '#current-page-selector', function(e) {
            if (e.key === 'Enter' || e.code === 'Enter' || e.keyCode === 13 || e.which === 13) {
                if (RdPostOrderObj.debug === 'true') {
                    console.log('The current page input has entered key press. Reset all action select boxes to nothing because this is going to next page, not submit action.');
                }
                $('#bulk-action-selector-top').val('-1');
                $('#bulk-action-selector-bottom').val('-1');
                // mark form to method get.
                $('#re-order-posts-form').attr('method', 'get');
            }
        });
    }// listenEnterKeyPressOnPageNumberInput


    /**
     * Listen escape key press to cancel sortable.
     * 
     * @returns {undefined}
     */
    listenEscKeyPress() {
        let $ = jQuery.noConflict();
        let thisClass = this;

        // use event delegation.
        $(document).on('keyup', function(e) {
            if (e.key === 'Escape') {
                // esc key press
                if (RdPostOrderObj.debug === 'true') {
                    console.log('Cancelling sortable.');
                }

                // cancel sortable.
                $('.post-reorder-table tbody')
                    .find('.post-item-row')
                    .css({
                        'display': '',
                        'height': '',
                        'left': '',
                        'position': '',
                        'right': '',
                        'top': '',
                        'width': '',
                        'z-index': ''
                    });
                $('.post-reorder-table tbody')
                    .find('.ui-placeholder')
                    .remove();
                $('.post-reorder-table tbody')
                    .find('.ui-sortable-helper')
                    .removeClass('ui-sortable-helper');
                if ($('.post-reorder-table tbody').hasClass('ui-sortable')) {
                    $('.post-reorder-table tbody')
                        .sortable('destroy')
                        .trigger('mouseup');
                }
                thisClass.enablePostSortable();
                // unable to cancel with `.sortable('cancel')`. the item will be removed. see more at https://bugs.jqueryui.com/ticket/15076#ticket
            }
        });
    }// listenEscKeyPress


    /**
     * Listen on form submit and get action select box value at least one from top or bottom.
     * 
     * @returns {undefined}
     */
    listenFormSubmit() {
        let $ = jQuery.noConflict();
        let thisClass = this;

        // use event delegation.
        $('body').on('submit', '#re-order-posts-form', function(event) {
            if (RdPostOrderObj.debug === 'true') {
                console.log('The form submitted');
            }

            let action_selector;
            let action_selector_top = $('#bulk-action-selector-top').val();
            let action_selector_bottom = $('#bulk-action-selector-bottom').val();

            if (action_selector_top != '-1') {
                action_selector = action_selector_top;
            } else if (action_selector_bottom != '-1') {
                action_selector = action_selector_bottom;
            }

            if (typeof(action_selector) !== 'undefined') {
                event.preventDefault();
                if (RdPostOrderObj.debug === 'true') {
                    console.log('  Prevented default event.');
                    console.log('  Action selected: '+action_selector);
                }
                if (action_selector == 'renumber_all') {
                    return thisClass.ajaxReNumberAll();
                } else if (action_selector == 'reset_all') {
                    return thisClass.ajaxResetAllPostsOrder();
                } else if (action_selector == 'save_all_numbers_changed') {
                    return thisClass.ajaxSaveAllNumbersChanged();
                }
            }
        });
    }// listenFormSubmit


    /**
     * Re-active dismissable on notice element.
     * 
     * @private This function was called from `displayNoticeElement()`.
     * @returns {undefined}
     */
    reActiveDismissable() {
        jQuery('.notice.is-dismissible').on('click', '.notice-dismiss', function(event){
            jQuery(this).closest('.notice').remove();
        });
    }// reActiveDismissable


    /**
     * Re-active table toggle row.
     * 
     * @private This function was called from `ajaxReNumberAll()`, `ajaxReOrder()`, `ajaxResetAllPostsOrder()`, `ajaxSaveAllNumbersChanged()`.
     * @returns {undefined}
     */
    reActiveTableToggleRow() {
        let $ = jQuery.noConflict();

        // copy from wp-admin/js/common.js
        $('tbody').on('click', '.toggle-row', function() {
            $(this).closest('tr').toggleClass('is-expanded');
        });
    }// reActiveTableToggleRow


}// RdPostOrderReOrder


var rdpostorder_is_updating = false;


jQuery(function($) {
    let rdPostOrderClass = new RdPostOrderReOrder();

    // post sortable
    rdPostOrderClass.enablePostSortable();

    rdPostOrderClass.listenEscKeyPress();

    rdPostOrderClass.listenEnterKeyPressOnPageNumberInput();
    rdPostOrderClass.listenButtonActionClick();
    rdPostOrderClass.listenFormSubmit();
});