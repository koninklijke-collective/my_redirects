<?php

use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Update script
 *
 * @package KoninklijkeCollective\MyRedirects
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
        return $this->getBackendUserAuthentication()->isAdmin();
    }

    /**
     * Run upgrade scripts (this function is called from the extension manager)
     *
     * @return string
     */
    public function main()
    {
        $updateScriptLink = BackendUtility::getModuleUrl('tools_ExtensionmanagerExtensionmanager',
            [
                'tx_extensionmanager_tools_extensionmanagerextensionmanager' => [
                    'extensionKey' => $this->extensionKey,
                    'action' => 'show',
                    'controller' => 'UpdateScript',
                ],
            ]);
        $view = $this->getView();
        $view->assignMultiple([
            'formAction' => $updateScriptLink,
        ]);

        if ((int) $_POST['calculate-hash'] === 1) {
            $this->calculateHashes();
        }

        if ((int) $_POST['convert-realurl'] === 1) {
            $this->migrateRedirectsFromRealUrl();
        }

        return $view->render();
    }

    /**
     * Calculate hashes based on given url
     *
     * @return void
     */
    protected function calculateHashes()
    {
        $hashed = 0;
        $res = $this->getDatabaseConnection()->exec_SELECTquery(
            'uid, url, url_hash',
            Redirect::TABLE,
            ''
        );
        while ($row = $this->getDatabaseConnection()->sql_fetch_assoc($res)) {
            $newHash = $this->generateNewHash($row['url']);
            if ($newHash !== null) {
                if ($row['url_hash'] != $newHash) {
                    $updateFields = [
                        'tstamp' => time(),
                        'url_hash' => $newHash,
                    ];

                    $this->getDatabaseConnection()->exec_UPDATEquery(Redirect::TABLE, 'uid = ' . (int) $row['uid'], $updateFields);
                    $hashed++;
                }
            }
        }

        if ($hashed > 0) {
            $message = $this->getObjectManager()->get(
                'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                'A total of ' . $hashed . ' hashes are re-calculated.',
                'RealURL Redirect hashes calculated',
                FlashMessage::OK
            );
        } else {
            $message = $this->getObjectManager()->get(
                'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                'No hashes are re-calculated. None found or none needed updating.',
                'No Redirect hashes calculated',
                FlashMessage::ERROR
            );
        }
        $this->getFlashMessageQueue()->enqueue($message);
    }

    /**
     * Migrate data from realurl to this extension
     *
     * @return void
     */
    protected function migrateRedirectsFromRealUrl()
    {
        $migrated = 0;

        $res = $this->getDatabaseConnection()->exec_SELECTquery(
            '*',
            'tx_realurl_redirects',
            ''
        );
        while ($row = $this->getDatabaseConnection()->sql_fetch_assoc($res)) {
            if ($this->getDatabaseConnection()->exec_SELECTcountRows('uid', Redirect::TABLE,
                    'url_hash = ' . (int) $row['url_hash'] . ' AND domain = ' . (int) $row['domain_limit']) == 0
            ) {
                $newHash = $this->generateNewHash($row['url']);
                if ((int) $row['url_hash'] > 0 && $newHash !== null) {
                    $insertFields = [
                        'pid' => 0,
                        'tstamp' => (int) $row['tstamp'],
                        'crdate' => (int) $row['tstamp'],
                        'cruser_id' => (string) $this->getBackendUserAuthentication()->user['uid'],
                        'url_hash' => $newHash,
                        'url' => (string) $row['url'],
                        'destination' => (string) $this->correctUrl($row['destination']),
                        'last_referrer' => (string) $row['last_referer'],
                        'counter' => (int) $row['counter'],
                        'http_response' => ($row['has_moved'] ? 301 : 302),
                        'domain' => (int) $row['domain_limit'],
                    ];

                    if ($this->getDatabaseConnection()->exec_INSERTquery(Redirect::TABLE, $insertFields)) {
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
            } elseif (class_exists('TYPO3\CMS\Core\Utility\MathUtility')
                && method_exists('TYPO3\CMS\Core\Utility\MathUtility', 'canBeInterpretedAsInteger')
            ) {
                if (\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($urlParameters['query'])) {
                    $url = $urlParameters['query'];
                }
            }
        }

        return $url;
    }

    /**
     * Generate hash with exception catch
     *
     * @param string $url
     * @return string
     */
    protected function generateNewHash($url)
    {
        try {
            return (string) $this->getRedirectService()->generateUrlHash($url);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected function getObjectManager()
    {
        return GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
    }

    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\RedirectService
     */
    protected function getRedirectService()
    {
        return $this->getObjectManager()->get('KoninklijkeCollective\\MyRedirects\\Service\\RedirectService');
    }

    /**
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
        $view->setLayoutRootPaths([GeneralUtility::getFileAbsFileName('EXT:' . $this->extensionKey . '/Resources/Private/Layouts')]);
        $view->setPartialRootPaths([GeneralUtility::getFileAbsFileName('EXT:' . $this->extensionKey . '/Resources/Private/Partials')]);
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
            $this->flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier('myredirects.errors');
        }
        return $this->flashMessageQueue;
    }

}
