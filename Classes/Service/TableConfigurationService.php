<?php

namespace KoninklijkeCollective\MyRedirects\Service;

/**
 * Service: TCA Functions
 * @package KoninklijkeCollective\MyRedirects\Service
 */
class TableConfigurationService implements \TYPO3\CMS\Core\SingletonInterface
{
    use \KoninklijkeCollective\MyRedirects\Functions\TranslateTrait;
    use \KoninklijkeCollective\MyRedirects\Functions\ObjectManagerTrait;
    use \KoninklijkeCollective\MyRedirects\Functions\BackendUserAuthenticationTrait;

    /**
     * @var array
     */
    protected $rootPages;

    /**
     * @var array
     */
    protected $domains;

    /**
     * @return boolean
     */
    public function hasAllowedDomains()
    {
        return !empty($this->parseDomainsInPageRoots(
            $this->getDomainService()->getDomains(),
            $this->getRootPageService()->getRootPages()
        ));
    }

    /**
     * @return void
     */
    public function addAllowedDomains($parameters)
    {
        $parameters['items'] = $this->parseDomainsInPageRoots(
            $this->getDomainService()->getDomains(),
            $this->getRootPageService()->getRootPages(),
            $parameters['row']['root_page_domain']
        );
    }

    /**
     * @param array $domains
     * @param array $pages
     * @param string $active
     * @return array
     */
    public function parseDomainsInPageRoots($domains, $pages, $active = null)
    {
        $activeRootPage = 0;
        if ($active) {
            list($activeRootPage) = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode('-', $active);
        }

        $data = [];
        // Reorder domains based on parent RootPage
        $items = [];
        foreach ($domains as $domain) {
            $items[$domain['pid']][] = $domain;
        }

        foreach ($pages as $page) {
            $pageId = $page['uid'];
            $hasAccess = $this->getBackendUserAuthentication()->isInWebMount($pageId);
            if ($hasAccess || $pageId === $activeRootPage) {
                $data[] = [
                    $page['title'],
                    '--div--'
                ];

                if ($hasAccess) {
                    $data[] = [
                        $this->translate('redirect.all.domains.in.root'),
                        $pageId . '-' . '0'
                    ];
                    if (isset($items[$pageId])) {
                        // Only add possible selection when more than 1 page
                        if (count($items[$pageId]) > 1) {
                            foreach ($items[$pageId] as $domain) {
                                if ($hasAccess || $active === $domain['pid'] . '-' . $domain['uid']) {
                                    $data[] = [
                                        $domain['domainName'],
                                        $domain['pid'] . '-' . $domain['uid']
                                    ];
                                }
                            }
                        }
                    }
                }

                unset($items[$pageId]);
            } else {
                $data[] = [
                    'No domains for root page found',
                    ''
                ];
            }
        }

        // Always render unknown domains for broken stuff
        if (!empty($items)) {
            $data[] = [
                'Unknown',
                '--div--'
            ];

            foreach ($items as $pageId => $rows) {
                foreach ($rows as $domain) {
                    if ($this->getBackendUserAuthentication()->isInWebMount($domain['pid']) || $active === $domain['pid'] . '-' . $domain['uid']) {
                        $data[] = [
                            $domain['pid'] . ': ' . $domain['domainName'],
                            $domain['pid'] . '-' . $domain['uid']
                        ];
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @return RootPageService|object
     */
    protected function getRootPageService()
    {
        return $this->getObjectManager()->get(RootPageService::class);
    }

    /**
     * @return DomainService|object
     */
    protected function getDomainService()
    {
        return $this->getObjectManager()->get(DomainService::class);
    }
}
