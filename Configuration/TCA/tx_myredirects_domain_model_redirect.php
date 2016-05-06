<?php
use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;

if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$translation = 'LLL:EXT:my_redirects/Resources/Private/Language/locallang_be.xlf:' . Redirect::TABLE;

return array(
    'ctrl' => array(
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
        'security' => array(
            'ignoreWebMountRestriction' => true,
            'ignoreRootLevelRestriction' => true,
        ),
        'searchFields' => 'url, destination, backend_note'
    ),
    'interface' => array(
        'showRecordFieldList' => 'url_hash, url, destination, last_referrer, counter, http_response, domain_limit, active, last_hit, last_checked, inactive_reason'
    ),
    'types' => array(
        0 => array(
            'showitem' => '--palette--;' . $translation . '.palette.from;from, --palette--;' . $translation . '.palette.to;to, --palette--;' . $translation . '.palette.information;information,'
                . '--div--;' . $translation . '.div.health,'
                . 'url_hash, --palette--;' . $translation . '.palette.visited;visited, --palette--;' . $translation . '.palette.response;response'
        )
    ),
    'palettes' => array(

        'from' => array(
            'showitem' => 'url, domain',
            'canNotCollapse' => true
        ),
        'to' => array(
            'showitem' => 'destination, http_response',
            'canNotCollapse' => true
        ),
        'information' => array(
            'showitem' => 'crdate, --linebreak--, backend_note',
            'canNotCollapse' => true
        ),
        'visited' => array(
            'showitem' => 'counter, last_hit, last_referrer',
            'canNotCollapse' => true
        ),
        'response' => array(
            'showitem' => 'last_checked, active, --linebreak--, inactive_reason',
            'canNotCollapse' => true
        ),
    ),
    'columns' => array(
        'pid' => array(
            'config' => array(
                'type' => 'passthrough'
            )
        ),
        'tstamp' => array(
            'config' => array(
                'type' => 'passthrough',
            )
        ),
        'crdate' => array(
            'exclude' => 0,
            'label' => $translation . '.crdate',
            'config' => array(
                'readOnly' => true,
                'type' => 'input',
                'size' => 10,
                'eval' => 'datetime'
            )
        ),
        'url_hash' => array(
            'exclude' => 0,
            'label' => $translation . '.url_hash',
            'config' => array(
                'readOnly' => true,
                'type' => 'input',
                'size' => 30,
            )
        ),
        'url' => array(
            'exclude' => 0,
            'label' => $translation . '.url',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim, unique',
                'max' => 65535,
            )
        ),
        'destination' => array(
            'exclude' => 0,
            'label' => $translation . '.destination',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'max' => 65535,
                'wizards' => array(
                    'link' => array(
                        'type' => 'popup',
                        'title' => 'LLL:EXT:cms/locallang_ttc.xlf:header_link_formlabel',
                        'icon' => 'link_popup.gif',
                        'module' => array(
                            'name' => 'wizard_element_browser',
                            'urlParameters' => array(
                                'mode' => 'wizard'
                            )
                        ),
                        'params' => array(
                            'blindLinkOptions' => 'mail, folder, spec',
                            'blindLinkFields' => 'target, title, class, params',
                        ),
                        'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
                    )
                ),
            )
        ),
        'last_hit' => array(
            'exclude' => 0,
            'label' => $translation . '.last_hit',
            'config' => array(
                'readOnly' => true,
                'type' => 'input',
                'size' => 10,
                'eval' => 'datetime'
            )
        ),
        'last_referrer' => array(
            'exclude' => 0,
            'label' => $translation . '.last_referrer',
            'config' => array(
                'readOnly' => true,
                'type' => 'input',
                'size' => 30,
            )
        ),
        'counter' => array(
            'exclude' => 0,
            'label' => $translation . '.counter',
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
            'label' => $translation . '.http_response',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'size' => 1,
                'items' => array(
                    array(
                        $translation . '.http_response.I.0',
                        0
                    ),
                    array(
                        $translation . '.http_response.I.301',
                        301
                    ),
                    array(
                        $translation . '.http_response.I.302',
                        302
                    ),
                    array(
                        $translation . '.http_response.I.303',
                        303
                    ),
                    array(
                        $translation . '.http_response.I.307',
                        307
                    ),
                ),
            )
        ),
        'domain' => array(
            'exclude' => 0,
            'label' => $translation . '.domain',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'size' => 1,
                'items' => array(
                    array(
                        $translation . '.domain.I.0',
                        0
                    ),
                ),
                'foreign_table' => 'sys_domain',
                'foreign_table_where' => ' AND sys_domain.redirectTo = ""',
            )
        ),
        'active' => array(
            'exclude' => 0,
            'label' => $translation . '.active',
            'config' => array(
                'readOnly' => true,
                'type' => 'check',
                'items' => array(
                    array(
                        $translation . '.active.I.0',
                        ''
                    ),
                )
            )
        ),
        'last_checked' => array(
            'exclude' => 0,
            'label' => $translation . '.last_checked',
            'config' => array(
                'readOnly' => true,
                'type' => 'input',
                'size' => 10,
                'eval' => 'date'
            )
        ),
        'inactive_reason' => array(
            'exclude' => 0,
            'label' => $translation . '.inactive_reason',
            'config' => array(
                'type' => 'none',
                'cols' => 48,
                'rows' => 10,
                'eval' => 'trim'
            ),
            'displayCond' => 'FIELD:active:REQ:false',
        ),
        'backend_note' => array(
            'exclude' => 0,
            'label' => $translation . '.backend_note',
            'config' => array(
                'type' => 'text',
                'cols' => 48,
                'rows' => 10,
                'eval' => 'trim'
            ),
        ),
    ),
);
