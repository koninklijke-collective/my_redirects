<?php
use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;

defined('TYPO3_MODE') or die('Access denied.');

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
        'canNotCollapse' => true,
        'hideTable' => true, // don't show in listing..
        'typeicon_classes' => [
            'default' => 'tcarecords-' . Redirect::TABLE . '-default',
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
            'showitem' => 'url, root_page_domain',
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
            'exclude' => false,
            'label' => $translation . '.crdate',
            'config' => [
                'readOnly' => true,
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 10,
                'eval' => 'datetime'
            ]
        ],
        'url_hash' => [
            'exclude' => false,
            'label' => $translation . '.url_hash',
            'config' => [
                'readOnly' => true,
                'type' => 'input',
                'size' => 30,
            ]
        ],
        'url' => [
            'exclude' => false,
            'label' => $translation . '.url',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim, required',
                'max' => 65535,
            ]
        ],
        'destination' => [
            'exclude' => false,
            'label' => $translation . '.destination',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputLink',
                'eval' => 'trim, required',
            ]
        ],
        'last_hit' => [
            'exclude' => false,
            'label' => $translation . '.last_hit',
            'config' => [
                'readOnly' => true,
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 10,
                'eval' => 'datetime'
            ]
        ],
        'last_referrer' => [
            'exclude' => false,
            'label' => $translation . '.last_referrer',
            'config' => [
                'readOnly' => true,
                'type' => 'input',
                'size' => 30,
            ]
        ],
        'counter' => [
            'exclude' => false,
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
            'exclude' => false,
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
            'exclude' => false,
            'label' => $translation . '.domain',

            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'root_page_domain' => [
            'exclude' => false,
            'label' => $translation . '.root_page_domain',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'eval' => 'required',
                'minitems' => 1,

                'size' => 1,
                'itemsProcFunc' => \KoninklijkeCollective\MyRedirects\Service\TableConfigurationService::class . '->addAllowedDomains'
            ],
            'displayCond' => 'USER:' . \KoninklijkeCollective\MyRedirects\Service\TableConfigurationService::class . '->hasAllowedDomains',
        ],
        'active' => [
            'exclude' => false,
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
            'exclude' => false,
            'label' => $translation . '.last_checked',
            'config' => [
                'readOnly' => true,
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 10,
                'eval' => 'date'
            ]
        ],
        'inactive_reason' => [
            'exclude' => false,
            'label' => $translation . '.inactive_reason',
            'config' => [
                'type' => 'text',
                'cols' => 48,
                'rows' => 10,
                'eval' => 'trim',
                'readOnly' => true,
            ],
            'displayCond' => 'FIELD:active:REQ:false',
        ],
        'backend_note' => [
            'exclude' => false,
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
