/**
 * Module: TYPO3/CMS/MyRedirects/Tooltip
 * API for tooltip windows powered by Twitter Bootstrap.
 * @exports TYPO3/CMS/MyRedirects/Tooltip
 */
define(['jquery', 'TYPO3/CMS/Backend/Tooltip'], function($, Tooltip) {
    'use strict';

    $(function() {
        Tooltip.initialize('#my-redirects-list a[title]', {
            delay: {
                show: 500,
                hide: 100
            },
            trigger: 'hover',
            container: 'body'
        });
    });

});
