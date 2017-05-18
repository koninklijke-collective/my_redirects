<?php

namespace KoninklijkeCollective\MyRedirects\Utility;

use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageGenerator;
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

            // @TODO: deprecated workaround since 8/9
            if (method_exists(\TYPO3\CMS\Core\Core\Bootstrap::getInstance(), 'loadExtensionTables')) {
                \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->loadExtensionTables();
            } elseif (method_exists(\TYPO3\CMS\Core\Core\Bootstrap::getInstance(), 'loadCachedTca')) {
                \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->loadCachedTca();
            }
        }

        if (!($controller->fe_user instanceof FrontendUserAuthentication)) {
            $controller->initFEuser();
        }

        if (!($controller->sys_page instanceof PageRepository)) {
            $controller->determineId();
        }

        if (!($controller->tmpl instanceof TemplateService)) {
            $controller->initTemplate();
        }

        $controller->getConfigArray();

        if (!($controller->cObj instanceof ContentObjectRenderer)) {
            $controller->newCObj();
        }

        if (empty($controller->indexedDocTitle)) {
            PageGenerator::pagegenInit();
        }
    }

}
