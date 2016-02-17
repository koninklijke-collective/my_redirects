<?php
namespace Serfhos\MyRedirects\Domain\Model;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Model: Redirect
 *
 * @package Serfhos\MyRedirects\Domain\Model
 */
class Redirect extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * @var \DateTime
     */
    protected $crdate;

    /**
     * @var \DateTime
     */
    protected $tstamp;

    /**
     * @var string
     */
    protected $urlHash;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $destination;

    /**
     * @var string
     */
    protected $lastReferrer;

    /**
     * @var integer
     */
    protected $httpResponse;

    /**
     * @var integer
     */
    protected $counter;

    /**
     * @var integer
     */
    protected $domain;

    /**
     * @var boolean
     */
    protected $active;

    /**
     * @var \DateTime
     */
    protected $lastHit;

    /**
     * @var \DateTime
     */
    protected $lastChecked;

    /**
     * @var string
     */
    protected $inactiveReason;

    /**
     * Returns the Url Hash
     *
     * @return string
     */
    public function getUrlHash()
    {
        return $this->urlHash;
    }

    /**
     * Sets the Url Hash
     *
     * @param string $urlHash
     * @return void
     */
    public function setUrlHash($urlHash)
    {
        $this->urlHash = $urlHash;
    }

    /**
     * Returns the Active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Sets the Active
     *
     * @param boolean $active
     * @return void
     */
    public function setActive($active)
    {
        $this->active = (bool) $active;
    }

    /**
     * Returns the HTTP Response
     *
     * @return integer
     */
    public function getHttpResponse()
    {
        return $this->httpResponse;
    }

    /**
     * Sets the HTTP Response
     *
     * @param integer $httpResponse
     * @return void
     */
    public function setHttpResponse($httpResponse)
    {
        $this->httpResponse = $httpResponse;
    }

    /**
     * Returns the Counter
     *
     * @return integer
     */
    public function getCounter()
    {
        return $this->counter;
    }

    /**
     * Sets the Counter
     *
     * @param integer $counter
     * @return void
     */
    public function setCounter($counter)
    {
        $this->counter = $counter;
    }

    /**
     * Returns the Destination
     *
     * @return string
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Get absolute destination based on configured destination
     *
     * @return string
     */
    public function getAbsoluteDestination()
    {
        $destination = $this->destination;
        if (MathUtility::canBeInterpretedAsInteger($destination)) {
            $destination = 'Page: ' . $destination;
        }
        return $destination;
    }

    /**
     * Sets the Destination
     *
     * @param string $destination
     * @return void
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    /**
     * Returns the domain
     *
     * @return integer
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Sets the domain
     *
     * @param integer $domain
     * @return void
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Returns the Last Checked
     *
     * @return \DateTime
     */
    public function getLastChecked()
    {
        return $this->lastChecked;
    }

    /**
     * Get age of last checked date, based on BackendUtility
     *
     * @return string
     */
    public function getAgeLastChecked()
    {
        if ($this->lastChecked instanceof \DateTime) {
            return BackendUtility::dateTimeAge($this->lastChecked->getTimestamp());
        }

        return null;
    }

    /**
     * Sets the Last Checked
     *
     * @param \DateTime $lastChecked
     * @return void
     */
    public function setLastChecked(\DateTime $lastChecked)
    {
        $this->lastChecked = $lastChecked;
    }

    /**
     * Returns the Last Referrer
     *
     * @return string
     */
    public function getLastReferrer()
    {
        return $this->lastReferrer;
    }

    /**
     * Sets the Last Referrer
     *
     * @param string $lastReferrer
     * @return void
     */
    public function setLastReferrer($lastReferrer)
    {
        $this->lastReferrer = $lastReferrer;
    }

    /**
     * Returns the Update At
     *
     * @return \DateTime
     */
    public function getUpdateAt()
    {
        return $this->tstamp;
    }

    /**
     * Sets the Update At
     *
     * @param \DateTime $updateAt
     * @return void
     */
    public function setUpdateAt(\DateTime $updateAt)
    {
        $this->tstamp = $updateAt;
    }

    /**
     * Returns the Url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Sets the Url
     *
     * @param string $url
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Returns the Inactive Reason
     *
     * @return string
     */
    public function getInactiveReason()
    {
        return $this->inactiveReason;
    }

    /**
     * Sets the Inactive Reason
     *
     * @param string $inactiveReason
     * @return void
     */
    public function setInactiveReason($inactiveReason)
    {
        $this->inactiveReason = $inactiveReason;
    }

    /**
     * Returns the LastHit
     *
     * @return \DateTime
     */
    public function getLastHit()
    {
        return $this->lastHit;
    }

    /**
     * Sets the LastHit
     *
     * @param \DateTime $lastHit
     * @return void
     */
    public function setLastHit(\DateTime $lastHit)
    {
        $this->lastHit = $lastHit;
    }

    /**
     * Returns the Tstamp
     *
     * @return \DateTime
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * Returns the Crdate
     *
     * @return \DateTime
     */
    public function getCrdate()
    {
        return $this->crdate;
    }

}