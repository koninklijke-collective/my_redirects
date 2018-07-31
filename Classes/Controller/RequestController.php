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
     * @var \KoninklijkeCollective\MyRedirects\Service\RedirectService
     */
    protected $redirectService;

    /**
     * @var \KoninklijkeCollective\MyRedirects\Service\DomainService
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
        if (!empty($path)) {
            try {
                $redirect = $this->getRedirectService()->queryByPathAndDomain(
                    $path,
                    $this->getDomainService()->getCurrentDomainId()
                );
                if (is_array($redirect) && (int)$redirect['uid'] > 0) {
                    $this->getRedirectService()->handleRedirect($redirect);
                }
            } catch (\Exception $e) {
                // There should be no exception when trying to redirect!
            }
        }
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\RedirectService
     */
    protected function getRedirectService()
    {
        if ($this->redirectService === null) {
            $this->redirectService = GeneralUtility::makeInstance(\KoninklijkeCollective\MyRedirects\Service\RedirectService::class);
        }
        return $this->redirectService;
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\DomainService
     */
    protected function getDomainService()
    {
        if ($this->domainService === null) {
            $this->domainService = GeneralUtility::makeInstance(\KoninklijkeCollective\MyRedirects\Service\DomainService::class);
        }
        return $this->domainService;
    }

    /**
     * @return \TYPO3\CMS\Core\Charset\CharsetConverter
     */
    protected function getCharsetConverter()
    {
        return GeneralUtility::makeInstance(\TYPO3\CMS\Core\Charset\CharsetConverter::class);
    }
}
