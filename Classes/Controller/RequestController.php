<?php
namespace KoninklijkeCollective\MyRedirects\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook: Frontend Request: Redirect controller
 *
 * @package KoninklijkeCollective\MyRedirects\Controller
 */
class RequestController
{

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * @var \KoninklijkeCollective\MyRedirects\Service\RedirectService
     * @inject
     */
    protected $redirectService;

    /**
     * @var \KoninklijkeCollective\MyRedirects\Service\DomainService
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
     * @return \KoninklijkeCollective\MyRedirects\Service\RedirectService
     */
    protected function getRedirectService()
    {
        if (!isset($this->redirectService)) {
            $this->redirectService = $this->getObjectManager()->get('KoninklijkeCollective\\MyRedirects\\Service\\RedirectService');
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
     * @return \KoninklijkeCollective\MyRedirects\Service\DomainService
     */
    protected function getDomainService()
    {
        if (!isset($this->domainService)) {
            $this->domainService = $this->getObjectManager()->get('KoninklijkeCollective\\MyRedirects\\Service\\DomainService');
        }
        return $this->domainService;
    }

}
