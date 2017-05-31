<?php

namespace KoninklijkeCollective\MyRedirects\Hook;

use KoninklijkeCollective\MyRedirects\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook: Frontend Request: Redirect controller
 *
 * @package KoninklijkeCollective\MyRedirects\Controller
 */
class RedirectActionHook
{

    /**
     * Action: Redirect current url if configured
     *
     * @return void
     */
    public function redirectAction()
    {
        $path = GeneralUtility::getIndpEnv('TYPO3_SITE_SCRIPT');
        $path = mb_strtolower($path, 'utf-8');

        if (!empty($path)) {
            try {
                // Get hook objects for queryByPathAndDomain
                $hookObjects = null;
                // Example: $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['my_redirects']['queryByPathAndDomainHook'][] = Your\Class\For\Hook::class;
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][ConfigurationUtility::EXTENSION]['queryByPathAndDomainHook'])) {
                    $hookObjects = [];
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][ConfigurationUtility::EXTENSION]['queryByPathAndDomainHook'] as $class) {
                        $hookObject = GeneralUtility::makeInstance($class);
                        if ($hookObject !== null) {
                            $hookObjects[] = $hookObject;
                        }
                    }
                }

                $redirect = null;
                $domain = $this->getDomainService()->getDomainByDomainName(
                    GeneralUtility::getIndpEnv('HTTP_HOST')
                );
                /** @var string $fields @deprecated */
                $fields = '*';
                if ($hookObjects) {
                    // Hook: beforeQueryByPathAndDomain
                    foreach ($hookObjects as $hookObject) {
                        if (is_callable([$hookObject, 'beforeQueryByPathAndDomain'])) {
                            $redirect = $hookObject->beforeQueryByPathAndDomain($redirect, $path, $domain, $fields);
                        }
                    }
                }
                $redirect = $this->getRedirectService()->findRedirect(
                    $path,
                    $domain
                );

                if ($hookObjects) {
                    // Hook: afterQueryByPathAndDomain
                    foreach ($hookObjects as $hookObject) {
                        if (is_callable([$hookObject, 'afterQueryByPathAndDomain'])) {
                            $redirect = $hookObject->afterQueryByPathAndDomain($redirect, $path, $domain, $fields);
                        }
                    }
                }
                if ($redirect instanceof \KoninklijkeCollective\MyRedirects\Domain\Model\Redirect) {
                    $this->getRedirectService()->handleRedirect($redirect);
                }
            } catch (\Exception $e) {
                // There should be no exception when trying to redirect!
            }
        }
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\DomainService|object
     */
    protected function getDomainService()
    {
        return GeneralUtility::makeInstance(\KoninklijkeCollective\MyRedirects\Service\DomainService::class);
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\RedirectService|object
     */
    protected function getRedirectService()
    {
        return GeneralUtility::makeInstance(\KoninklijkeCollective\MyRedirects\Service\RedirectService::class);
    }

}
