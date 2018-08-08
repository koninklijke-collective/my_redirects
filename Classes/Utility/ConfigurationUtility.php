<?php

namespace KoninklijkeCollective\MyRedirects\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility: Extension Configuration
 *
 * @package KoninklijkeCollective\MyRedirects\Utility
 */
class ConfigurationUtility
{

    /**
     * Global approach for $_EXTKEY
     */
    const EXTENSION = 'my_redirects';

    /**
     * Default Queue identifier by extbase controllers
     * Not always known by other services
     */
    const FLASH_MESSAGE_QUEUE_IDENTIFIER = 'extbase.flashmessages.tx_myredirects_web_myredirectsmyredirects';

    /**
     * Get default root page id (from link or configuration)
     *
     * @param string $link
     * @return int
     */
    public static function getDefaultRootPageId($link)
    {
        if (stripos($link, 't3://page') === 0) {
            // lets parse the urn
            $url = parse_url($link);

            if (isset($url['query'])) {
                parse_str(htmlspecialchars_decode($url['query']), $data);
            } else {
                $data = [];
            }
            if (isset($data['uid'])) {
                return (int)$data['uid'];
            }
        }
        // Fallback on default configuration
        $configuration = static::getConfiguration();
        return (int)($configuration['defaultRootPageId'] ?: ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT']['pagePath']['rootpage_id'] ?: 1));
    }

    /**
     * Get configured excluded parameters to keep in redirect
     *
     * @return array
     */
    public static function getCHashExcludedParameters()
    {
        return GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['FE']['cHashExcludedParameters'], true);
    }

    /**
     * Get Global Configuration from:
     * $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extension_key']
     * - fallback on -
     * $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['extension_key']
     *
     * @return array
     */
    public static function getConfiguration()
    {
        static $configuration;
        if ($configuration === null) {
            $data = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][static::EXTENSION] ?: $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][static::EXTENSION];
            if (!is_array($data)) {
                $configuration = (array)unserialize($data, false);
            } else {
                $configuration = $data;
            }
        }

        return $configuration;
    }
}
