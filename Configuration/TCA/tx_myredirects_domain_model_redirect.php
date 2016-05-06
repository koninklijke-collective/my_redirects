<?php
use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;

if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

return array(
    'ctrl' => array(
        'title' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE . '.singular',
        'groupName' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE . '.plural',
        'label' => 'url',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'editlock' => 'editlock',
        'dividers2tabs' => true,
        'iconfile' => 'EXT:my_redirects/Resources/Public/Icons/' . Redirect::TABLE . '.png',
        'rootLevel' => true,
        'canNotCollapse' => true,
        'hideTable' => true, // don't show in listing..
        'security' => array(
            'ignoreWebMountRestriction' => true,
            'ignoreRootLevelRestriction' => true,
        ),
        'searchFields' => 'url,destination'
    ),
    'interface' => array(
        'showRecordFieldList' => 'url_hash, url, destination, last_referrer, counter, http_response, domain_limit, active, last_hit, last_checked, inactive_reason'
    ),
    'types' => array(
        0 => array(
            'showitem' => '--palette--;;from, --palette--;;to,'
                . '--div--;LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE . '.div.health,'
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
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE . '.url_hash',
            'config' => array(
                'readOnly' => true,
                'type' => 'input',
                'size' => 30,
            )
        ),
        'url' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE . '.url',
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
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE . '.destination',
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
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE . '.last_hit',
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
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE . '.last_referrer',
            'config' => array(
                'readOnly' => true,
                'type' => 'input',
                'size' => 30,
            )
        ),
        'counter' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE . '.counter',
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
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE . '.http_response',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'size' => 1,
                'items' => array(
                    array(
                        'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE . '.http_response.I.0',
                        0
                    ),
                    array(
                        'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE . '.http_response.I.301',
                        301
                    ),
                    array(
                        'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE . '.http_response.I.302',
                        302
                    ),
                    array(
                        'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE . '.http_response.I.303',
                        303
                    ),
                    array(
                        'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE . '.http_response.I.307',
                        307
                    ),
                ),
            )
        ),
        'domain' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE . '.domain',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'size' => 1,
                'items' => array(
                    array(
                        'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE . '.domain.I.0',
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
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE . '.active',
            'config' => array(
                'readOnly' => true,
                'type' => 'check',
                'items' => array(
                    array(
                        'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE . '.active.I.0',
                        ''
                    ),
                )
            )
        ),
        'last_checked' => array(
            'exclude' => 0,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE . '.last_checked',
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
            'label' => 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE . '.inactive_reason',
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