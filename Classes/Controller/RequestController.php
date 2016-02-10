<?php
namespace Serfhos\MyRedirects\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook: Frontend Request: Redirect controller
 *
 * @package Serfhos\MyRedirects\Controller
 */
class RequestController
{

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * @var \Serfhos\MyRedirects\Service\RedirectService
     * @inject
     */
    protected $redirectService;

    /**
     * @var \Serfhos\MyRedirects\Service\DomainService
     * @inject
     */
    protected $domainService;

    /**
     * Action: Redirect current url if configured
     *
     * @return void
     */
    public function redirectAction()
    {
        $path = GeneralUtility::getIndpEnv('TYPO3_SITE_SCRIPT');
        $path = strtolower(trim($path));
        if (!empty($path)) {
            $redirect = $this->getRedirectService()->queryByPathAndDomain(
                $path,
                $this->getDomainService()->getCurrentDomainId()
            );
            if (is_array($redirect) && (int) $redirect['uid'] > 0) {
                $this->getRedirectService()->handleRedirect($redirect);
            }
        }
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

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected function getObjectManager()
    {
        if (!isset($this->objectManager)) {
            $this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        }
        return $this->objectManager;
    }

    /**
     * @return \Serfhos\MyRedirects\Service\DomainService
     */
    protected function getDomainService()
    {
        if (!isset($this->domainService)) {
            $this->domainService = $this->getObjectManager()->get('Serfhos\\MyRedirects\\Service\\DomainService');
        }
        return $this->domainService;
    }
}