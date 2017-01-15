<?php
namespace KoninklijkeCollective\MyRedirects\Service;

use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;
use KoninklijkeCollective\MyRedirects\Utility\EidUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Error\Http\BadRequestException;
use TYPO3\CMS\Core\Error\Http\StatusException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Service: Handle Redirects
 *
 * @package KoninklijkeCollective\MyRedirects\Service
 */
class RedirectService implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * @var array
     */
    protected $keptQueryParameters = [];

    /**
     * Do an active lookup for redirect
     *
     * @param \KoninklijkeCollective\MyRedirects\Domain\Model\Redirect $redirect
     * @param string $defaultDomain
     * @return void
     */
    public function activeLookup(Redirect $redirect, $defaultDomain = null)
    {
        $active = true;
        $url = $redirect->getUrl();

        if ($defaultDomain === null) {
            $defaultDomain = GeneralUtility::getHostname();
        }

        if (!empty($url)) {
            $urlDomain = $this->getDomainService()->getDomainUrlFromRedirect($redirect);
            // this should be done in the domain service..
            if ($urlDomain == '/') {
                $url = rtrim($defaultDomain, '/') . '/' . $url;
            } else {
                $url = $urlDomain . $url;
            }

            $urlDetails = parse_url($url);
            if (!isset($urlDetails['scheme'])) {
                $url = (GeneralUtility::getIndpEnv('TYPO3_SSL') ? 'https://' : 'http://') . $url;
            }

            $details = [];
            $this->curlUrl($url, $details);

            if ((int)$details['response']['http_code'] !== 200) {
                $active = false;

                if ((int)$details['response']['http_code'] === 0) {
                    $redirect->setInactiveReason('Response timeout');
                } elseif (isset($details['error']['id'])) {
                    $redirect->setInactiveReason($details['error']['id'] . ': ' . $details['error']['message']);
                } else {
                    $redirect->setInactiveReason('Unknown: ' . var_export($details, true));
                }
            } elseif ($details['response']['url'] == $url) {
                $active = false;
                $redirect->setInactiveReason('Redirect got stuck, could be timeout');
            }
            $redirect->setActive($active);
            $redirect->setLastChecked(new \DateTime());

            if ($active === true) {
                $redirect->setInactiveReason('');
            }
        } else {
            $redirect->setActive(false);
        }
    }

    /**
     * Get complete report from curled url
     *
     * @param string $url
     * @param array $info
     */
    protected function curlUrl($url, &$info = [])
    {
        // Use cURL for: http, https, ftp, ftps, sftp and scp
        if (preg_match('/^(?:http|ftp)s?|s(?:ftp|cp):/', $url)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_NOBODY, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            // Some sites need a user-agent
            curl_setopt($ch, CURLOPT_USERAGENT, 'my_redirects: Redirect Lookup/1.0');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'X-REDIRECT-SERVICE: 1'
            ]);

            curl_exec($ch);
            $curlInfo = curl_getinfo($ch);

            if ($curlInfo['http_code'] > 0) {
                $info['forwards'][] = $url;
                $info['response'] = $curlInfo;

                if (curl_errno($ch)) {
                    $info['error']['id'] = curl_errno($ch);
                    $info['error']['message'] = curl_error($ch);
                } else {
                    $info['total_time'] += $curlInfo['total_time'];
                    if ($curlInfo['http_code'] >= 300 && $curlInfo['http_code'] < 400 && isset($curlInfo['redirect_url'])) {
                        $this->curlUrl($curlInfo['redirect_url'], $info);
                    }
                }
            }
            curl_close($ch);
        }
    }

    /**
     * Query results by path and domain
     *
     * @param string $path
     * @param integer $domain
     * @param string $fields
     * @return array
     * @throws \TYPO3\CMS\Core\Error\Http\StatusException
     */
    public function queryByPathAndDomain($path, $domain = 0, $fields = 'uid, destination, http_response, domain')
    {
        $redirect = null;
        if (!empty($path)) {
            // Hook: beforeQueryByPathAndDomain
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['my_redirects']['beforeQueryByPathAndDomain'])) {
                foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['my_redirects']['beforeQueryByPathAndDomain'] as $key => $_funcRef) {
                    $_params = [
                        'redirect' => &$redirect,
                        'path' => &$path,
                        'domain' => &$domain,
                        'fields' => &$fields
                    ];
                    GeneralUtility::callUserFunction($_funcRef, $_params, $this);
                }
            }

            if ($redirect === null) {
                $path = $this->generateCleanPath($path);
                $redirects = $this->getMatchingService()->matchRedirects($path, $domain, $fields);
                if ($redirects) {
                    $redirect = reset($redirects);
                }
            }

            // Hook: afterQueryByPathAndDomain
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['my_redirects']['afterQueryByPathAndDomain'])) {
                foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['my_redirects']['afterQueryByPathAndDomain'] as $key => $_funcRef) {
                    $_params = [
                        'redirect' => &$redirect,
                        'path' => $path,
                        'domain' => $domain,
                        'fields' => $fields
                    ];
                    $redirect = GeneralUtility::callUserFunction($_funcRef, $_params, $this);
                }
            }
        }
        return $redirect;
    }

    /**
     * Handle redirect with core HTTP Response constants
     *
     * @param array $redirect
     * @return void
     */
    public function handleRedirect($redirect)
    {
        if ((bool)$_SERVER['HTTP_X_REDIRECT_SERVICE'] === false) {
            // Update statistics
            $updateFields = [
                'counter' => 'counter+1',
                'last_hit' => time(),
                'last_referrer' => GeneralUtility::getIndpEnv('HTTP_REFERER')
            ];
            // Remove empty values
            $updateFields = array_filter($updateFields);

            $this->getDatabaseConnection()->exec_UPDATEquery(
                Redirect::TABLE,
                'uid = ' . (int)$redirect['uid'],
                $updateFields,
                ['counter']
            );
        }

        $destination = $this->generateLink($redirect['destination']);

        if (!empty($this->keptQueryParameters)) {
            $urlParts = parse_url($destination);
            $urlParts['query'] .= '&' . http_build_query($this->keptQueryParameters);
            $urlParts['query'] = trim($urlParts['query'], '&');
            $destination = HttpUtility::buildUrl($urlParts);
        }

        header('X-Redirect-Handler: my_redirects:' . $redirect['uid']);

        // Get response code constant from core
        $constantLookUp = '\TYPO3\CMS\Core\Utility\HttpUtility::HTTP_STATUS_' . $redirect['http_response'];
        $httpStatus = (defined($constantLookUp) ? constant($constantLookUp) : HttpUtility::HTTP_STATUS_302);
        HttpUtility::redirect($destination, $httpStatus);
    }

    /**
     * Generate the Url Hash
     *
     * @param string $path
     * @return string
     * @throws \Exception
     */
    public function generateCleanPath($path)
    {
        if ($urlParts = parse_url($path)) {
            if (!empty($urlParts['path'])) {
                // Remove trailing slash from url generation
                $urlParts['path'] = rtrim($urlParts['path'], '/');
            }
            if (!empty($urlParts['query'])) {
                $excludedQueryParameters = $this->getCHashExcludedParameters();
                if (!empty($excludedQueryParameters)) {
                    parse_str($urlParts['query'], $queryParameters);
                    if (!empty($queryParameters)) {
                        foreach ($queryParameters as $key => $value) {
                            if (in_array($key, $excludedQueryParameters)) {
                                unset($queryParameters[$key]);
                                $this->keptQueryParameters[$key] = $value;
                            }
                        }

                        $urlParts['query'] = (!empty($queryParameters) ? http_build_query($queryParameters) : null);
                    }
                }
            }
            $url = HttpUtility::buildUrl($urlParts);
            // Make sure the hash is case-insensitive
            $url = strtolower($url);
            return $url;
        }

        throw new BadRequestException('Incorrect url given.', 1467622163);
    }

    /**
     * Generate link based on current page information
     *
     * @param string $target
     * @return string
     */
    protected function generateLink($target)
    {
        $link = null;

        if (MathUtility::canBeInterpretedAsInteger($target)) {
            $target = (int)$target;
            $controller = $this->getTypoScriptFrontendController($target);
            $page = BackendUtility::getRecord('pages', $target);
            $linkData = $controller->tmpl->linkData(
                $page,
                '',
                false,
                ''
            );
            if (!empty($linkData) && isset($linkData['totalURL'])) {
                $link = $linkData['totalURL'];
            }
        } elseif (GeneralUtility::isValidUrl($target) === false) {
            // Render it via the cObj with default rootpage id if available
            $defaultRootPageId = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT']['pagePath']['rootpage_id'] ?: 1;
            $controller = $this->getTypoScriptFrontendController($defaultRootPageId);

            $link = $controller->cObj->typoLink_URL(
                ['parameter' => $target]
            );
        } else {
            $link = $target;
        }

        return $link;
    }

    /**
     * Get configured excluded parameters to keep in redirect
     *
     * @return array
     */
    protected function getCHashExcludedParameters()
    {
        return GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters'], true);
    }

    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @param integer $pageId
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController($pageId = 0)
    {
        // Check if GLOBALS['TSFE'] is initiated correctly
        EidUtility::initializeTypoScriptFrontendController($pageId);
        return $GLOBALS['TSFE'];
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\MatchingService
     */
    protected function getMatchingService()
    {
        if (!isset($this->matchingService)) {
            $this->matchingService = $this->getObjectManager()->get(MatchingService::class);
        }
        return $this->matchingService;
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\DomainService
     */
    protected function getDomainService()
    {
        if (!isset($this->domainService)) {
            $this->domainService = $this->getObjectManager()->get(DomainService::class);
        }
        return $this->domainService;
    }

    /**
     * Get the SignalSlot dispatcher
     *
     * @return \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     */
    protected function getSignalSlotDispatcher()
    {
        return $this->getObjectManager()->get(Dispatcher::class);
    }

}
