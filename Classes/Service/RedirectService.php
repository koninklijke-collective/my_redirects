<?php

namespace KoninklijkeCollective\MyRedirects\Service;

use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;
use KoninklijkeCollective\MyRedirects\Utility\ConfigurationUtility;
use KoninklijkeCollective\MyRedirects\Utility\EidUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Error\Http\BadRequestException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Service: Redirect
 *
 * @package KoninklijkeCollective\MyRedirects\Service
 */
class RedirectService
{

    use \KoninklijkeCollective\MyRedirects\Functions\ObjectManagerTrait;
    use \KoninklijkeCollective\MyRedirects\Functions\QueryBuilderTrait;

    /**
     * Find redirect based on path and domain
     * Skipped RedirectRepository (without ObjectManager) for performance in hook
     *
     * @param string $path
     * @param array $domain
     * @return Redirect
     */
    public function findRedirect($path, $domain)
    {
        $redirect = null;

        $keptParameters = [];
        if ($urlHash = $this->generateUrlHash($path, $keptParameters)) {
            $queryBuilder = $this->getQueryBuilderForTable(Redirect::TABLE);
            $queryBuilder->select('*')
                ->from(Redirect::TABLE)
                ->where($queryBuilder->expr()->eq('url_hash', $queryBuilder->createNamedParameter($urlHash)))
                ->orderBy('domain', Query::ORDER_DESCENDING);

            $domainId = null;
            $rootPageId = null;
            if ($domain && isset($domain['uid'])) {
                $domainId = $domain['uid'];
                $rootPageId = $domain['pid'];
                $queryBuilder->andWhere($queryBuilder->expr()->in(
                    'domain',
                    $queryBuilder->createNamedParameter([0, $domainId], Connection::PARAM_INT_ARRAY)
                ));
            }
            $query = $queryBuilder->execute();
            while ($row = $query->fetch()) {
                // If domain matches the redirect, all good it should redirect to this row!
                if (
                    // Current domain matches record domain
                    ($domainId && $row['domain'] === $domainId) ||
                    // Current root page matches redirects folder & is for all subdomains
                    ((int)$row['pid'] === $rootPageId && (int)$row['domain'] === 0)
                ) {
                    $_redirect = $row;
                    break;
                }
            }

            if (!empty($_redirect)) {
                $redirect = Redirect::create($_redirect);
                $redirect->setStoredParameters($keptParameters);
            }
        }

        return $redirect;
    }

    /**
     * Generate the Url Hash
     *
     * @param string $url target of current url without hostname so; ex: /index.php?id=12
     * @param array $keptParameters store skipped parameters for future redirect
     * @return string
     * @throws BadRequestException
     */
    public function generateUrlHash($url, &$keptParameters = null)
    {
        if ($urlParts = parse_url($url)) {
            $hash = null;
            if (!empty($urlParts['path'])) {
                // Remove trailing slash from url generation
                $urlParts['path'] = rtrim($urlParts['path'], '/');
            }
            if (!empty($urlParts['query'])) {
                $excludedQueryParameters = ConfigurationUtility::getCHashExcludedParameters();
                if (!empty($excludedQueryParameters)) {
                    parse_str($urlParts['query'], $queryParameters);
                    if (!empty($queryParameters)) {
                        foreach ($queryParameters as $key => $value) {
                            if (in_array($key, $excludedQueryParameters)) {
                                unset($queryParameters[$key]);
                                if (is_array($keptParameters)) {
                                    $keptParameters[$key] = $value;
                                }
                            }
                        }

                        $urlParts['query'] = (!empty($queryParameters) ? http_build_query($queryParameters) : null);
                    }
                }
            }
            $url = HttpUtility::buildUrl($urlParts);
            // Make sure the hash is case-insensitive
            $url = strtolower($url);
            return sha1($url);
        }

        throw new BadRequestException('Incorrect url given.', 1467622163);
    }

    /**
     * Handle redirect with core HTTP Response constants
     *
     * @param Redirect $redirect
     * @return void
     */
    public function handleRedirect(Redirect $redirect)
    {
        if ((bool)$_SERVER['HTTP_X_REDIRECT_SERVICE'] === false) {
            $queryBuilder = $this->getQueryBuilderForTable(Redirect::TABLE);
            $queryBuilder->update(Redirect::TABLE)
                ->set('counter', 'counter+1', false)
                ->set('last_hit', time())
                ->set('last_referrer', GeneralUtility::getIndpEnv('HTTP_REFERER'))
                ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($redirect->getUid(), Connection::PARAM_INT)))
                ->execute();
        }

        try {
            $destination = $this->generateLink($redirect->getDestination());
            if ($redirect->getStoredParameters()) {
                $urlParts = parse_url($destination);
                $urlParts['query'] .= '&' . http_build_query($redirect->getStoredParameters());
                $urlParts['query'] = trim($urlParts['query'], '&');
                $destination = HttpUtility::buildUrl($urlParts);
            }

            header('X-Redirect-Handler: my_redirects:' . $redirect->getUid());

            // Get response code constant from core
            $constantLookUp = HttpUtility::class . '::HTTP_STATUS_' . $redirect->getHttpResponse();
            $httpStatus = (defined($constantLookUp) ? constant($constantLookUp) : HttpUtility::HTTP_STATUS_302);
            HttpUtility::redirect($destination, $httpStatus);

        } catch (\Exception $e) {
            // If there is an exception while making the url, the configuration seems to be invalid
            // and should not crash by this extension
        }
    }

    /**
     * Generate link based on current page information
     *
     * @param string $link
     * @return string
     */
    protected function generateLink($link)
    {
        if (stripos($link, 't3://') === 0 || GeneralUtility::isValidUrl($link) === false) {
            $link = $this->getContentObjectRenderer(ConfigurationUtility::getDefaultRootPageId($link))->typoLink_URL(
                ['parameter' => $link]
            );

        }
        return $link;
    }

    /**
     * @param integer $pageId
     * @return ContentObjectRenderer
     */
    protected function getContentObjectRenderer($pageId = 1)
    {
        try {
            // Check if GLOBALS['TSFE'] is initiated correctly
            EidUtility::initializeTypoScriptFrontendController($pageId);
            return $GLOBALS['TSFE']->cObj;
        } catch (\Exception $e) {
            return GeneralUtility::makeInstance(ContentObjectRenderer::class);
        }
    }

    /**
     * @return RootPageService|object
     */
    protected function getRootPageService()
    {
        return $this->getObjectManager()->get(RootPageService::class);
    }

}
