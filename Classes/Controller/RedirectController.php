<?php
namespace Serfhos\MyRedirects\Controller;

use Serfhos\MyRedirects\Domain\Model\Redirect;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Backend Module Controller: Redirects
 *
 * @package Serfhos\MyRedirects\Controller
 */
class RedirectController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * Page information from given access
     *
     * @var array
     */
    protected $page = array();

    /**
     * @var \Serfhos\MyRedirects\Domain\Repository\RedirectRepository
     * @inject
     */
    protected $redirectRepository;

    /**
     * @var \Serfhos\MyRedirects\Service\RedirectService
     * @inject
     */
    protected $redirectService;

    /**
     * @var \Serfhos\MyRedirects\Backend\BackendSession
     * @inject
     */
    protected $backendSession;

    /**
     * @var string
     */
    protected $sessionKey = 'MyRedirects';

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
     * Initializes the view before invoking an action method
     *
     * @param \TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view The view to be initialized
     * @return void
     */
    protected function initializeView(ViewInterface $view)
    {
        $moduleUrl = urlencode(\TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl('web_MyRedirectsMyRedirects'));
        $currentUrl = $this->uriBuilder->setAddQueryString(true)->setArgumentsToBeExcludedFromQueryString(array('returnUrl'))->build()
            . '&vC=' . urlencode($this->getBackendUserAuthentication()->veriCode())
            . BackendUtility::getUrlToken('tceAction')
            . '&prErr=1&uPT=1';

        $this->view->assignMultiple(array(
            'moduleUrl' => $moduleUrl,
            'currentUrl' => $currentUrl,
        ));
    }

    /**
     * Initializes the action
     *
     * @return void
     */
    protected function initializeAction()
    {
        parent::initializeAction();

        $this->backendSession
            ->setBackendUserAuthentication($GLOBALS['BE_USER'])
            ->createSession($this->sessionKey);

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
            $this->addFlashMessage(
                LocalizationUtility::translate('controller.initialize.error.no_typoscript.description', 'my_redirects'),
                LocalizationUtility::translate('controller.initialize.error.no_typoscript.title', 'my_redirects'),
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
        } else {
            $filters = $this->backendSession->getSessionContents($this->sessionKey);
            if ($filters === false) {
                $filters = array(
                    'filter' => array(),
                    'order' => 'url',
                    'direction' => QueryInterface::ORDER_ASCENDING
                );
            }
            if ($this->request->hasArgument('filter')) {
                $filter = $this->request->getArgument('filter');
                if (is_array($filter)) {
                    $filters['filter'] = $this->request->getArgument('filter');
                } else {
                    $filters['filter'] = array();
                }
            }
            if ($this->request->hasArgument('order')) {
                $filters['order'] = $this->request->getArgument('order');
            }
            if ($this->request->hasArgument('direction')) {
                $filters['direction'] = $this->request->getArgument('direction');
            }

            $this->backendSession->saveSessionContents($filters);
        }
    }

    /**
     * Action: List redirects
     *
     * @return void
     */
    public function listAction()
    {
        $arguments = $this->backendSession->getSessionContents($this->sessionKey);
        $filter = (array) $arguments['filter'];

        // Temporary set page filter
        if (!empty($this->page)) {
            $filter['page'] = $this->page['uid'];
        }

        $this->view->assignMultiple(array(
            'page' => $this->page,
            'filter' => $filter,
            'order' => $arguments['order'],
            'direction' => $arguments['direction'],
            'redirects' => $this->getRedirectRepository()->findByOrder(
                $filter,
                $arguments['order'],
                $arguments['direction']
            ),
        ));
    }

    /**
     * Action: Check if redirect is still active and works as intended
     *
     * @param \Serfhos\MyRedirects\Domain\Model\Redirect $redirect
     * @param string $returnUrl
     * @return void
     */
    public function lookupAction($redirect = null, $returnUrl = '')
    {
        if ($redirect instanceof Redirect) {
            $this->getRedirectService()->activeLookup($redirect);
            $this->getRedirectRepository()->update($redirect);

            $this->addFlashMessage(
                LocalizationUtility::translate('controller.action.success.lookup.description', 'my_redirects',
                    array('/' . $redirect->getUrl())),
                LocalizationUtility::translate('controller.action.success.lookup.title', 'my_redirects'),
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
     * @param \Serfhos\MyRedirects\Domain\Model\Redirect $redirect
     * @param string $returnUrl
     * @return void
     */
    public function deleteAction($redirect = null, $returnUrl = '')
    {
        $this->addFlashMessage(
            LocalizationUtility::translate('controller.action.success.delete.description', 'my_redirects'),
            LocalizationUtility::translate('controller.action.success.delete.title', 'my_redirects')
        );
        $this->getRedirectRepository()->remove($redirect);

        if (!empty($returnUrl)) {
            $this->redirectToUri($returnUrl);
        } else {
            $this->redirect('list');
        }
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected function getObjectManager()
    {
        if (!isset($this->objectManager)) {
            $this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        }
        return $this->objectManager;
    }

    /**
     * @return \Serfhos\MyRedirects\Domain\Repository\RedirectRepository
     */
    protected function getRedirectRepository()
    {
        if (!isset($this->redirectRepository)) {
            $this->redirectRepository = $this->getObjectManager()->get('Serfhos\\MyRedirects\\Domain\\Repository\\RedirectRepository');
        }
        return $this->redirectRepository;
    }

    /**
     * @return \Serfhos\MyRedirects\Service\RedirectService
     */
    protected function getRedirectService()
    {
        if (!isset($this->redirectService)) {
            $this->redirectService = $this->getObjectManager()->get('Serfhos\\MyRedirects\\Service\\RedirectService');
        }
        return $this->redirectService;
    }

    /**
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUserAuthentication()
    {
        return $GLOBALS['BE_USER'];
    }
}