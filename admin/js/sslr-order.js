/* global sslrOrder, jQuery */
(function ($) {
    'use strict';

    $(function () {
        var $list   = $('#sslr-sortable');
        var $btn    = $('#sslr-save-order');
        var $notice = $('#sslr-order-notice');

        if ( ! $list.length ) return;

        $list.sortable({
            handle: '.dashicons-menu',
            placeholder: 'sslr-sortable-placeholder',
            update: function () {
                $btn.prop('disabled', false);
                $notice.text('');
            }
        });

        $btn.on('click', function () {
            var order = {};
            $list.find('li').each(function (index) {
                order[index] = $(this).data('post-id');
            });

            $btn.prop('disabled', true).text('Saving…');

            $.post(sslrOrder.ajaxurl, {
                action : 'sslr_update_order',
                nonce  : sslrOrder.nonce,
                order  : order
            })
            .done(function (res) {
                if (res.success) {
                    $notice.text('Order saved!').css('color', 'green');
                } else {
                    $notice.text('Error saving order. Please try again.').css('color', 'red');
                }
            })
            .fail(function () {
                $notice.text('Network error. Please try again.').css('color', 'red');
            })
            .always(function () {
                $btn.prop('disabled', false).text('Save Order');
            });
        });
    });

}(jQuery));
