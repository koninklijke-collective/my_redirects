<?php

namespace KoninklijkeCollective\MyRedirects\Service;

use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service: Status of all redirects functionality
 *
 * @package KoninklijkeCollective\MyRedirects\Service
 */
class StatusService
{

    use \KoninklijkeCollective\MyRedirects\Functions\ObjectManagerTrait;

    /**
     * Do an active lookup for redirect
     *
     * @param Redirect $redirect
     * @param boolean $safeCheck
     * @return array
     */
    public function activeLookup(Redirect $redirect, $safeCheck = true)
    {
        $url = $redirect->getUrl();

        if (!empty($url)) {
            if ($domain = $this->getDomainService()->getDomainNameByRedirect($redirect)) {
                $url = rtrim($domain, '/') . '/' . $url;
                $urlDetails = parse_url($url);
                if (!isset($urlDetails['scheme'])) {
                    $url = (GeneralUtility::getIndpEnv('TYPO3_SSL') ? 'https://' : 'http://') . $url;
                }

                $details = [
                    'starting_uri' => $url,
                ];
                $this->getResponse($url, $details, ($safeCheck ? null : $redirect));

                // Validate redirect data based on response info
                $active = true;
                $inactiveReason = '';
                if ((int)$details['response']['http_code'] !== 200) {
                    $active = false;

                    if ((int)$details['response']['http_code'] === 0) {
                        $inactiveReason = 'Response timeout';
                    } elseif (isset($details['error']['id'])) {
                        $inactiveReason = $details['error']['id'] . ': ' . $details['error']['message'];
                    } else {
                        $inactiveReason = 'Unknown: ' . ArrayUtility::arrayExport($details);
                    }
                } elseif ($details['response']['url'] == $url) {
                    $active = false;
                    $inactiveReason = 'Redirect got stuck, could be timeout';
                }

                $redirect
                    ->setLastChecked(new \DateTime())
                    ->setActive($active)
                    ->setInactiveReason($inactiveReason);

                return $details;
            }
        } else {
            $redirect->setActive(false);
        }
        return [];
    }

    /**
     * Get complete report from curled url
     *
     * @param string $url
     * @param array $info
     * @param Redirect $redirect
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getResponse($url, &$info = [], $redirect = null)
    {
        // Use cURL for: http, https, ftp, ftps, sftp and scp
        if (preg_match('/^(?:http|ftp)s?|s(?:ftp|cp):/', $url)) {
            try {
                if (empty($info)) {
                    $info['request_started'] = microtime();
                    $info['request_ended'] = null;
                    $info['total_time'] = null;
                }

                $response = $this->getRequestFactory()->request($url, 'GET', [
                    'allow_redirects' => false,
                    'connect_timeout' => 10,
                    'headers' => [
                        // Some sites need a user-agent
                        'User-Agent' => 'my_redirects: Redirect Lookup/1.0',
                        'X-REDIRECT-SERVICE' => 1,
                    ],
                    'on_stats' => function (\GuzzleHttp\TransferStats $stats) use (&$info) {
                        $info['response']['url'] = (string)$stats->getEffectiveUri();
                        $info['response']['request_time'] = $stats->getTransferTime();
                        $info['response']['statistics'] = $stats->getHandlerStats();

                        // You must check if a response was received before using the response object.
                        if ($stats->hasResponse() && is_callable([$stats->getResponse(), 'getStatusCode'])) {
                            $info['response']['http_code'] = $stats->getResponse()->getStatusCode();
                        } else {
                            $info['error']['data'] = $stats->getHandlerErrorData();
                        }
                    }
                ]);
                $error = false;
                $info['forwards'][] = $url;

                if ($redirect && $response->hasHeader('X-Redirect-Handler')) {
                    list($extension, $id) = GeneralUtility::trimExplode(
                        ':',
                        reset($response->getHeader('X-Redirect-Handler'))
                    );
                    if ($redirect->getUid() !== (int)$id) {
                        $error = true;
                        $info['error']['id'] = 1495895151978;
                        $info['error']['message'] = 'This redirect was handled by another redirect (#' . $id . ')';
                    }
                }

                if ($error === false) {
                    $nextTarget = reset($response->getHeader('Location'));
                    if ($response->getStatusCode() >= 300 && $response->getStatusCode() < 400 && !empty($nextTarget)) {
                        $response = $this->getResponse($nextTarget, $info, $redirect);
                    } else {
                        $info['request_ended'] = microtime();
                        $info['total_time'] = $info['request_started'] - $info['request_ended'];
                    }
                }
                return $response;
            } catch (\Exception $e) {
                if ($e instanceof \GuzzleHttp\Exception\ClientException) {
                    $info['error']['id'] = $e->getResponse()->getReasonPhrase();
                    $info['error']['message'] = '<pre><code>' . htmlspecialchars($e->getResponse()->getBody()) . '</code></pre>';
                } else {
                    // Fallback on default throws..
                    $info['error']['id'] = $e->getCode();
                    $info['error']['message'] = $e->getMessage();
                }
            }
        }
        return null;
    }

    /**
     * @return DomainService|object
     */
    protected function getDomainService()
    {
        return $this->getObjectManager()->get(DomainService::class);
    }

    /**
     * @return \TYPO3\CMS\Core\Http\RequestFactory|object
     */
    protected function getRequestFactory()
    {
        return $this->getObjectManager()->get(\TYPO3\CMS\Core\Http\RequestFactory::class);
    }
}
