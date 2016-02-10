<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$TCA['tx_myredirects_domain_model_redirect'] = array(
    'ctrl' => $TCA['tx_myredirects_domain_model_redirect']['ctrl'],
    'interface' => array(
        'showRecordFieldList' => 'url_hash, url, destination, last_referrer, counter, http_response, domain_limit, active, last_hit, last_checked, inactive_reason'
    ),
    'types' => array(
        0 => array(
            'showitem' => '--palette--;;from, --palette--;;to,'
                . '--div--;LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.div.health,'
                . 'url_hash, --palette--;;visited, active;;response'
        )
    ),
    'palettes' => array(
        'from' => array(
            'showitem' => 'url, domain'
        ),
        'to' => array(
            'showitem' => 'destination, http_response'
        ),
        'visited' => array(
            'showitem' => 'counter, last_hit, last_referrer',
        ),
        'response' => array(
            'showitem' => 'last_checked, inactive_reason',
        ),
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
                'readOnly' => true,
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
                'eval' => 'trim, unique',
                'max' => 65535,
            )
        ),
        'destination' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.destination',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'max' => 65535,
            )
        ),
        'last_hit' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.last_hit',
            'config' => array(
                'readOnly' => true,
                'type' => 'input',
                'size' => 10,
                'eval' => 'datetime'
            )
        ),
        'last_referrer' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.last_referrer',
            'config' => array(
                'readOnly' => true,
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
                'size' => 5,
                'eval' => 'int',
                'range' => array(
                    'lower' => 0,
                ),
            )
        ),
        'http_response' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.http_response',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'size' => 1,
                'items' => array(
                    array(
                        'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.http_response.I.0',
                        0
                    ),
                    array(
                        'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.http_response.I.301',
                        301
                    ),
                    array(
                        'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.http_response.I.302',
                        302
                    ),
                    array(
                        'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.http_response.I.303',
                        303
                    ),
                    array(
                        'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.http_response.I.307',
                        307
                    ),
                ),
            )
        ),
        'domain' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.domain',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'size' => 1,
                'items' => array(
                    array(
                        'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.domain.I.0',
                        0
                    ),
                ),
                'foreign_table' => 'sys_domain',
                'foreign_table_where' => ' AND sys_domain.redirectTo = ""',
            )
        ),
        'active' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.active',
            'config' => array(
                'readOnly' => true,
                'type' => 'check',
                'items' => array(
                    array(
                        'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.active.I.0',
                        ''
                    ),
                )
            )
        ),
        'last_checked' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.last_checked',
            'config' => array(
                'readOnly' => true,
                'type' => 'input',
                'size' => 10,
                'eval' => 'date'
            )
        ),
        'inactive_reason' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:tx_myredirects_domain_model_redirect.inactive_reason',
            'config' => array(
                'type' => 'none',
                'fixedRows' => true,
                'cols' => 48,
                'rows' => 10,
                'eval' => 'trim'
            ),
            'displayCond' => 'FIELD:active:REQ:false',
        ),
    ),
);