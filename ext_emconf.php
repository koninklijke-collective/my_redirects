<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'My Redirects',
    'description' => 'Maintain your own redirects in the backend',
    'category' => 'module',
    'version' => '3.1.0',
    'state' => 'stable',
    'uploadFolder' => false,
    'clearCacheOnLoad' => true,
    'author' => 'Benjamin Serfhos',
    'author_email' => 'benjamin@serfhos.com',
    'author_company' => 'Rotterdam School of Management, Erasmus University',
    'constraints' => array(
        'depends' => array(
            'typo3' => '7.6.0-8.99.99',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
    'autoload' => array(
        'psr-4' => array(
            'KoninklijkeCollective\\MyRedirects\\' => 'Classes'
        )
    ),
);
