<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'My Redirects',
    'description' => 'Maintain your own redirects in the backend',
    'category' => 'module',
    'version' => '1.0.1',
    'state' => 'stable',
    'uploadFolder' => false,
    'createDirs' => null,
    'clearCacheOnLoad' => true,
    'author' => 'Benjamin Serfhos',
    'author_email' => 'benjamin@serfhos.com',
    'author_company' => 'Rotterdam School of Management, Erasmus University',
    'constraints' => array(
        'depends' => array(
            'typo3' => '6.0.0-6.2.99',
            'beuser' => '6.0.0-6.2.99',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);
