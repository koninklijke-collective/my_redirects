<?php
namespace Serfhos\MyRedirects\Service;

use Serfhos\MyRedirects\Domain\Model\Redirect;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Service: Handle Redirects
 *
 * @package Serfhos\MyRedirects\Service
 */
class RedirectService implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * @var string
     */
    protected $redirectTable = 'tx_myredirects_domain_model_redirect';

    /**
     * @var array
     */
    protected $keptQueryParameters = array();

    /**
     * Do an active lookup for redirect
     *
     * @param \Serfhos\MyRedirects\Domain\Model\Redirect $redirect
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

            $details = array();
            $this->curlUrl($url, $details);

            if ((int) $details['response']['http_code'] !== 200) {
                $active = false;

                if ((int) $details['response']['http_code'] === 0) {
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
    protected function curlUrl($url, &$info = array())
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
            curl_setopt($ch, CURLOPT_USERAGENT, 'Serfhos.com: Redirect Lookup/1.0');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'X-REDIRECT-SERVICE: 1'
            ));

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
     */
    public function queryByPathAndDomain($path, $domain = 0, $fields = 'uid, destination, http_response, domain')
    {
        if (!empty($path)) {
            $hash = $this->generateUrlHash($path);
            return $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
                $fields,
                $this->redirectTable,
                'url_hash = "' . $hash . '"'
                . ' AND domain IN (0,' . $domain . ')',
                null,
                'domain DESC'
            );
        }

        return null;
    }

    /**
     * Handle redirect with core HTTP Response constants
     *
     * @param array $redirect
     * @return string
     */
    public function handleRedirect($redirect)
    {
        if ((bool) $_SERVER['HTTP_X_REDIRECT_SERVICE'] === false) {
            // Update statistics
            $updateFields = array(
                'counter' => 'counter+1',
                'last_hit' => time(),
                'last_referrer' => GeneralUtility::getIndpEnv('HTTP_REFERER')
            );
            // Remove empty values
            $updateFields = array_filter($updateFields);

            $this->getDatabaseConnection()->exec_UPDATEquery(
                $this->redirectTable,
                'uid = ' . (int) $redirect['uid'],
                $updateFields,
                array('counter')
            );
        }

        $destination = $redirect['destination'];
        if (MathUtility::canBeInterpretedAsInteger($destination)) {
            $destination = '/index.php?id=' . $destination;
            if (!empty($this->keptQueryParameters)) {
                $destination .= '&' . http_build_query($this->keptQueryParameters);
            }
        } elseif (!empty($this->keptQueryParameters)) {
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
     * @param string $url
     * @return string
     */
    public function generateUrlHash($url)
    {
        $urlParts = parse_url($url);
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
        return sha1($url);
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
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected function getObjectManager()
    {
        return GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
    }

    /**
     * @return \Serfhos\MyRedirects\Service\DomainService
     */
    protected function getDomainService()
    {
        if (!isset($this->domainService)) {
            $this->domainService = $this->getObjectManager()->get('Serfhos\\MyRedirects\\Service\\DomainService');
        }
        return $this->domainService;
    }
}