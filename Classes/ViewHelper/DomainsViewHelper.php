<?php
namespace Serfhos\MyRedirects\ViewHelper;

use Serfhos\MyRedirects\Utility\DomainUtility;

/**
 * Get all (active) domains in TYPO3
 *
 * @package Serfhos\MyRedirects\ViewHelpers
 */
class DomainsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Render confirm link with sprite icon
     *
     * @return array
     */
    public function render()
    {
        $domains = array();

        $allDomains = DomainUtility::getDomains();
        if (!empty($allDomains)) {
            foreach ($allDomains as $domain) {
                $domains[$domain['uid']] = $domain['domainName'];
            }
        }

        return $domains;
    }
}