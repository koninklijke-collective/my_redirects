<?php

namespace KoninklijkeCollective\MyRedirects\Service;

use KoninklijkeCollective\MyRedirects\Domain\Model\DTO\Filter;

/**
 * Service: Filter Data
 */
class FilterDataService implements \TYPO3\CMS\Core\SingletonInterface
{
    use \KoninklijkeCollective\MyRedirects\Functions\BackendUserAuthenticationTrait;

    const KEY = 'tx_myredirects_filter';

    /**
     * Loads module data for user settings or returns a fresh object initially
     *
     * @return \KoninklijkeCollective\MyRedirects\Domain\Model\DTO\Filter
     */
    public function loadModuleFilter()
    {
        $moduleData = $this->getBackendUserAuthentication()->getModuleData(self::KEY);
        $filter = Filter::load($moduleData);
        return $filter;
    }

    /**
     * Persists serialized module data to user settings
     *
     * @param \KoninklijkeCollective\MyRedirects\Domain\Model\DTO\Filter $filter
     * @return void
     */
    public function persistModuleFilter(Filter $filter)
    {
        $this->getBackendUserAuthentication()->pushModuleData(self::KEY, $filter->unload());
    }
}
