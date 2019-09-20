<?php

namespace KoninklijkeCollective\MyRedirects\Functions;

use KoninklijkeCollective\MyRedirects\Utility\ConfigurationUtility;

/**
 * Trait: Translate
 */
trait TranslateTrait
{

    /**
     * Translate key for local extension
     *
     * @param string $key
     * @param array $arguments
     * @return string
     */
    protected static function translate($key, $arguments = [])
    {
        $text = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($key, ConfigurationUtility::EXTENSION, $arguments);
        return $text ? $text : $key;
    }
}
