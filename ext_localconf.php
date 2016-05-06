<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'KoninklijkeCollective\\MyRedirects\\Command\\ActiveLookupCommandController';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][$_EXTKEY] = 'KoninklijkeCollective\\MyRedirects\\Hook\\DataHandlerHook';

if ('FE' === TYPO3_MODE) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/index_ts.php']['preprocessRequest'][$_EXTKEY] = 'KoninklijkeCollective\\MyRedirects\\Controller\\RequestController->redirectAction';
}
