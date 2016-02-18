<?php
namespace Serfhos\MyRedirects\Hook;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * DataHandler: Hook to update needed lookup variables
 *
 * @package Serfhos\MyRedirects\Hook
 */
class DataHandlerHook
{

    /**
     * Safely check for redirect links and generate query hash
     *
     * @param string $type
     * @param string $table
     * @param string $id
     * @param array $row
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $reference
     */
    public function processDatamap_postProcessFieldArray($type, $table, $id, &$row, $reference)
    {
        if (!empty($type) && $table === 'tx_myredirects_domain_model_redirect') {
            if (isset($row['url'])) {
                $row['url'] = ltrim($row['url'], '/');
                $row['url_hash'] = $this->getRedirectService()->generateUrlHash($row['url']);
            }
        }
    }

    /**
     * @return \Serfhos\MyRedirects\Service\RedirectService
     */
    protected function getRedirectService()
    {
        return $this->getObjectManager()->get('Serfhos\\MyRedirects\\Service\\RedirectService');
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected function getObjectManager()
    {
        return GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
    }
}