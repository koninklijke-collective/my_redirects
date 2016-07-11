<?php
namespace KoninklijkeCollective\MyRedirects\ViewHelper;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Get all (active) domains in TYPO3
 *
 * @package KoninklijkeCollective\MyRedirects\ViewHelpers
 */
class DomainsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * @var \KoninklijkeCollective\MyRedirects\Service\DomainService
     * @inject
     */
    protected $domainService;

    /**
     * Get all configured domains
     *
     * @return array
     */
    public function render()
    {
        $domains = [];
        $data = $this->getDomainService()->getDomains();
        if (!empty($data)) {
            foreach ($data as $domain) {
                $domains[$domain['uid']] = $domain['domainName'];
            }
        }

        return $domains;
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\DomainService
     */
    protected function getDomainService()
    {
        if (!isset($this->domainService)) {
            $this->domainService = $this->getObjectManager()->get(\KoninklijkeCollective\MyRedirects\Service\DomainService::class);
        }
        return $this->domainService;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected function getObjectManager()
    {
        if (!isset($this->objectManager)) {
            $this->objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        }
        return $this->objectManager;
    }

}
