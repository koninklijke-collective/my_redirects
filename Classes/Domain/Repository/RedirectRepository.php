<?php
namespace KoninklijkeCollective\MyRedirects\Domain\Repository;

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
        'domain' => QueryInterface::ORDER_ASCENDING,
        'url' => QueryInterface::ORDER_ASCENDING,
        'destination' => QueryInterface::ORDER_ASCENDING,
        'counter' => QueryInterface::ORDER_ASCENDING,
    ];

    /**
     * Find redirects by given order
     *
     * @param array $filter
     * @param string $order
     * @param string $direction
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array
     */
    public function findByOrder($filter, $order, $direction)
    {
        $query = $this->createQuery();

        $newOrderings = [];
        switch ($order) {
            case 'url':
                $newOrderings = $this->defaultOrderings;
                $newOrderings['domain'] = $direction;
                $newOrderings['url'] = $direction;
                break;
            case 'destination':
            case 'last_hit':
            case 'counter':
            case 'has_moved':
                $newOrderings[$order] = $direction;
                foreach ($this->defaultOrderings as $order => $direction) {
                    if (!isset($newOrderings[$order])) {
                        $newOrderings[$order] = $direction;
                    }
                }
                break;
        }
        if (!empty($newOrderings)) {
            $query->setOrderings($newOrderings);
        }

        if (!empty($filter)) {
            $constraints = [];
            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'page':
                        $constraints[] = $query->equals('destination', (int) $value);
                        break;

                    case 'sword':
                        $constraints[] = $query->logicalOr(
                            $query->like('url', '%' . ltrim($value, '/') . '%', false),
                            $query->like('destination', '%' . $value . '%', false)
                        );
                        break;

                    case 'status':
                        if ($value == 'active') {
                            $constraints[] = $query->equals('active', true);
                        } elseif ($value == 'inactive') {
                            $constraints[] = $query->equals('active', false);
                        }
                        break;

                    case 'domain':
                        if (!empty($value)) {
                            $constraints[] = $query->equals('domain', $value);
                        }
                        break;
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
