<?php

namespace KoninklijkeCollective\MyRedirects\Utility;

/**
 * Utility: Extension Configuration
 *
 * @package KoninklijkeCollective\MyRedirects\Utility
 */
class ConfigurationUtility
{
    const EXTENSION = 'my_redirects';

    /**
     * @return integer
     */
    public static function getDefaultRootPageId()
    {
        $configuration = static::getConfiguration();
        return (int)($configuration['defaultRootPageId'] ?: ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT']['pagePath']['rootpage_id'] ?: 1));
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
                $configuration = (array)unserialize($data);
            } else {
                $configuration = $data;
            }
        }

        return $configuration;
    }
}
