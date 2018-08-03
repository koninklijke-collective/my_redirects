<?php

namespace KoninklijkeCollective\MyRedirects\Utility;

use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Utility: Initialize objects inside EID usage
 *
 * @package KoninklijkeCollective\MyRedirects\Utility
 */
class EidUtility
{

    /**
     * Initialize TSFE based on given page id
     *
     * @param integer $pageId
     * @return void
     * @throws \Exception
     */
    public static function initializeTypoScriptFrontendController($pageId = 0)
    {
        global $TYPO3_CONF_VARS;

        $controller = &$GLOBALS['TSFE'];

        if (!($controller instanceof TypoScriptFrontendController)) {
            $controller = GeneralUtility::makeInstance(
                TypoScriptFrontendController::class,
                $TYPO3_CONF_VARS,
                $pageId,
                0
            );

            static::invokeMethod(\TYPO3\CMS\Core\Core\Bootstrap::getInstance(), 'loadBaseTca');
            static::invokeMethod(\TYPO3\CMS\Core\Core\Bootstrap::getInstance(), 'loadExtTables');
        }

        if (!($controller->fe_user instanceof FrontendUserAuthentication)) {
            static::invokeMethod($controller, 'initFEuser');
        }

        if (!($controller->sys_page instanceof PageRepository)) {
            static::invokeMethod($controller, 'determineId');
        }

        if (!($controller->tmpl instanceof TemplateService)) {
            static::invokeMethod($controller, 'initTemplate');
            static::invokeMethod($controller, 'getFromCache');
            static::invokeMethod($controller, 'getConfigArray');
        }

        if (empty($controller->rootLine)) {
            $controller->rootLine = $controller->sys_page->getRootLine($controller->id, $controller->MP);
        }

        if (empty($controller->page)) {
            $controller->page = $controller->sys_page->getPage($controller->id);
        }

        if (!($controller->cObj instanceof ContentObjectRenderer)) {
            $controller->newCObj();
        }
    }

    /**
     * @param object $object
     * @param string $method
     * @return void
     */
    protected static function invokeMethod($object, $method)
    {
        try {
            if (is_callable([$object, $method])) {
                $object->{$method}();
            }
        } catch (\Exception $e) {
        }
    }
}
