<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Serfhos\\MyRedirects\\Command\\ActiveLookupCommandController';

if ('FE' === TYPO3_MODE) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/index_ts.php']['preprocessRequest'][$_EXTKEY] = 'Serfhos\\MyRedirects\\Controller\\RequestController->redirectAction';
}
