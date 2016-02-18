<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'My Redirects',
    'description' => 'Maintain your own redirects in the backend',
    'category' => 'module',
    'version' => '1.4.0',
    'state' => 'stable',
    'uploadFolder' => false,
    'clearCacheOnLoad' => true,
    'author' => 'Benjamin Serfhos',
    'author_email' => 'benjamin@serfhos.com',
    'author_company' => 'Rotterdam School of Management, Erasmus University',
    'constraints' => array(
        'depends' => array(
            'typo3' => '6.2.0-7.99.99',
            'beuser' => '6.2.0-7.99.99',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
    'autoload' => array(
        'psr-4' => array(
            'Serfhos\\MyRedirects\\' => 'Classes'
        )
    ),
);
