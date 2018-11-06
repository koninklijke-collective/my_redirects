<?php

namespace KoninklijkeCollective\MyRedirects\Install\Updates;

use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;
use KoninklijkeCollective\MyRedirects\Service\DomainService;
use KoninklijkeCollective\MyRedirects\Service\RootPageService;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Class RealUrlRedirectsImport
 *
 * @package KoninklijkeCollective\MyRedirects\Install\Updates
 */
class DomainTreeRedirects extends \TYPO3\CMS\Install\Updates\AbstractUpdate
{
    use \KoninklijkeCollective\MyRedirects\Functions\QueryBuilderTrait;
    use \KoninklijkeCollective\MyRedirects\Functions\ObjectManagerTrait;

    /**
     * @var string
     */
    protected $title = 'Migrate root redirects to specified domain specific redirect';

    /**
     * @var array
     */
    protected $availableRootPages;

    /**
     * @var array
     */
    protected $domains;

    /**
     * Checks if an update is needed
     *
     * @param string &$description The description for the update
     * @return bool Whether an update is needed (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description)
    {
        static $update;
        if ($update === null) {
            $queryBuilder = static::getQueryBuilderForTable(Redirect::TABLE);
            $existingRows = $queryBuilder
                ->select('*')
                ->from(Redirect::TABLE)
                ->where($queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)))
                ->count('uid')
                ->execute()->fetchColumn(0);
            $update = ($existingRows > 0);
        }
        $description = 'For moving root records to their corresponding root pages.';
        return $update;
    }

    /**
     * Performs the database migrations if requested
     *
     * @param array &$databaseQueries Queries done in this update
     * @param string &$customMessage Custom messages
     * @return boolean
     */
    public function performUpdate(array &$databaseQueries, &$customMessage)
    {
        $migrated = 0;
        $queryBuilder = static::getQueryBuilderForTable(Redirect::TABLE);

        $query = $queryBuilder
            ->select('*')
            ->from(Redirect::TABLE)
            ->where($queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)))
            ->execute();

        while ($row = $query->fetch()) {
            $updateQuery = clone $queryBuilder;
            $updateQuery->update(Redirect::TABLE)
                ->where($queryBuilder->expr()->eq('uid', (int)$row['uid']))
                ->set('tstamp', time(), false)
                ->set('active', true, false);

            // Fix destination for TYPO3 8.7
            if (MathUtility::canBeInterpretedAsInteger($row['destination'])) {
                $updateQuery->set('destination', 't3://page?uid=' . $row['destination']);
            }

            // Register and configure new root_page_domain and folder
            $info = $this->getRootPageByDomain((int)$row['domain']);
            $updateQuery->set('pid', (int)$info['uid'], false);
            $updateQuery->set('domain', (int)$row['domain'], false);
            $updateQuery->set('root_page_domain', $info['uid'] . '-' . $row['domain'], false);

            $databaseQueries[] = $updateQuery->getSQL();
            if ($updateQuery->execute()) {
                $migrated++;
            }
            unset($updateQuery);
        }
        $customMessage = '<p>A total of ' . $migrated . ' redirects are updated.</p>';
        return true;
    }

    /**
     * @param integer $domain
     * @return array
     */
    protected function getRootPageByDomain(int $domain)
    {
        $rootPages = $this->getAvailableRootPages();
        if ($domain > 0) {
            if (!isset($this->domains[$domain])) {
                $this->domains[$domain] = $this->getDomainService()->getDomain($domain);
            }

            $rootPageId = $this->domains[$domain]['pid'];
            if (isset($rootPages[$rootPageId])) {
                return $rootPages[$rootPageId];
            }
        }

        // Fallback on first available root page
        return reset($rootPages);
    }

    /**
     * @return array
     */
    protected function getAvailableRootPages()
    {
        if ($this->availableRootPages === null) {
            $this->availableRootPages = static::getObjectManager()->get(RootPageService::class)
                ->getRootPages();
        }
        return $this->availableRootPages;
    }

    /**
     * @return DomainService
     */
    protected function getDomainService()
    {
        return static::getObjectManager()->get(DomainService::class);
    }
}
