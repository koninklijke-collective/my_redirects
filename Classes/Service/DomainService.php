<?php
namespace KoninklijkeCollective\MyRedirects\Service;

use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service: Web Domains
 *
 * @package KoninklijkeCollective\MyRedirects\Utility
 */
class DomainService implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * Get domain from database just once..
     * If already retrieved, just return element
     *
     * @param integer $domainId
     * @return array
     */
    public function getDomain($domainId)
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
    public function getDomains()
    {
        static $results;
        if (!isset($results)) {
            $results = BackendUtility::getRecordsByField('sys_domain', 'redirectTo', '');
        }

        return $results;
    }

    /**
     * Obtains current domain id from sys_domain
     *
     * @return integer
     */
    public function getCurrentDomainId()
    {
        $result = null;
        $connection = $GLOBALS['TYPO3_DB'];
        if ($connection instanceof DatabaseConnection) {
            $currentDomain = GeneralUtility::getIndpEnv('HTTP_HOST');

            list($id) = $connection->exec_SELECTgetSingleRow('uid',
                'sys_domain',
                'domainName = ' . $connection->fullQuoteStr(
                    $currentDomain,
                    'sys_domain'
                )
                . ' AND redirectTo = ""',
                null, null, true
            );

            $result = (!empty($id) ? (int) $id : 0);
        }

        return $result;
    }

    /**
     * Get used domain if configured
     *
     * @param \KoninklijkeCollective\MyRedirects\Domain\Model\Redirect $redirect
     * @return string
     */
    public function getDomainUrlFromRedirect(Redirect $redirect)
    {
        $return = '/';

        $domain = $redirect->getDomain();
        if ($domain > 0) {
            $domainRecord = $this->getDomain($domain);
            if (!empty($domainRecord)) {
                $return = rtrim($domainRecord['domainName'], '/') . '/';
            }
        }

        return $return;
    }

}
