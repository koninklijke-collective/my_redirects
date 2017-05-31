<?php
defined('TYPO3_MODE') or die ('Access denied.');

call_user_func(function ($extension) {

    if (TYPO3_MODE === 'BE') {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'KoninklijkeCollective.' . $extension,
            'web',
            $extension,
            '',
            [
                // Allowed controller action combinations
                'Redirect' => 'list, delete, activate, lookup',
            ],
            [
                // Additional configuration
                'access' => 'user, group',
                'icon' => 'EXT:' . $extension . '/Resources/Public/Icons/my_redirects_module.png',
                'labels' => 'LLL:EXT:' . $extension . '/Resources/Private/Language/locallang_mod.xlf',
                // Disable
                'navigationComponentId' => ''
            ]
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
            \KoninklijkeCollective\MyRedirects\Domain\Model\Redirect::TABLE,
            'EXT:' . $extension . '/Resources/Private/Language/locallang_csh.xlf'
        );
    }

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(\KoninklijkeCollective\MyRedirects\Domain\Model\Redirect::TABLE);
}, \KoninklijkeCollective\MyRedirects\Utility\ConfigurationUtility::EXTENSION);
