<?php

namespace KoninklijkeCollective\MyRedirects\Install\Updates;

use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class RealUrlRedirectsImport
 *
 * @package KoninklijkeCollective\MyRedirects\Install\Updates
 */
class RealUrlRedirectsImport extends \TYPO3\CMS\Install\Updates\AbstractUpdate
{
    use \KoninklijkeCollective\MyRedirects\Functions\ObjectManagerTrait;
    use \KoninklijkeCollective\MyRedirects\Functions\QueryBuilderTrait;
    use \KoninklijkeCollective\MyRedirects\Functions\BackendUserAuthenticationTrait;

    /**
     * @var string
     */
    protected $title = 'Import "realurl" redirects to my_redirects extension';

    /**
     * @var integer
     */
    private $defaultRootPage;

    /**
     * Checks if an update is needed
     *
     * @param string &$description The description for the update
     * @return bool Whether an update is needed (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description)
    {
        static $update;
        if ($update === null) {
            $update = false;
            $existingRows = $this->getQueryBuilderForTable(Redirect::TABLE)
                ->select('*')
                ->from(Redirect::TABLE)
                ->count('uid')
                ->execute()->fetchColumn(0);
            if ($existingRows === 0 && $this->isWizardDone() === false && ExtensionManagementUtility::isLoaded('realurl')) {
                try {
                    $realurlRedirects = $this->getQueryBuilderForTable('tx_realurl_redirects')
                        ->select('*')
                        ->from('tx_realurl_redirects')
                        ->count('uid')
                        ->execute()->fetchColumn(0);

                    if ($realurlRedirects > 0) {
                        $description = 'For initial import you can use the deprecated RealURL redirects inside this module.';
                        $update = true;
                    }
                } catch (\Exception $e) {
                }
            }
        }
        return $update;
    }

    /**
     * Performs the database migrations if requested
     *
     * @param array &$databaseQueries Queries done in this update
     * @param string &$customMessages Custom messages
     * @return boolean
     */
    public function performUpdate(array &$databaseQueries, &$customMessages)
    {
        $migrated = 0;
        $userId = ($this->getBackendUserAuthentication()) ? (int)$this->getBackendUserAuthentication()->user['uid'] : 0;
        $query = $this->getQueryBuilderForTable('tx_realurl_redirects')
            ->select('*')
            ->from('tx_realurl_redirects')
            ->execute();

        /** @var \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
            ->getConnectionForTable(Redirect::TABLE);

        while ($row = $query->fetch()) {
            list($storage, $domainId) = $this->getDomainInfo((int)$row['domain_limit']);

            $urlHash = $this->generateNewHash($row['url']);
            if ($urlHash) {
                $queryBuilder = $connection->createQueryBuilder();
                $queryBuilder->insert(Redirect::TABLE)
                    ->set('pid', $storage, false)
                    ->set('tstamp', $row['tstamp'], false)
                    ->set('crdate', $row['tstamp'], false)
                    ->set('cruser_id', $userId, false)
                    ->set('url_hash', $urlHash)
                    ->set('url', $row['url'])
                    ->set('destination', $this->correctUrl($row['destination']))
                    ->set('last_referrer', $row['last_referer'])
                    ->set('counter', $row['counter'])
                    ->set('http_response', ($row['has_moved'] ? 301 : 302))
                    ->set('domain', $domainId)
                    ->set('root_page_domain', $storage . '-' . $domainId);

                $databaseQueries[] = $queryBuilder->getSQL();
                if ($queryBuilder->execute()) {
                    $migrated++;
                }
            }
        }

        return true;
    }

    /**
     * Correct url based on RealURL configuration
     * if defaultToHTMLsuffixOnPrev is not set, force the trailing / in url
     *
     * @param string $url
     * @return string
     */
    protected function correctUrl($url)
    {
        $urlParameters = parse_url($url);
        if (ExtensionManagementUtility::isLoaded('realurl')) {
            if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']) && (int)$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT']['fileName']['defaultToHTMLsuffixOnPrev'] === 0) {
                if (substr($url, -1) !== '/') {
                    if (isset($urlParameters['path'])) {
                        $pathInfo = pathinfo($urlParameters['path']);
                        if (empty($pathInfo['extension'])) {
                            $urlParameters['path'] = rtrim($urlParameters['path'], '/') . '/';
                            $url = \TYPO3\CMS\Core\Utility\HttpUtility::buildUrl($urlParameters);
                        }
                    }
                }
            }
        }

        // Only look if query is configured and link is relative to the root
        if (isset($urlParameters['query']) && !isset($urlParameters['host'])) {
            $idOnlyRegEx = '/^id=[1-9][0-9]{0,15}$/i';
            if (preg_match($idOnlyRegEx, $urlParameters['query'])) {
                $pageId = (int)str_replace('id=', '', $urlParameters['query']);
                if ($pageId > 0) {
                    $url = 't3://page?uid=' . $pageId;
                }
            } elseif (class_exists('TYPO3\CMS\Core\Utility\MathUtility')
                && method_exists('TYPO3\CMS\Core\Utility\MathUtility', 'canBeInterpretedAsInteger')
            ) {
                if (\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($urlParameters['query'])) {
                    $url = 't3://page?uid=' . $urlParameters['query'];
                }
            }
        }

        return $url;
    }

    /**
     * @param integer $domainId
     * @return array [storage, domainID]
     */
    protected function getDomainInfo($domainId)
    {
        $storage = null;

        if ($domainId > 0) {
            $domain = $this->getDomainService()->getDomain($domainId);
            if (!empty($domain)) {
                return [$domain['pid'], $domain['uid']];
            }
        }

        return [$this->getDefaultRootPage(), 0];
    }

    /**
     * @return integer
     */
    protected function getDefaultRootPage()
    {
        if ($this->defaultRootPage === null) {
            $rootPages = $this->getRootPageService()->getRootPages();
            $this->defaultRootPage = $rootPages[0]['uid'];
        }
        return $this->defaultRootPage;
    }

    /**
     * Generate hash with exception catch
     *
     * @param string $url
     * @return string
     */
    protected function generateNewHash($url)
    {
        try {
            return (string)$this->getRedirectService()->generateUrlHash($url);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\RedirectService|object
     */
    protected function getRedirectService()
    {
        return $this->getObjectManager()->get(\KoninklijkeCollective\MyRedirects\Service\RedirectService::class);
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\DomainService|object
     */
    protected function getDomainService()
    {
        return $this->getObjectManager()->get(\KoninklijkeCollective\MyRedirects\Service\DomainService::class);
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\RootPageService|object
     */
    protected function getRootPageService()
    {
        return $this->getObjectManager()->get(\KoninklijkeCollective\MyRedirects\Service\RootPageService::class);
    }
}
