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
    const EXTENSION = 'my_redirects';

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
                $configuration = (array)unserialize($data);
            } else {
                $configuration = $data;
            }
        }

        return $configuration;
    }
}
