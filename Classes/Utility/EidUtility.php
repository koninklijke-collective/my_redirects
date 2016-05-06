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

        // fallback for timetracker
        if (!is_object($GLOBALS['TT'])) {
            $GLOBALS['TT'] = new \TYPO3\CMS\Core\TimeTracker\NullTimeTracker();
        }

        $controller = &$GLOBALS['TSFE'];

        if (!($controller instanceof TypoScriptFrontendController)) {
            $controller = GeneralUtility::makeInstance(
                'TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController',
                $TYPO3_CONF_VARS,
                $pageId,
                0
            );
            \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->loadCachedTca();
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

        if (!is_array($controller->config)) {
            $controller->getConfigArray();
        }

        if (!($controller->cObj instanceof ContentObjectRenderer)) {
            $controller->newCObj();
        }

        if (empty($controller->indexedDocTitle)) {
            PageGenerator::pagegenInit();
        }
    }
}