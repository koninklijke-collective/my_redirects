<?php
namespace Serfhos\MyRedirects\Utility;

use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Utility: Web Domains
 *
 * @package Serfhos\MyRedirects\Utility
 */
class DomainUtility
{

    /**
     * Get domain from database just once..
     * If already retrieved, just return element
     *
     * @param integer $domainId
     * @return array
     */
    static public function getDomain($domainId)
    {
        static $results;
        if (!isset($results[$domainId])) {
            $results[$domainId] = BackendUtility::getRecord('sys_domain', $domainId);
        }

        return $results[$domainId];
    }

    /**
     * Get all domains from database just once..
     * If already retrieved, just return element
     *
     * @return array
     */
    static public function getDomains()
    {
        static $results;
        if (!isset($results)) {
            $results = BackendUtility::getRecordsByField('sys_domain', 'redirectTo', '');
        }

        return $results;
    }
}