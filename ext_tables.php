<?php
defined('TYPO3_MODE') or die ('Access denied.');

call_user_func(function ($extension) {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'KoninklijkeCollective.' . $extension,
        'web',
        $extension,
        '',
        ['Redirect' => 'list, delete, activate, lookup'],
        [
            // Additional configuration
            'access' => 'user, group',
            'icon' => 'EXT:' . $extension . '/Resources/Public/Icons/module-my_redirects.svg',
            'iconIdentifier' => 'module-my_redirects',
            'labels' => 'LLL:EXT:' . $extension . '/Resources/Private/Language/locallang_mod.xlf',
            'navigationComponentId' => ''
        ]
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
        \KoninklijkeCollective\MyRedirects\Domain\Model\Redirect::TABLE,
        'EXT:' . $extension . '/Resources/Private/Language/locallang_csh.xlf'
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(\KoninklijkeCollective\MyRedirects\Domain\Model\Redirect::TABLE);
}, \KoninklijkeCollective\MyRedirects\Utility\ConfigurationUtility::EXTENSION);
