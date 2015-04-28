<?php
namespace Serfhos\MyRedirects\Service;

use Serfhos\MyRedirects\Domain\Model\Redirect;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;

class RedirectService implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * @var string
     */
    protected $redirectTable = 'tx_myredirects_domain_model_redirect';

    /**
     * Obtains current domain id from sys_domain.
     *
     * @return integer
     */
    public function getCurrentDomainId()
    {
        $row = $this->getDatabaseConnection()->exec_SELECTgetSingleRow('uid',
            'sys_domain',
            'domainName=' . $this->getDatabaseConnection()->fullQuoteStr(GeneralUtility::getIndpEnv('HTTP_HOST'),
                'sys_domain') .
            ' AND redirectTo=\'\''
        );
        $result = (is_array($row) ? (int) $row['uid'] : 0);

        return $result;
    }

    /**
     * Do an active lookup for redirect
     *
     * @param \Serfhos\MyRedirects\Domain\Model\Redirect $redirect
     * @param string $defaultDomain
     * @return void
     */
    public function activeLookup(Redirect &$redirect, $defaultDomain = '')
    {
        $active = true;
        $url = $redirect->getUrl();
        if (!empty($url)) {
            if ($redirect->getUrlDomain() == '/') {
                $url = rtrim($defaultDomain, '/') . '/' . $url;
            } else {
                $url = $redirect->getUrlDomain() . $url;
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
            }

            if ($details['response']['url'] == $url) {
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
        // use cURL for: http, https, ftp, ftps, sftp and scp
        if (preg_match('/^(?:http|ftp)s?|s(?:ftp|cp):/', $url)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_NOBODY, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            // some sites need a user-agent
            curl_setopt($ch, CURLOPT_USERAGENT, 'Serfhos.com: Redirect Lookup/1.0');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            curl_exec($ch);
            $curlInfo = curl_getinfo($ch);
            curl_close($ch);

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
            $hash = GeneralUtility::md5int($path);
            return $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
                $fields,
                $this->redirectTable,
                'url_hash = "' . $hash . '"'
                . ' AND domain = ' . $domain
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
        // Update statistics
        $this->getDatabaseConnection()->exec_UPDATEquery(
            $this->redirectTable,
            'uid = ' . $redirect['uid'],
            array(
                'counter' => 'counter+1',
                'last_hit' => time(),
                'last_referrer' => GeneralUtility::getIndpEnv('HTTP_REFERER')
            )
        );

        // Get response code constant from core
        $constantLookUp = '\TYPO3\CMS\Core\Utility\HttpUtility::HTTP_STATUS_' . $redirect['http_response'];
        $httpStatus = (defined($constantLookUp) ? constant($constantLookUp) : HttpUtility::HTTP_STATUS_302);
        HttpUtility::redirect($redirect['destination'], $httpStatus);
    }

    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}