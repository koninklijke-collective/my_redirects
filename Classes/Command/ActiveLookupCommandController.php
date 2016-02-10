<?php
namespace Serfhos\MyRedirects\Command;

use Serfhos\MyRedirects\Domain\Model\Redirect;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ExtBase Command: Refresh all redirects states
 *
 * @package Serfhos\MyRedirects\Command
 */
class ActiveLookupCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController
{

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
     * @inject
     */
    protected $persistenceManager;

    /**
     * @var \Serfhos\MyRedirects\Domain\Repository\RedirectRepository
     * @inject
     */
    protected $redirectRepository;

    /**
     * @var \Serfhos\MyRedirects\Service\RedirectService
     * @inject
     */
    protected $redirectService;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Force a 20 seconds curl timeout
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlTimeout'] = 20;
    }

    /**
     * Command: Refresh feeds
     *
     * @param string $defaultDomain Default domain (ex: http://serfhos.com/)
     * @return boolean
     */
    public function refreshCommand($defaultDomain = '')
    {
        if ($this->validateVariables($defaultDomain)) {
            return $this->updateRedirects($defaultDomain);
        }
        return false;
    }

    /**
     * Validate required variables
     *
     * @param string $defaultDomain
     * @throws \TYPO3\CMS\Core\Exception
     * @return boolean
     */
    protected function validateVariables($defaultDomain)
    {
        if (empty($defaultDomain)) {
            throw new \TYPO3\CMS\Core\Exception('No default domain configured');
        } elseif (GeneralUtility::isValidUrl($defaultDomain) === false) {
            throw new \TYPO3\CMS\Core\Exception('Default domain invalid');
        }

        return true;
    }

    /**
     * Goes through all items in the cache and updates them.
     *
     * @param string $defaultDomain
     * @return boolean
     */
    protected function updateRedirects($defaultDomain)
    {
        $i = 0;
        $redirects = $this->getRedirectRepository()->findAll();
        foreach ($redirects as $redirect) {
            $i++;
            if ($redirect instanceof Redirect) {
                $check = true;

                // Only check redirects that are not already checked today
                $lastChecked = $redirect->getLastChecked();
                if ($lastChecked instanceof \DateTime) {
                    $yesterday = new \DateTime('yesterday');
                    $check = ($lastChecked < $yesterday);
                }

                if ($check) {
                    // Only do daily checks on active url's
                    $active = $redirect->getActive();
                    if ($active === true) {
                        $this->getRedirectService()->activeLookup($redirect, $defaultDomain);
                        $this->getRedirectRepository()->update($redirect);
                    }
                }
            }

            if ($i % 50 === 0) {
                $this->getPersistenceManager()->persistAll();
            }
        }

        $this->getPersistenceManager()->persistAll();

        return true;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected function getObjectManager()
    {
        return GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
     */
    protected function getPersistenceManager()
    {
        if (!isset($this->persistenceManager)) {
            $this->persistenceManager = $this->getObjectManager()->get('TYPO3\\CMS\\Extbase\\Persistence\\PersistenceManagerInterface');
        }
        return $this->persistenceManager;
    }

    /**
     * @return \Serfhos\MyRedirects\Domain\Repository\RedirectRepository
     */
    protected function getRedirectRepository()
    {
        if (!isset($this->redirectRepository)) {
            $this->redirectRepository = $this->getObjectManager()->get('Serfhos\\MyRedirects\\Domain\\Repository\\RedirectRepository');
        }
        return $this->redirectRepository;
    }

    /**
     * @return \Serfhos\MyRedirects\Service\RedirectService
     */
    protected function getRedirectService()
    {
        if (!isset($this->redirectService)) {
            $this->redirectService = $this->getObjectManager()->get('Serfhos\\MyRedirects\\Service\\RedirectService');
        }
        return $this->redirectService;
    }
}