<?php
namespace KoninklijkeCollective\MyRedirects\ViewHelper;

use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Get domain url for output
 *
 * @package KoninklijkeCollective\MyRedirects\ViewHelpers
 */
class DomainUrlViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * @var \KoninklijkeCollective\MyRedirects\Service\DomainService
     * @inject
     */
    protected $domainService;

    /**
     * Get domain url based on given redirect
     *
     * @param \KoninklijkeCollective\MyRedirects\Domain\Model\Redirect $redirect
     * @return array
     */
    public function render(Redirect $redirect = null)
    {
        $output = '/';

        if ($redirect === null) {
            $redirect = $this->renderChildren();
        }

        if ($redirect instanceof Redirect) {
            $output = $this->getDomainService()->getDomainUrlFromRedirect($redirect);
        }
        return $output;
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
