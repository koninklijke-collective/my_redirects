<?php

defined('TYPO3_MODE') or die('Access denied.');

call_user_func(function ($extension) {
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        'tcarecords-' . \KoninklijkeCollective\MyRedirects\Domain\Model\Redirect::TABLE . '-default',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:' . $extension . '/Resources/Public/Icons/tcarecords-tx_myredirects_domain_model_redirect-default.svg']
    );
    $iconRegistry->registerIcon(
        'module-my_redirects',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:' . $extension . '/Resources/Public/Icons/module-my_redirects.svg']
    );

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = \KoninklijkeCollective\MyRedirects\Command\ActiveLookupCommandController::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][$extension] = \KoninklijkeCollective\MyRedirects\Hook\DataHandlerHook::class;

    // Actual frontend hook for redirect invoke
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/index_ts.php']['preprocessRequest'][$extension] = \KoninklijkeCollective\MyRedirects\Hook\RedirectActionHook::class . '->redirectAction';

    // Install tool migrations
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][$extension . '_fix_redirects'] = \KoninklijkeCollective\MyRedirects\Install\Updates\FixRedirects::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][$extension . '_realurl_redirects_import'] = \KoninklijkeCollective\MyRedirects\Install\Updates\RealUrlRedirectsImport::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][$extension . '_domain_tree_redirects'] = \KoninklijkeCollective\MyRedirects\Install\Updates\DomainTreeRedirects::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][$extension . '_deprecate_extension'] = \KoninklijkeCollective\MyRedirects\Install\Updates\MigrateDeprecatedRedirects::class;
}, \KoninklijkeCollective\MyRedirects\Utility\ConfigurationUtility::EXTENSION);
