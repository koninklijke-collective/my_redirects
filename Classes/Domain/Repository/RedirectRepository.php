<?php

namespace KoninklijkeCollective\MyRedirects\Domain\Repository;

use KoninklijkeCollective\MyRedirects\Domain\Model\DTO\Filter;
use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Repository: Redirects
 *
 * @package KoninklijkeCollective\MyRedirects\Domain\Repository
 */
class RedirectRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    /**
     * @var array
     */
    protected $defaultOrderings = [
        'url' => QueryInterface::ORDER_ASCENDING,
        'pid' => QueryInterface::ORDER_ASCENDING,
        'domain' => QueryInterface::ORDER_ASCENDING,
        'destination' => QueryInterface::ORDER_ASCENDING,
        'counter' => QueryInterface::ORDER_ASCENDING,
    ];

    /**
     * Query specific redirects by filter object
     *
     * @param Filter $filter
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findAllByFilter(Filter $filter = null)
    {
        $query = $this->createQuery();

        if ($filter->getOrder()) {
            $newOrderings = [];
            switch ($filter->getOrder()) {
                case Filter::ORDER_URL:
                    $newOrderings = $this->defaultOrderings;
                    $newOrderings['domain'] = $filter->getDirection();
                    $newOrderings['url'] = $filter->getDirection();
                    break;
                default:
                    $newOrderings[$filter->getOrder()] = $filter->getDirection();
                    foreach ($this->defaultOrderings as $order => $direction) {
                        if (!isset($newOrderings[$order])) {
                            $newOrderings[$order] = $direction;
                        }
                    }
                    break;
            }
        }

        if (!empty($newOrderings)) {
            $query->setOrderings($newOrderings);
        }

        if ($filter->isActive()) {
            $constraints = [];
            if ($value = $filter->getSearch()) {
                $constraints[] = $query->logicalOr([
                    $query->like('url', '%' . ltrim($value, '/') . '%', false),
                    $query->like('destination', '%' . $value . '%', false)
                ]);
            }

            if ($value = $filter->getStatus()) {
                if ($value === Filter::STATUS_ACTIVE) {
                    $constraints[] = $query->equals('active', true);
                } elseif ($value === Filter::STATUS_INACTIVE) {
                    $constraints[] = $query->equals('active', false);
                }
            }

            if ($value = $filter->getRootDomain()) {
                $info = Redirect::getDomainInfo($value);
                if ($info['storage'] > 0) {
                    $constraints[] = $query->equals('pid', $info['storage']);
                    if ($info['domain'] > 0) {
                        $constraints[] = $query->in('domain', [0, $info['domain']]);
                    }
                }
            }

            if (!empty($constraints)) {
                $query->matching($query->logicalAnd($constraints));
            }
        }

        return $query->execute();
    }

    /**
     * Returns a query for objects of this repository
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryInterface
     */
    public function createQuery()
    {
        $query = parent::createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        return $query;
    }
}
