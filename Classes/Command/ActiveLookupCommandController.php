<?php
namespace Serfhos\MyRedirects\Command;

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
        // force a 20 seconds curl timeout
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
        } elseif (\TYPO3\CMS\Core\Utility\GeneralUtility::isValidUrl($defaultDomain) === false) {
            throw new \TYPO3\CMS\Core\Exception('Default domain invalid');
        }
        if (!($this->persistenceManager instanceof \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface)) {
            throw new \TYPO3\CMS\Core\Exception('No persistance manager loaded');
        }
        if (!($this->redirectRepository instanceof \Serfhos\MyRedirects\Domain\Repository\RedirectRepository)) {
            throw new \TYPO3\CMS\Core\Exception('No repository loaded');
        }
        if (!($this->redirectService instanceof \Serfhos\MyRedirects\Service\RedirectService)) {
            throw new \TYPO3\CMS\Core\Exception('No redirect service loaded');
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
        $redirects = $this->redirectRepository->findAll();
        foreach ($redirects as $redirect) {
            $i++;
            if ($redirect instanceof \Serfhos\MyRedirects\Domain\Model\Redirect) {
                $check = true;

                // only check redirects that are not already checked today
                $lastChecked = $redirect->getLastChecked();
                if ($lastChecked instanceof \DateTime) {
                    $yesterday = new \DateTime('yesterday');
                    $check = ($lastChecked < $yesterday);
                }

                if ($check) {
                    $active = $redirect->getActive();
                    if ($active === true) {
                        $this->redirectService->activeLookup($redirect, $defaultDomain);
                        $this->redirectRepository->update($redirect);
                    }
                }
            }

            if ($i % 50 === 0) {
                $this->persistenceManager->persistAll();
            }
        }

        $this->persistenceManager->persistAll();

        return true;
    }
}