<?php
namespace KoninklijkeCollective\MyRedirects\Service;

use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;
use TYPO3\CMS\Extbase\Object\InvalidObjectConfigurationException;
use TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility;

/**
 * Service: Redirect Matching
 *
 * @package KoninklijkeCollective\MyRedirects\Service
 */
class MatchingService implements \TYPO3\CMS\Core\SingletonInterface
{
    const MATCHING_MODE_HASH = 0;
    const MATCHING_MODE_WILDCARD = 1;
    const MATCHING_MODE_REGEXP = 2;

    /**
     * @var integer
     */
    protected $configuredMatchingMode;

    /**
     * @param string $path
     * @param integer $domain
     * @param string $fields
     * @return array
     * @throws \TYPO3\CMS\Core\Error\Http\StatusException
     */
    public function matchRedirects($path, $domain = 0, $fields = 'uid, url, destination, http_response, domain')
    {
        $redirects = null;
        if (!empty($path)) {
            $this->getDatabaseConnection()->store_lastBuiltQuery = 1;
            switch ($this->getConfiguredMatchingMode()) {
                case self::MATCHING_MODE_HASH:
                    $redirects = $this->getDatabaseConnection()->exec_SELECTgetRows(
                        $fields,
                        Redirect::TABLE,
                        'url_hash = "' . $this->generateUrlHash($path) . '"'
                        . ' AND domain IN (0,' . $domain . ')',
                        null,
                        'domain DESC'
                    );
                    break;

                case self::MATCHING_MODE_WILDCARD:
                    $redirects = $this->getDatabaseConnection()->exec_SELECTgetRows(
                        $fields . ', ROUND(CHAR_LENGTH(url) - CHAR_LENGTH(REPLACE(url, "*", ""))) AS wildcards, CHAR_LENGTH(url) AS length',
                        Redirect::TABLE,
                        '"' . $path . '" LIKE REPLACE(url, "*", "%")'
                        . ' AND domain IN (0,' . $domain . ')',
                        null,
                        'wildcards, length DESC, domain DESC'
                    );
                    break;

                case self::MATCHING_MODE_REGEXP:
                    $redirects = $this->getDatabaseConnection()->exec_SELECTgetRows(
                        $fields . ', CHAR_LENGTH(url) AS length',
                        Redirect::TABLE,
                        '"' . $path . '" REGEXP url'
                        . ' AND domain IN (0,' . $domain . ')',
                        null,
                        'length DESC, domain DESC'
                    );
                    break;


                default:
                    throw new \TYPO3\CMS\Core\Error\Http\StatusException(
                        \TYPO3\CMS\Core\Utility\HttpUtility::HTTP_STATUS_500,
                        'Unknown redirect matching mode.',
                        1480136597
                    );
            }
        }
        return $redirects;
    }

    /**
     * Get all collisions for the given redirect
     *
     * @param array $redirect
     * @return array
     */
    public function getCollisions($redirect)
    {
        switch ($matchingMode = $this->getConfiguredMatchingMode()) {
            case self::MATCHING_MODE_WILDCARD:
            case self::MATCHING_MODE_REGEXP:
                $collisions = [];
                $redirects = $this->getDatabaseConnection()->exec_SELECTgetRows(
                    '*',
                    Redirect::TABLE,
                    'uid <> "' . $redirect['uid'] . '"'
                    . ($matchingMode === self::MATCHING_MODE_HASH ? ' AND url = "' . $redirect['url'] . '"' : '')
                    . ' AND domain IN (0,' . $redirect['domain'] . ')',
                    null,
                    'domain DESC'
                );

                if ($matchingMode === self::MATCHING_MODE_HASH) {
                    $collisions = $redirects;
                } else {
                    $lexer = new \FormalTheory\RegularExpression\Lexer();

                    $a = $lexer->lex($this->urlToRegex($redirect['url'], $matchingMode))->getNFA();

                    foreach ($redirects as $redirect) {
                        $b = $lexer->lex($this->urlToRegex($redirect['url'], $matchingMode))->getNFA();

                        if (\FormalTheory\FiniteAutomata::intersectionByDeMorgan($a, $b)->validSolutionExists()) {
                            $collisions[] = $redirect;
                        }
                    }
                }

                return $collisions;
        }
        return null;

    }


    /**
     * Convert an URL to a regular expression
     *
     * @param string $url
     * @param int $matchingMode
     * @return string
     */
    protected function urlToRegex($url, $matchingMode)
    {
        $url = '^' . rtrim(ltrim(trim($url), '^'), '$') . '$';
        if ((int)$matchingMode === self::MATCHING_MODE_WILDCARD) {
            $url = str_replace('*', '.*', $url);
        }
        return $url;
    }

    /**
     * Generate the Url Hash
     *
     * @param string $url
     * @return string
     * @throws \Exception
     */
    public function generateUrlHash($url)
    {
        if ($urlParts = parse_url($url)) {
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
                            }
                        }

                        $urlParts['query'] = (!empty($queryParameters) ? http_build_query($queryParameters) : null);
                    }
                }
            }
            $url = \TYPO3\CMS\Core\Utility\HttpUtility::buildUrl($urlParts);
            // Make sure the hash is case-insensitive
            $url = strtolower($url);
            return sha1($url);
        }

        throw new \TYPO3\CMS\Core\Error\Http\BadRequestException('Incorrect url given.', 1467622163);
    }

    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @return integer
     * @throws \TYPO3\CMS\Extbase\Object\InvalidObjectConfigurationException
     */
    public function getConfiguredMatchingMode()
    {
        if ($this->configuredMatchingMode === null) {
            $configuration = $this->getObjectManager()->get(ConfigurationUtility::class)
                ->getCurrentConfiguration('my_redirects');
            $this->configuredMatchingMode = (int)$configuration['matching_mode']['value'];

            if ($this->configuredMatchingMode !== self::MATCHING_MODE_HASH && (!class_exists('FormalTheory\RegularExpression\Lexer'))) {
                throw new InvalidObjectConfigurationException(
                    'The library "witrin/FormalTheory" is required when using different matching modes',
                    1484249470
                );
            }
        }
        return $this->configuredMatchingMode;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected function getObjectManager()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
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
}
