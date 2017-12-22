<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'My Redirects',
    'description' => 'Maintain your own redirects in the backend',
    'category' => 'module',
    'version' => '3.4.0',
    'state' => 'stable',
    'uploadFolder' => false,
    'clearCacheOnLoad' => true,
    'author' => 'Benjamin Serfhos',
    'author_email' => 'benjamin@serfhos.com',
    'author_company' => 'Rotterdam School of Management, Erasmus University',
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.0-8.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'KoninklijkeCollective\\MyRedirects\\' => 'Classes'
        ]
    ],
];
