<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

if (TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'KoninklijkeCollective.' . $_EXTKEY,
        'web',
        'my_redirects',
        '',
        array(
            // Allowed controller action combinations
            'Redirect' => 'list, delete, lookup',
        ),
        array(
            // Additional configuration
            'access' => 'user, group',
            'icon' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/my_redirects_module.png',
            'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_mod.xlf',
        )
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
        \KoninklijkeCollective\MyRedirects\Domain\Model\Redirect::TABLE,
        'EXT:my_redirects/Resources/Private/Language/locallang_csh.xlf'
    );
}
