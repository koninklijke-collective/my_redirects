<?php
namespace KoninklijkeCollective\MyRedirects\Controller;

use KoninklijkeCollective\MyRedirects\Backend\BackendSession;
use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Backend Module Controller: Redirects
 *
 * @package KoninklijkeCollective\MyRedirects\Controller
 */
class RedirectController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * @var \TYPO3\CMS\Backend\View\BackendTemplateView
     */
    protected $view;

    /**
     * Backend Template Container
     *
     * @var string
     */
    protected $defaultViewObjectName = \TYPO3\CMS\Backend\View\BackendTemplateView::class;

    /**
     * Page information from given access
     *
     * @var array
     */
    protected $page = [];

    /**
     * @var \KoninklijkeCollective\MyRedirects\Domain\Repository\RedirectRepository
     */
    protected $redirectRepository;

    /**
     * @var \KoninklijkeCollective\MyRedirects\Service\RedirectService
     */
    protected $redirectService;

    /**
     * @var BackendSession
     */
    protected $backendSession;

    /**
     * @var \TYPO3\CMS\Core\Messaging\FlashMessageQueue
     */
    protected $flashMessageQueue;

    /**
     * Redirect request from post when forced
     *
     * @param \TYPO3\CMS\Extbase\Mvc\RequestInterface $request The request object
     * @param \TYPO3\CMS\Extbase\Mvc\ResponseInterface $response The response, modified by this handler
     * @return void
     */
    public function processRequest(
        \TYPO3\CMS\Extbase\Mvc\RequestInterface $request,
        \TYPO3\CMS\Extbase\Mvc\ResponseInterface $response
    ) {
        parent::processRequest($request, $response);

        if ($request instanceof \TYPO3\CMS\Extbase\Mvc\Web\Request) {
            $arguments = $request->getArguments();
            if (isset($arguments['forceRedirect']) && (bool) $arguments['forceRedirect'] === true) {
                unset ($arguments['forceRedirect'], $arguments['controller'], $arguments['action']);

                // Remove empty arguments
                $arguments = array_filter($arguments);
                $this->redirect($request->getControllerActionName(), null, null, $arguments);
            }
        }
    }

    /**
     * Initialize parameters for BackendTemplateView
     *
     * @param \TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view
     * @return void
     */
    protected function initializeView(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view)
    {
        parent::initializeView($view);
        if ($view instanceof \TYPO3\CMS\Backend\View\BackendTemplateView) {
            $this->registerDocheaderButtons();
            $view->getModuleTemplate()->setFlashMessageQueue($this->controllerContext->getFlashMessageQueue());
            $view->getModuleTemplate()->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Backend/Modal');
        }

        $currentUrl = $this->uriBuilder->setAddQueryString(true)->setArgumentsToBeExcludedFromQueryString(['returnUrl'])->buildBackendUri();

        $this->view->assignMultiple([
            'moduleUrl' => \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl('web_MyRedirectsMyRedirects'),
            'currentUrl' => $currentUrl
        ]);
    }

    /**
     * Stupid docheader buttons without fluid rendering -_-
     *
     * @return void
     */
    protected function registerDocheaderButtons()
    {
        $buttonBar = $this->view->getModuleTemplate()->getDocHeaderComponent()->getButtonBar();
        $currentRequest = $this->request;
        $moduleName = $currentRequest->getPluginName();
        $getVars = $this->request->getArguments();

        $extensionName = $currentRequest->getControllerExtensionName();
        if (count($getVars) === 0) {
            $modulePrefix = strtolower('tx_' . $extensionName . '_' . $moduleName);
            $getVars = ['id', 'M', $modulePrefix];
        }

        $returnUrl = rawurlencode(BackendUtility::getModuleUrl('web_MyRedirectsMyRedirects'));
        $parameters = GeneralUtility::explodeUrl2Array('edit[tx_myredirects_domain_model_redirect][0]=new&returnUrl=' . $returnUrl);
        $addUserLink = BackendUtility::getModuleUrl('record_edit', $parameters);

        $title = $this->translate('controller.action.add.record');
        $icon = $this->view->getModuleTemplate()->getIconFactory()->getIcon('actions-document-new', \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL);
        $addUserButton = $buttonBar->makeLinkButton()
            ->setHref($addUserLink)
            ->setTitle($title)
            ->setIcon($icon);
        $buttonBar->addButton($addUserButton, \TYPO3\CMS\Backend\Template\Components\ButtonBar::BUTTON_POSITION_LEFT);

        if (!empty($this->page)) {
            $shortcutName = $this->translate('shortcut.page.active', [$this->page['title'], $this->page['uid']]);
        } else {
            $shortcutName = $this->translate('shortcut.default');
        }
        $shortcutButton = $buttonBar->makeShortcutButton()
            ->setModuleName($moduleName)
            ->setDisplayName($shortcutName)
            ->setGetVariables($getVars);
        $buttonBar->addButton($shortcutButton);
    }

    /**
     * Initializes the action
     *
     * @return void
     */
    protected function initializeAction()
    {
        parent::initializeAction();

        $this->getBackendSession()->createSession(BackendSession::SESSION_KEY);

        // Configure page array when page is configured
        $pageId = (int) GeneralUtility::_GP('id');
        if ($pageId > 0) {
            $pagePerms = $this->getBackendUserAuthentication()->getPagePermsClause(1);
            $page = BackendUtility::readPageAccess($pageId, $pagePerms);
            if (is_array($page)) {
                $this->page = $page;
            }
        }

        if (!isset($this->settings['staticTemplate'])) {
            $this->controllerContext = $this->buildControllerContext();
            $this->enqueueFlashMessage(
                $this->translate('controller.initialize.error.no_typoscript.description'),
                $this->translate('controller.initialize.error.no_typoscript.title'),
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
        } else {
            $filters = $this->getBackendSession()->getSessionContents(BackendSession::SESSION_KEY);
            if ($filters === false) {
                $filters = [
                    'filter' => [],
                    'order' => 'url',
                    'direction' => QueryInterface::ORDER_ASCENDING
                ];
            }
            if ($this->request->hasArgument('filter')) {
                $filter = $this->request->getArgument('filter');
                if (is_array($filter)) {
                    $filters['filter'] = $this->request->getArgument('filter');
                } else {
                    $filters['filter'] = [];
                }
            }
            if ($this->request->hasArgument('order')) {
                $filters['order'] = $this->request->getArgument('order');
            }
            if ($this->request->hasArgument('direction')) {
                $filters['direction'] = $this->request->getArgument('direction');
            }

            $this->getBackendSession()->saveSessionContents($filters);
        }
    }

    /**
     * Action: List redirects
     *
     * @return void
     */
    public function listAction()
    {
        $arguments = $this->getBackendSession()->getSessionContents(BackendSession::SESSION_KEY);
        $filter = (array) $arguments['filter'];
        $noPageUri = null;
        if (!empty($this->page)) {
            $filter['page'] = $this->page['uid'];
            $noPageUri = $this->uriBuilder->reset()->setAddQueryString(true)->setArgumentsToBeExcludedFromQueryString(['id'])->build();
        }

        $this->view->assignMultiple([
            'noPageUri' => $noPageUri,
            'page' => $this->page,
            'filter' => (array) $arguments['filter'],
            'order' => $arguments['order'],
            'direction' => $arguments['direction'],
            'redirects' => $this->getRedirectRepository()->findByOrder(
                $filter,
                $arguments['order'],
                $arguments['direction']
            ),
        ]);
    }

    /**
     * Action: Check if redirect is still active and works as intended
     *
     * @param \KoninklijkeCollective\MyRedirects\Domain\Model\Redirect $redirect
     * @param string $returnUrl
     * @return void
     */
    public function lookupAction($redirect = null, $returnUrl = '')
    {
        if ($redirect instanceof Redirect) {
            $this->getRedirectService()->activeLookup($redirect);
            $this->getRedirectRepository()->update($redirect);

            $this->enqueueFlashMessage(
                $this->translate('controller.action.success.lookup.description',
                    ['/' . $redirect->getUrl()]),
                $this->translate('controller.action.success.lookup.title'),
                \TYPO3\CMS\Core\Messaging\AbstractMessage::INFO
            );
        }

        if (!empty($returnUrl)) {
            $this->redirectToUri($returnUrl);
        } else {
            $this->redirect('list');
        }
    }

    /**
     * Action: Delete
     *
     * @param \KoninklijkeCollective\MyRedirects\Domain\Model\Redirect $redirect
     * @param string $returnUrl
     * @return void
     */
    public function deleteAction($redirect = null, $returnUrl = '')
    {
        $this->enqueueFlashMessage(
            $this->translate('controller.action.success.delete.description'),
            $this->translate('controller.action.success.delete.title')
        );
        $this->getRedirectRepository()->remove($redirect);

        if (!empty($returnUrl)) {
            $this->redirectToUri($returnUrl);
        } else {
            $this->redirect('list');
        }
    }

    /**
     * Handle own enqueue for flash messages
     *
     * @param string $messageBody
     * @param string $messageTitle
     * @param int $severity
     * @param bool $storeInSession
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function enqueueFlashMessage($messageBody, $messageTitle = '', $severity = \TYPO3\CMS\Core\Messaging\AbstractMessage::OK, $storeInSession = true)
    {
        if (!is_string($messageBody)) {
            throw new \InvalidArgumentException('The message body must be of type string, "' . gettype($messageBody) . '" given.', 1243258395);
        }
        /* @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
        $flashMessage = $this->getObjectManager()->get(\TYPO3\CMS\Core\Messaging\FlashMessage::class, $messageBody, $messageTitle, $severity, $storeInSession);
        $this->getFlashMessageQueue()->enqueue($flashMessage);
    }

    /**
     * @return \TYPO3\CMS\Core\Messaging\FlashMessageQueue
     */
    protected function getFlashMessageQueue()
    {
        if ($this->flashMessageQueue === null) {
            /** @var \TYPO3\CMS\Core\Messaging\FlashMessageService $flashMessageService */
            $flashMessageService = $this->getObjectManager()->get(\TYPO3\CMS\Core\Messaging\FlashMessageService::class);
            $this->flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier('myredirects.errors');
        }
        return $this->flashMessageQueue;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected function getObjectManager()
    {
        if ($this->objectManager === null) {
            $this->objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        }
        return $this->objectManager;
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Domain\Repository\RedirectRepository
     */
    protected function getRedirectRepository()
    {
        if ($this->redirectRepository === null) {
            $this->redirectRepository = $this->getObjectManager()->get(\KoninklijkeCollective\MyRedirects\Domain\Repository\RedirectRepository::class);
        }
        return $this->redirectRepository;
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\RedirectService
     */
    protected function getRedirectService()
    {
        if ($this->redirectService === null) {
            $this->redirectService = $this->getObjectManager()->get(\KoninklijkeCollective\MyRedirects\Service\RedirectService::class);
        }
        return $this->redirectService;
    }

    /**
     * @return BackendSession
     */
    protected function getBackendSession()
    {
        if ($this->backendSession === null) {
            $this->backendSession = $this->getObjectManager()->get(BackendSession::class);
            $this->backendSession->setBackendUserAuthentication($GLOBALS['BE_USER']);
        }
        return $this->backendSession;
    }

    /**
     * Translate key for local extension
     *
     * @param string $key
     * @param array $arguments
     * @return string
     */
    protected function translate($key, $arguments = [])
    {
        $text = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($key, 'my_redirects', $arguments);
        return $text ? $text : $key;
    }

    /**
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUserAuthentication()
    {
        return $GLOBALS['BE_USER'];
    }

}
