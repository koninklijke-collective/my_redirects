<?php

namespace KoninklijkeCollective\MyRedirects\Service;

use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;
use TYPO3\CMS\Core\Database\Connection;

/**
 * Service: Web Domains
 */
class DomainService implements \TYPO3\CMS\Core\SingletonInterface
{
    use \KoninklijkeCollective\MyRedirects\Functions\QueryBuilderTrait;

    protected $results = [];

    /**
     * Generic use of domain query
     *
     * @return \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    protected function getDomainQuery()
    {
        $queryBuilder = $this->getQueryBuilderForTable('sys_domain');
        return $queryBuilder->select('*')->from('sys_domain')
            ->where($queryBuilder->expr()->eq('redirectTo', $queryBuilder->createNamedParameter('')))
            ->orderBy('sorting');
    }

    /**
     * Get domain from database just once..
     * If already retrieved, just return element
     *
     * @param integer $domainId
     * @return array
     */
    public function getDomain($domainId)
    {
        if (!isset($this->results[$domainId])) {
            $queryBuilder = $this->getDomainQuery();
            $queryBuilder->andWhere($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($domainId, Connection::PARAM_INT)));
            $this->results[$domainId] = $queryBuilder->execute()->fetch();
        }

        return $this->results[$domainId];
    }

    /**
     * Get all domains from database
     *
     * @return array
     */
    public function getDomains()
    {
        $domains = [];
        $query = $this->getDomainQuery()->execute();
        while ($row = $query->fetch()) {
            $domains[$row['uid']] = $this->results[$row['uid']] = $row;
        }

        return $domains;
    }

    /**
     * Obtains domain based on url
     *
     * @param string $domainName
     * @return array
     */
    public function getDomainByDomainName($domainName)
    {
        $queryBuilder = $this->getDomainQuery();
        $queryBuilder->andWhere($queryBuilder->expr()->eq('domainName', $queryBuilder->createNamedParameter($domainName)));
        $row = $queryBuilder->execute()->fetch();
        return is_array($row) ? $row : null;
    }

    /**
     * Obtains domain based on url
     *
     * @param integer $storageId
     * @return array
     */
    public function getDomainsByStorageId($storageId)
    {
        $queryBuilder = $this->getDomainQuery();
        $queryBuilder->andWhere($queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($storageId, Connection::PARAM_INT)));
        $query = $queryBuilder->execute();
        $domains = [];
        while ($row = $query->fetch()) {
            $domains[$row['uid']] = $this->results[$row['uid']] = $row;
        }
        return $domains;
    }

    /**
     * Get domain by specific redirect
     *
     * @param \KoninklijkeCollective\MyRedirects\Domain\Model\Redirect $redirect
     * @return array
     */
    public function getDomainNameByRedirect(Redirect $redirect)
    {
        $domain = null;
        if ($row = $this->getDomain($redirect->getDomain())) {
            $domain = $row['domainName'];
        } elseif ($domains = $this->getDomainsByStorageId($redirect->getPid())) {
            $_domain = reset($domains);
            if ($_domain && !empty($_domain['domainName'])) {
                $domain = $_domain['domainName'];
            }
        }
        return $domain;
    }
}
