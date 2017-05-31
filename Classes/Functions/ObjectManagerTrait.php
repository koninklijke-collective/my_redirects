<?php

namespace KoninklijkeCollective\MyRedirects\Functions;

/**
 * Trait: ObjectManager
 *
 * @package KoninklijkeCollective\MyRedirects\Functions
 */
trait ObjectManagerTrait
{

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected static function getObjectManager()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
    }

}
