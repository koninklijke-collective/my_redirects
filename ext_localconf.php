<?php

defined('TYPO3_MODE') or die ('Access denied.');

call_user_func(function ($extension) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = \KoninklijkeCollective\MyRedirects\Command\ActiveLookupCommandController::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][$extension] = \KoninklijkeCollective\MyRedirects\Hook\DataHandlerHook::class;

    // Actual frontend hook for redirect invoke
    if ('FE' === TYPO3_MODE) {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/index_ts.php']['preprocessRequest'][$extension] = \KoninklijkeCollective\MyRedirects\Hook\RedirectActionHook::class . '->redirectAction';
    }

    // Install tool migrations
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][$extension . '_realurl_redirects_import'] = \KoninklijkeCollective\MyRedirects\Install\Updates\RealUrlRedirectsImport::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][$extension . '_domain_tree_redirects'] = \KoninklijkeCollective\MyRedirects\Install\Updates\DomainTreeRedirects::class;
}, \KoninklijkeCollective\MyRedirects\Utility\ConfigurationUtility::EXTENSION);
