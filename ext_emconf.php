<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'My Redirects',
    'description' => 'Maintain your own redirects in the backend',
    'category' => 'module',
    'author' => 'Benjamin Serfhos',
    'author_email' => 'benjamin@serfhos.com',
    'dependencies' => '',
    'priority' => '',
    'module' => '',
    'state' => 'stable',
    'internal' => '',
    'uploadFolder' => false,
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => true,
    'lockType' => '',
    'author_company' => 'Rotterdam School of Management, Erasmus University',
    'version' => '1.0.0',
    'constraints' => array(
        'depends' => array(
            array(
                'typo3' => '6.0.0-6.2.99',
                'beuser' => '6.0.0-6.2.99',
            ),
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);