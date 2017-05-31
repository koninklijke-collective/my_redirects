<?php

namespace KoninklijkeCollective\MyRedirects\Hook;

use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;

/**
 * DataHandler: Hook to update needed lookup variables
 *
 * @package KoninklijkeCollective\MyRedirects\Hook
 */
class DataHandlerHook
{

    use \KoninklijkeCollective\MyRedirects\Functions\ObjectManagerTrait;

    /**
     * Safely check for redirect links and generate query hash
     *
     * @param array $incomingFieldArray
     * @param string $table
     * @param string $id
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $reference
     */
    public function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, $reference)
    {
        if ($table === Redirect::TABLE) {
            if (isset($incomingFieldArray['root_page_domain'])) {
                $info = Redirect::getDomainInfo($incomingFieldArray['root_page_domain']);
                // Set Page ID & domain based on configured domain selection
                $incomingFieldArray['pid'] = $info['storage'];
                $incomingFieldArray['domain'] = $info['domain'];
            }
        }
    }

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
        if (!empty($type) && $table === Redirect::TABLE) {
            if (isset($row['url'])) {
                $row['url'] = ltrim($row['url'], '/');
                $row['url_hash'] = $this->getRedirectService()->generateUrlHash($row['url']);
            }
        }
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\RedirectService
     */
    protected function getRedirectService()
    {
        return $this->getObjectManager()->get(\KoninklijkeCollective\MyRedirects\Service\RedirectService::class);
    }

}
