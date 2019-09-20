<?php

namespace KoninklijkeCollective\MyRedirects\Functions;

/**
 * Trait: Backend User retrieval
 */
trait BackendUserAuthenticationTrait
{

    /**
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUserAuthentication()
    {
        return $GLOBALS['BE_USER'];
    }
}
