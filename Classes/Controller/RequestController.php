<?php
namespace Serfhos\MyRedirects\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class RequestController
{

    /**
     * @var \Serfhos\MyRedirects\Service\RedirectService
     * @inject
     */
    protected $redirectService;

    /**
     * @var \Serfhos\MyRedirects\Domain\Repository\RedirectRepository
     * @inject
     */
    protected $redirectRepository;

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
                $this->getRedirectService()->getCurrentDomainId()
            );
            if (is_array($redirect) && (int) $redirect['uid'] > 0) {
                $this->getRedirectService()->handleRedirect($redirect);
            }
        }
    }

    /**
     * Get the ObjectManager
     *
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected function getObjectManager()
    {
        return GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
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