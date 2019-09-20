<?php

namespace KoninklijkeCollective\MyRedirects\Service;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service: Backend allowed Root Pages
 */
class RootPageService implements \TYPO3\CMS\Core\SingletonInterface
{
    use \KoninklijkeCollective\MyRedirects\Functions\QueryBuilderTrait;

    /**
     * @var array
     */
    protected $results = [];

    /**
     * Generic use of page root query
     *
     * @return \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    protected function getRootPageQuery()
    {
        $queryBuilder = $this->getQueryBuilderForTable('pages');
        $queryBuilder->select('uid', 'title')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('is_siteroot', $queryBuilder->createNamedParameter(true, \PDO::PARAM_INT))
            )
            ->orderBy('sorting');

        // Only hide deleted
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        return $queryBuilder;
    }

    /**
     * @param integer $id
     * @return array
     */
    public function getRootPage($id)
    {
        if (!isset($this->results[$id])) {
            $queryBuilder = $this->getRootPageQuery();
            $queryBuilder->andWhere($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($id, Connection::PARAM_INT)));
            $this->results[$id] = $queryBuilder->execute()->fetch();
        }

        return $this->results[$id];
    }

    /**
     * @return array
     */
    public function getRootPages()
    {
        $pages = [];
        $query = $this->getRootPageQuery()->execute();
        while ($row = $query->fetch()) {
            $pages[$row['uid']] = $this->results[$row['uid']] = $row;
        }
        return $pages;
    }
}
