<?php

namespace KoninklijkeCollective\MyRedirects\Domain\Model\DTO;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Data Transfer Object: Filter
 *
 * @package KoninklijkeCollective\MyRedirects\Domain\Model\DTO
 */
class Filter
{

    /**
     * @var string
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const ORDER_URL = 'url';
    const ORDER_DESTINATION = 'destination';
    const ORDER_LAST_HIT = 'last_hit';
    const ORDER_COUNTER = 'counter';
    const ORDER_HAS_MOVED = 'has_moved';

    /**
     * @var \DateTime
     */
    protected $initiated;

    /**
     * @var string
     */
    protected $search;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $rootDomain;

    /**
     * @var array
     */
    private $_allowedOrders = [
        self::ORDER_URL,
        self::ORDER_DESTINATION,
        self::ORDER_LAST_HIT,
        self::ORDER_COUNTER,
        self::ORDER_HAS_MOVED
    ];

    /**
     * @var string
     */
    protected $order;

    /**
     * @var array
     */
    private $_allowedDirections = [
        QueryInterface::ORDER_ASCENDING,
        QueryInterface::ORDER_DESCENDING,
    ];

    /**
     * @var string
     */
    protected $direction;

    /**
     * Initialize object for internal usage
     *
     * @param array $row
     * @return static
     */
    public static function load($row = null)
    {
        $filter = new static();
        if ($row && is_array($row)) {
            $filter->apply($row);
        }
        return $filter;
    }

    /**
     * Decode object for internal caching
     *
     * @return array
     */
    public function unload()
    {
        return [
            'initiated' => $this->initiated->getTimestamp(),
            'search' => $this->search,
            'status' => $this->status,
            'rootDomain' => $this->rootDomain,
            'order' => $this->order,
            'direction' => $this->direction,
        ];
    }

    /**
     * Clean (reset) object
     *
     * @return array
     */
    public function getCleanObject()
    {
        $this->search = null;
        $this->status = null;
        $this->rootDomain = null;
        return $this->unload();
    }

    /**
     * Apply filter data to load correct object
     *
     * @param array $row
     * @return $this
     */
    public function apply(array $row)
    {
        if (!empty($row)) {
            foreach ($row as $key => $value) {
                try {
                    $method = 'set' . ucfirst($key);
                    if (is_callable([$this, $method])) {
                        $this->{$method}($value);
                        // Just try throw when invalid setter
                    }
                } catch (\InvalidArgumentException $e) {
                }
            }
        }
        return $this;
    }

    /**
     * DTO Constructor: Filter
     */
    public function __construct()
    {
        $this->initiated = new \DateTime();
    }

    /**
     * Check if filter is active
     *
     * @return boolean
     */
    public function isActive()
    {
        return (bool)(
            $this->getSearch() || $this->getStatus() || $this->getRootDomain()
        );
    }

    /**
     * Returns the Search
     *
     * @return string
     */
    public function getSearch()
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', ' ', $this->search);;
    }

    /**
     * Sets the Search
     *
     * @param string $search
     * @return $this
     */
    public function setSearch($search)
    {
        $this->search = $search;
        return $this;
    }

    /**
     * Returns the Status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the Status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Returns the Domain
     *
     * @return string
     */
    public function getRootDomain()
    {
        return $this->rootDomain;
    }

    /**
     * Sets the Domain
     *
     * @param string $rootDomain
     * @return $this
     */
    public function setRootDomain($rootDomain)
    {
        $info = \KoninklijkeCollective\MyRedirects\Domain\Model\Redirect::getDomainInfo($rootDomain);
        if ($info['storage'] !== 0) {
            $this->rootDomain = $rootDomain;
        }
        return $this;
    }

    /**
     * Returns the Order
     *
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Sets the Order
     *
     * @param string $order
     * @return $this
     */
    public function setOrder($order = null)
    {
        if (in_array($this->order, $this->_allowedOrders)) {
            $this->order = $order;
        } else {
            throw new \InvalidArgumentException('Invalid order given', 1496135747866);
        }
        return $this;
    }

    /**
     * Returns the Direction
     *
     * @return string
     */
    public function getDirection()
    {
        return in_array($this->direction, $this->_allowedDirections)
            ? $this->direction
            : QueryInterface::ORDER_ASCENDING;
    }

    /**
     * Sets the Direction
     *
     * @param string $direction
     * @return $this
     */
    public function setDirection($direction = null)
    {
        if (in_array($this->direction, $this->_allowedDirections)) {
            $this->direction = $direction;
        } else {
            throw new \InvalidArgumentException('Invalid direction given', 1496135702441);
        }
        return $this;
    }

}
