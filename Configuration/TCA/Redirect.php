<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$TCA['tx_myredirects_domain_model_redirect'] = array(
    'ctrl' => $TCA['tx_myredirects_domain_model_redirect']['ctrl'],
    'interface' => array(
        'showRecordFieldList' => 'url_hash, url, destination, last_referrer, counter, http_response, domain_limit, active, last_checked, inactive_reason'
    ),
    'types' => array(
        0 => array(
            'showitem' => 'url_hash, url, destination, last_referrer, counter, http_response, domain,'
                . 'active, last_checked, inactive_reason'
        )
    ),
    'palettes' => array(
    ),
    'columns' => array(
        'pid' => array(
            'config' => array(
                'type' => 'passthrough'
            )
        ),
        'crdate' => array(
            'config' => array(
                'type' => 'passthrough',
            )
        ),
        'tstamp' => array(
            'config' => array(
                'type' => 'passthrough',
            )
        ),
        'url_hash' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.url_hash',
            'config' => array(
                'type' => 'input',
                'size' => 30,
            )
        ),
        'url' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.url',
            'config' => array(
                'type' => 'input',
                'size' => 30,
            )
        ),
        'destination' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.destination',
            'config' => array(
                'type' => 'input',
                'size' => 30,
            )
        ),
        'last_referrer' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.last_referrer',
            'config' => array(
                'type' => 'input',
                'size' => 30,
            )
        ),
        'counter' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.counter',
            'config' => array(
                'type' => 'input',
                'size' => 30,
            )
        ),
        'http_response' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.http_response',
            'config' => array(
                'type' => 'input',
                'size' => 30,
            )
        ),
        'domain' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.domain',
            'config' => array(
                'type' => 'input',
                'size' => 30,
            )
        ),
        'active' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.active',
            'config' => array(
                'type' => 'input',
                'size' => 30,
            )
        ),
        'last_checked' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.last_checked',
            'config' => array(
                'type' => 'input',
                'size' => 30,
            )
        ),
        'inactive_reason' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.inactive_reason',
            'config' => array(
                'type' => 'input',
                'size' => 30,
            )
        ),
    ),
);