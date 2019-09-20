<?php

namespace KoninklijkeCollective\MyRedirects\ViewHelpers;

/**
 * ViewHelper: Domain Info for ui interaction
 */
class DomainInfoViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    use \KoninklijkeCollective\MyRedirects\Functions\ObjectManagerTrait;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('page', 'integer', 'Specified page id for domains', false);
        $this->registerArgument('domain', 'integer', 'Specific domain to show', false);
    }

    /**
     * @return string
     */
    public function render()
    {
        if ($this->arguments['page']) {
            $domains = $this->getDomainService()->getDomainsByStorageId($this->arguments['page']);
            $output = '';
            foreach ($domains as $domain) {
                $output .= $domain['domainName'] . LF;
            }
            return htmlspecialchars(trim($output));
        } elseif ($this->arguments['domain']) {
            $domain = $this->getDomainService()->getDomain($this->arguments['domain']);
            return htmlspecialchars($domain['domainName']);
        } else {
            return '';
        }
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\DomainService|object
     */
    protected function getDomainService()
    {
        return $this->getObjectManager()->get(\KoninklijkeCollective\MyRedirects\Service\DomainService::class);
    }
}
