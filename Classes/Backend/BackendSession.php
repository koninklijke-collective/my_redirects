<?php
namespace Serfhos\MyRedirects\Backend;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Backend session wrapper
 *
 * @package Serfhos\MyRedirects\Backend
 */
class BackendSession
{

    /**
     * @var mixed
     */
    protected $contents;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected $backendUserAuthentication;

    /**
     * @param \TYPO3\CMS\Core\Authentication\BackendUserAuthentication $backendUserAuthentication
     * @return BackendSession chaining possibility
     */
    public function setBackendUserAuthentication(
        BackendUserAuthentication $backendUserAuthentication
    ) {
        $this->backendUserAuthentication = $backendUserAuthentication;
        return $this;
    }

    /**
     * Creates a session if it does not exist yet
     *
     * @param string $key
     * @param mixed $contents
     * @return void
     */
    public function createSession($key, $contents = null)
    {
        $this->key = $key;
        $this->contents = $contents;

        if ($this->backendUserAuthentication->getSessionData($key) === null) {
            $this->saveSessionData(array('contents' => $contents));
        }
    }

    /**
     * Save the provided array into the session
     *
     * @param array $sessionArray
     * @return void
     */
    protected function saveSessionData(array $sessionArray)
    {
        $this->backendUserAuthentication->setAndSaveSessionData($this->key, serialize($sessionArray));
    }

    /**
     * Returns the session contents
     *
     * @param string $key
     * @return mixed
     */
    public function getSessionContents($key)
    {
        $sessionData = $this->backendUserAuthentication->getSessionData($key);
        if ($sessionData !== null) {
            $content = unserialize($sessionData);
            if (isset($content['contents'])) {
                return $content['contents'];
            }
        }
        return false;
    }

    /**
     * Saves the provided contents into the session
     *
     * @param mixed $contents
     * @return void
     */
    public function saveSessionContents($contents)
    {
        $this->saveSessiondata(array('contents' => $contents));
    }
}