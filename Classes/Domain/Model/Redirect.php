<?php

namespace KoninklijkeCollective\MyRedirects\Domain\Model;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Model: Redirect
 *
 * @package KoninklijkeCollective\MyRedirects\Domain\Model
 */
class Redirect extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    const TABLE = 'tx_myredirects_domain_model_redirect';

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
     * @var integer
     */
    protected $domain;

    /**
     * @var string
     */
    protected $backendNote;

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
     * @var string
     */
    protected $rootPageDomain;

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
     * @var array
     */
    protected $_internal_storedParameters;

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
     * @return $this
     */
    public function setUrlHash($urlHash)
    {
        $this->urlHash = $urlHash;
        return $this;
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
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = (bool)$active;
        return $this;
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
     * @return $this
     */
    public function setHttpResponse($httpResponse)
    {
        $this->httpResponse = $httpResponse;
        return $this;
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
     * @return $this
     */
    public function setCounter($counter)
    {
        $this->counter = $counter;
        return $this;
    }

    /**
     * Returns the Backend Note
     *
     * @return string
     */
    public function getBackendNote()
    {
        return $this->backendNote;
    }

    /**
     * Sets the Backend Note
     *
     * @param string $backendNote
     * @return $this
     */
    public function setBackendNote($backendNote)
    {
        $this->backendNote = $backendNote;
        return $this;
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
        // linking to any t3:// syntax
        if (stripos($destination, 't3://') === 0) {
            try {
                $linkService = GeneralUtility::makeInstance(LinkService::class);
                $urn = $linkService->resolveByStringRepresentation($destination);

                switch ($urn['type']) {
                    case LinkService::TYPE_PAGE:
                        $destination = 'Page: ' . $urn['pageuid'];
                        break;
                    case LinkService::TYPE_FILE:
                        $destination = 'File: ' . $urn['file']->getName();
                        break;
                    case LinkService::TYPE_RECORD:
                        $destination = 'Record: ' . ($urn['identifier'] ?? $urn['uid']);
                        break;
                }
            } catch (\Exception $e) {
            }
        }
        return $destination;
    }

    /**
     * Sets the Destination
     *
     * @param string $destination
     * @return $this
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
        return $this;
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
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * Returns the root page domain
     *
     * @return string
     */
    public function getRootPageDomain()
    {
        return $this->rootPageDomain;
    }

    /**
     * Sets the root page
     *
     * @param string $rootPageDomain
     * @return $this
     */
    public function setRootPageDomain($rootPageDomain)
    {
        $this->rootPageDomain = $rootPageDomain;
        return $this;
    }

    /**
     * Get the domain info based on configured root page domain
     *
     * @param string $domain
     * @return array
     */
    public static function getDomainInfo($domain)
    {
        list($storage, $domainId) = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode('-', $domain);
        return [
            'storage' => $storage,
            'domain' => $domainId
        ];
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
     * @param \DateTime|integer $lastChecked
     * @return $this
     */
    public function setLastChecked($lastChecked)
    {
        if (!($lastChecked instanceof \DateTime) && !empty($lastChecked) && MathUtility::canBeInterpretedAsInteger($lastChecked)) {
            $lastChecked = new \DateTime('@' . $lastChecked);
        }
        $this->lastChecked = $lastChecked;
        return $this;
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
     * @return $this
     */
    public function setLastReferrer($lastReferrer)
    {
        $this->lastReferrer = $lastReferrer;
        return $this;
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
     * @param \DateTime|integer $updateAt
     * @return $this
     */
    public function setUpdateAt($updateAt)
    {
        if (!($updateAt instanceof \DateTime) && !empty($updateAt) && MathUtility::canBeInterpretedAsInteger($updateAt)) {
            $updateAt = new \DateTime('@' . $updateAt);
        }
        $this->tstamp = $updateAt;
        return $this;
    }

    /**
     * Returns the Url Length
     *
     * @return integer
     */
    public function getUrlLength()
    {
        return strlen($this->url);
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
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
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
     * @return $this
     */
    public function setInactiveReason($inactiveReason)
    {
        $this->inactiveReason = $inactiveReason;
        return $this;
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
     * @param \DateTime|integer $lastHit
     * @return $this
     */
    public function setLastHit($lastHit)
    {
        if (!($lastHit instanceof \DateTime) && !empty($lastHit) && MathUtility::canBeInterpretedAsInteger($lastHit)) {
            $lastHit = new \DateTime('@' . $lastHit);
        }
        $this->lastHit = $lastHit;
        return $this;
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

    /**
     * Get record data for TYPO3 internal core usage
     *
     * @return array
     */
    public function getRecordData()
    {
        return [
            'uid' => $this->uid,
            'pid' => $this->pid,
            'url' => $this->url,
            'destination' => $this->destination,
            'active' => $this->active,
        ];
    }

    /**
     * Create redirect object for rendering
     *
     * @param array $row
     * @return Redirect
     */
    public static function create($row)
    {
        /** @var Redirect $redirect */
        $redirect = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Redirect::class);
        foreach ($row as $key => $value) {
            $method = 'set' . \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($key);
            if (is_callable([$redirect, $method])) {
                $redirect->{$method}($value);
            } else {
                $redirect->{$key} = $value;
            }
        }
        return $redirect;
    }

    /**
     * @param array $storedParameters
     * @return $this
     */
    public function setStoredParameters($storedParameters = [])
    {
        $this->_internal_storedParameters = $storedParameters;
        return $this;
    }

    /**
     * @return array
     */
    public function getStoredParameters()
    {
        return $this->_internal_storedParameters;
    }
}
