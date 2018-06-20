<?php

namespace KoninklijkeCollective\MyRedirects\Command;

use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;

/**
 * ExtBase Command: Refresh all redirects states
 *
 * @package KoninklijkeCollective\MyRedirects\Command
 */
class ActiveLookupCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController
{

    use \KoninklijkeCollective\MyRedirects\Functions\QueryBuilderTrait;
    use \KoninklijkeCollective\MyRedirects\Functions\ObjectManagerTrait;

    /**
     * Command: Refresh feeds
     *
     * @param boolean $skipInactive By default only crawl active redirects for possible performance problems
     * @return boolean
     */
    public function refreshCommand($skipInactive = true)
    {
        return $this->updateRedirects($skipInactive);
    }

    /**
     * Goes through all items in the cache and updates them.
     *
     * @param boolean $skipInactive
     * @return boolean
     */
    protected function updateRedirects($skipInactive)
    {
        $i = 0;
        $itemsPerLoop = 20;
        do {
            $records = $this->getRedirectRepository()->createQuery()
                ->setOffset($i)
                ->setLimit($itemsPerLoop)
                ->execute();

            foreach ($records as $redirect) {
                if ($redirect instanceof Redirect) {
                    $check = true;

                    // Only check redirects that are not already checked today
                    $lastChecked = $redirect->getLastChecked();
                    if ($lastChecked instanceof \DateTime) {
                        $yesterday = new \DateTime('yesterday');
                        $check = ($lastChecked < $yesterday);
                    }

                    if ($check) {
                        // Only do daily checks on active url's or when this is skipped
                        if ($redirect->getActive() || $skipInactive === false) {
                            $this->outputLine(date('d M y H:i:s') . ' - Updating "' . $redirect->getUrl() . '"');
                            $this->getStatusService()->activeLookup($redirect, true);
                            $this->getRedirectRepository()->update($redirect);
                        }
                    }
                }
                $i++;
            }
            $this->getPersistenceManager()->persistAll();
        } while ($records && $records->count() > 0);

        return true;
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\StatusService|object
     */
    protected function getStatusService()
    {
        return $this->getObjectManager()->get(\KoninklijkeCollective\MyRedirects\Service\StatusService::class);
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Domain\Repository\RedirectRepository|object
     */
    protected function getRedirectRepository()
    {
        return $this->getObjectManager()->get(\KoninklijkeCollective\MyRedirects\Domain\Repository\RedirectRepository::class);
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface|object
     */
    protected function getPersistenceManager()
    {
        return $this->getObjectManager()->get(\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class);
    }
}
