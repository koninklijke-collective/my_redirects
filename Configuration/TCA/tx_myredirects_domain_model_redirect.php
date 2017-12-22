<?php
use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;

if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$translation = 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE;

return [
    'ctrl' => [
        'title' => $translation . '.singular',
        'groupName' => $translation . '.plural',
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
        'security' => [
            'ignoreWebMountRestriction' => true,
            'ignoreRootLevelRestriction' => true,
        ],
        'searchFields' => 'url, destination, backend_note'
    ],
    'interface' => [
        'showRecordFieldList' => 'url_hash, url, destination, last_referrer, counter, http_response, domain_limit, active, last_hit, last_checked, inactive_reason'
    ],
    'types' => [
        0 => [
            'showitem' => '--palette--;' . $translation . '.palette.from;from, --palette--;' . $translation . '.palette.to;to, --palette--;' . $translation . '.palette.information;information,'
                . '--div--;' . $translation . '.div.health,'
                . 'url_hash, --palette--;' . $translation . '.palette.visited;visited, --palette--;' . $translation . '.palette.response;response'
        ]
    ],
    'palettes' => [

        'from' => [
            'showitem' => 'url, domain',
            'canNotCollapse' => true
        ],
        'to' => [
            'showitem' => 'destination, http_response',
            'canNotCollapse' => true
        ],
        'information' => [
            'showitem' => 'crdate, --linebreak--, backend_note',
            'canNotCollapse' => true
        ],
        'visited' => [
            'showitem' => 'counter, last_hit, last_referrer',
            'canNotCollapse' => true
        ],
        'response' => [
            'showitem' => 'last_checked, active, --linebreak--, inactive_reason',
            'canNotCollapse' => true
        ],
    ],
    'columns' => [
        'pid' => [
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'tstamp' => [
            'config' => [
                'type' => 'passthrough',
            ]
        ],
        'crdate' => [
            'exclude' => 0,
            'label' => $translation . '.crdate',
            'config' => [
                'readOnly' => true,
                'type' => 'input',
                'size' => 10,
                'eval' => 'datetime'
            ]
        ],
        'url_hash' => [
            'exclude' => 0,
            'label' => $translation . '.url_hash',
            'config' => [
                'readOnly' => true,
                'type' => 'input',
                'size' => 30,
            ]
        ],
        'url' => [
            'exclude' => 0,
            'label' => $translation . '.url',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'max' => 65535,
            ]
        ],
        'destination' => [
            'exclude' => 0,
            'label' => $translation . '.destination',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'max' => 65535,
                'wizards' => [
                    'link' => [
                        'type' => 'popup',
                        'title' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:header_link_formlabel',
                        'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_link.gif',
                        'module' => array(
                            'name' => 'wizard_link',
                        ),
                        'JSopenParams' => 'height=800,width=600,status=0,menubar=0,scrollbars=1',
                        'params' => [
                            'blindLinkOptions' => 'mail, folder, spec, url',
                            'blindLinkFields' => 'target, title, class, params',
                        ],
                    ]
                ],
            ]
        ],
        'last_hit' => [
            'exclude' => 0,
            'label' => $translation . '.last_hit',
            'config' => [
                'readOnly' => true,
                'type' => 'input',
                'size' => 10,
                'eval' => 'datetime'
            ]
        ],
        'last_referrer' => [
            'exclude' => 0,
            'label' => $translation . '.last_referrer',
            'config' => [
                'readOnly' => true,
                'type' => 'input',
                'size' => 30,
            ]
        ],
        'counter' => [
            'exclude' => 0,
            'label' => $translation . '.counter',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'eval' => 'int',
                'range' => [
                    'lower' => 0,
                ],
            ]
        ],
        'http_response' => [
            'exclude' => 0,
            'label' => $translation . '.http_response',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'size' => 1,
                'items' => [
                    [
                        $translation . '.http_response.I.0',
                        0
                    ],
                    [
                        $translation . '.http_response.I.301',
                        301
                    ],
                    [
                        $translation . '.http_response.I.302',
                        302
                    ],
                    [
                        $translation . '.http_response.I.303',
                        303
                    ],
                    [
                        $translation . '.http_response.I.307',
                        307
                    ],
                ],
            ]
        ],
        'domain' => [
            'exclude' => 0,
            'label' => $translation . '.domain',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'size' => 1,
                'items' => [
                    [
                        $translation . '.domain.I.0',
                        0
                    ],
                ],
                'foreign_table' => 'sys_domain',
                'foreign_table_where' => ' AND sys_domain.redirectTo = ""',
                'default' => 0,
            ]
        ],
        'active' => [
            'exclude' => 0,
            'label' => $translation . '.active',
            'config' => [
                'readOnly' => true,
                'type' => 'check',
                'items' => [
                    [
                        $translation . '.active.I.0',
                        ''
                    ],
                ]
            ]
        ],
        'last_checked' => [
            'exclude' => 0,
            'label' => $translation . '.last_checked',
            'config' => [
                'readOnly' => true,
                'type' => 'input',
                'size' => 10,
                'eval' => 'date'
            ]
        ],
        'inactive_reason' => [
            'exclude' => 0,
            'label' => $translation . '.inactive_reason',
            'config' => [
                'readOnly' => true,
                'type' => 'text',
                'cols' => 48,
                'rows' => 10,
                'eval' => 'trim'
            ],
            'displayCond' => 'FIELD:active:REQ:false',
        ],
        'backend_note' => [
            'exclude' => 0,
            'label' => $translation . '.backend_note',
            'config' => [
                'type' => 'text',
                'cols' => 48,
                'rows' => 10,
                'eval' => 'trim'
            ],
        ],
    ],
];
