<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$GLOBALS['TCA']['tx_myredirects_domain_model_redirect'] = array(
    'ctrl' => array(
        'title' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect',
        'label' => 'url',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,
        'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath(
                $_EXTKEY
            ) . 'Configuration/TCA/Redirect.php',
        'hideTable' => true, // don't show in listing..
    ),
);

if (TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Serfhos.' . $_EXTKEY,
        'web',
        'my_redirects',
        '',
        array(
            // Allowed controller action combinations
            'Redirect' => 'list, edit, new, create, delete, update, lookup',
        ),
        array(
            // Additional configuration
            'access' => 'user, group',
            'icon' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/my_redirects_module.png',
            'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_mod.xlf',
        )
    );
}