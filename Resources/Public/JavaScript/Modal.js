/**
 * Module: TYPO3/CMS/MyRedirects/Modal
 * API for modal windows powered by Twitter Bootstrap.
 * @exports TYPO3/CMS/MyRedirects/Modal
 */
define(
    [
        'jquery',
        'TYPO3/CMS/Backend/Severity',
        'TYPO3/CMS/Backend/Modal',
        'bootstrap'
    ],
    function ($, Severity, Modal) {
        'use strict';

        $(document).on('click', '.my-redirects-info-trigger', function (evt) {
            evt.preventDefault();
            var $element = $(this);
            var url = $element.data('url') || null;
            var content = $element.data('content') || 'Are you sure?';
            var severity = typeof Severity[$element.data('severity')] !== 'undefined' ? Severity[$element.data('severity')] : Severity.info;
            if (url !== null) {
                var separator = (url.indexOf('?') > -1) ? '&' : '?';
                var params = $.param({data: $element.data()});
                url = url + separator + params;
            }
            Modal.advanced({
                type: url !== null ? Modal.types.ajax : Modal.types.default,
                title: $element.data('title') || 'Alert',
                content: url !== null ? url : content,
                severity: severity,
                buttons: [
                    {
                        text: $element.data('button-close-text') || 'Close',
                        active: true,
                        btnClass: 'btn-default',
                        trigger: function () {
                            Modal.currentModal.trigger('modal-dismiss');
                        }
                    }
                ]
            });
        });
    }
);
