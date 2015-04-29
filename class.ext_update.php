<?php

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Update script
 *
 * @package Serfhos\MyRedirects
 */
class ext_update
{

    /**
     * @var \TYPO3\CMS\Core\Messaging\FlashMessageQueue
     * @inject
     */
    protected $flashMessageQueue;

    /**
     * @var string
     */
    protected $extensionKey = 'my_redirects';

    /**
     * Check if upgrade script is needed (this function is called from the extension manager)
     *
     * @return boolean
     */
    public function access()
    {
        return true;
    }

    /**
     * Run upgrade scripts (this function is called from the extension manager)
     *
     * @return string
     */
    public function main()
    {
        $updateScriptLink = BackendUtility::getModuleUrl('tools_ExtensionmanagerExtensionmanager',
            array(
                'tx_extensionmanager_tools_extensionmanagerextensionmanager' => array(
                    'extensionKey' => $this->extensionKey,
                    'action' => 'show',
                    'controller' => 'UpdateScript',
                ),
            ));
        $view = $this->getView();
        $view->assignMultiple(array(
            'formAction' => $updateScriptLink,
        ));

        if ((int) $_POST['convert-realurl'] === 1) {
            $this->migrateRedirectsFromRealUrl();
        }

        return $view->render();
    }

    /**
     * Migrate data from realurl to this extension
     *
     * @throws \TYPO3\CMS\Core\Exception
     * @return void
     */
    protected function migrateRedirectsFromRealUrl()
    {
        $migrated = 0;
        $targetTable = 'tx_myredirects_domain_model_redirect';

        $res = $this->getDatabaseConnection()->exec_SELECTquery(
            '*',
            'tx_realurl_redirects',
            ''
        );
        while ($row = $this->getDatabaseConnection()->sql_fetch_assoc($res)) {
            if ($this->getDatabaseConnection()->exec_SELECTcountRows('uid', $targetTable,
                    'url_hash = ' . (int) $row['url_hash'] . ' AND domain = ' . (int) $row['domain_limit']) == 0
            ) {
                if ((int) $row['url_hash'] > 0) {
                    $insertFields = array(
                        'pid' => 0,
                        'tstamp' => $row['tstamp'],
                        'crdate' => $row['tstamp'],
                        'cruser_id' => $this->getBackendUserAuthentication()->user['uid'],
                        'url_hash' => (int) $row['url_hash'],
                        'url' => $row['url'],
                        'destination' => $this->correctUrl($row['destination']),
                        'last_referrer' => $row['last_referer'],
                        'counter' => (int) $row['counter'],
                        'http_response' => ($row['has_moved'] ? 301 : 302),
                        'domain' => (int) $row['domain_limit'],
                    );

                    if ($this->getDatabaseConnection()->exec_INSERTquery($targetTable, $insertFields)) {
                        $migrated++;
                    }
                }
            }
        }

        if ($migrated > 0) {
            $message = $this->getObjectManager()->get(
                'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                'A total of ' . $migrated . ' records are migrated.',
                'RealURL Redirects migrated',
                FlashMessage::OK
            );
        } else {
            $message = $this->getObjectManager()->get(
                'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                'No records are migrated. Already done or none found.',
                'No RealURL Redirects migrated',
                FlashMessage::ERROR
            );
        }
        $this->getFlashMessageQueue()->enqueue($message);
    }

    /**
     * Correct url based on RealURL configuration
     * if defaultToHTMLsuffixOnPrev is not set, force the trailing / in url
     *
     * @param string $url
     * @return string
     */
    protected function correctUrl($url)
    {
        $urlParameters = parse_url($url);
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('realurl')) {
            if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']) && (int) $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT']['fileName']['defaultToHTMLsuffixOnPrev'] === 0) {
                if (substr($url, -1) !== '/') {
                    if (isset($urlParameters['path'])) {
                        $pathInfo = pathinfo($urlParameters['path']);
                        if (empty($pathInfo['extension'])) {
                            $urlParameters['path'] = rtrim($urlParameters['path'], '/') . '/';
                            $url = \TYPO3\CMS\Core\Utility\HttpUtility::buildUrl($urlParameters);
                        }
                    }
                }
            }
        }

        // Only look if query is configured and link is relative to the root
        if (isset($urlParameters['query']) && !isset($urlParameters['host'])) {
            $idOnlyRegEx = '/^id=[1-9][0-9]{0,15}$/i';
            if (preg_match($idOnlyRegEx, $urlParameters['query'])) {
                $pageId = (int) str_replace('id=', '', $urlParameters['query']);
                if ($pageId > 0) {
                    $url = $pageId;
                }
            }
        }

        return $url;
    }

    /**
     * Get the ObjectManager
     *
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected function getObjectManager()
    {
        return GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
    }

    /**
     * Get the DatabaseConnection
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * Get the BackendAuthentication
     *
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUserAuthentication()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @return \TYPO3\CMS\Fluid\View\StandaloneView
     */
    protected function getView()
    {
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
        $view = $this->getObjectManager()->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:' . $this->extensionKey . '/Resources/Private/Templates/UpdateScript/Index.html'));
        $view->setLayoutRootPath(GeneralUtility::getFileAbsFileName('EXT:' . $this->extensionKey . '/Resources/Private/Layouts'));
        $view->setPartialRootPath(GeneralUtility::getFileAbsFileName('EXT:' . $this->extensionKey . '/Resources/Private/Partials'));
        return $view;
    }

    /**
     * @return \TYPO3\CMS\Core\Messaging\FlashMessageQueue
     */
    protected function getFlashMessageQueue()
    {
        if (!isset($this->flashMessageQueue)) {
            /** @var \TYPO3\CMS\Core\Messaging\FlashMessageService $flashMessageService */
            $flashMessageService = $this->getObjectManager()->get('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService');
            $this->flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        }
        return $this->flashMessageQueue;
    }
}