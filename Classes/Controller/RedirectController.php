<?php

namespace KoninklijkeCollective\MyRedirects\Controller;

use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;
use KoninklijkeCollective\MyRedirects\Utility\ConfigurationUtility;
use KoninklijkeCollective\MyRedirects\Utility\FlashMessageUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Backend Module Controller: Redirects
 *
 * @package KoninklijkeCollective\MyRedirects\Controller
 */
class RedirectController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    use \KoninklijkeCollective\MyRedirects\Functions\TranslateTrait;
    use \KoninklijkeCollective\MyRedirects\Functions\ObjectManagerTrait;
    use \KoninklijkeCollective\MyRedirects\Functions\BackendUserAuthenticationTrait;

    /**
     * Backend Template Container
     *
     * @var string
     */
    protected $defaultViewObjectName = \TYPO3\CMS\Backend\View\BackendTemplateView::class;

    /**
     * @var \KoninklijkeCollective\MyRedirects\Domain\Model\DTO\Filter
     */
    protected $filter;

    /**
     * Redirect request from post when forced
     *
     * @param \TYPO3\CMS\Extbase\Mvc\RequestInterface $request The request object
     * @param \TYPO3\CMS\Extbase\Mvc\ResponseInterface $response The response, modified by this handler
     * @return void
     * @throws \Exception
     */
    public function processRequest(\TYPO3\CMS\Extbase\Mvc\RequestInterface $request, \TYPO3\CMS\Extbase\Mvc\ResponseInterface $response)
    {
        // Make sure filter is always persisted
        $this->filter = $this->getFilterDataService()->loadModuleFilter();

        if ($request instanceof \TYPO3\CMS\Extbase\Mvc\Web\Request) {
            $arguments = $request->getArguments();
            if (isset($arguments['resetFilter']) && (bool)$arguments['resetFilter'] === true) {
                $this->filter->getCleanObject();
            }
        }

        // We "finally" persist the module data.
        try {
            parent::processRequest($request, $response);
            $this->getFilterDataService()->persistModuleFilter($this->filter);
        } catch (\Exception $e) {
            $this->getFilterDataService()->persistModuleFilter($this->filter);
            throw $e;
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
            $view->getModuleTemplate()->setFlashMessageQueue($this->controllerContext->getFlashMessageQueue(ConfigurationUtility::FLASH_MESSAGE_QUEUE_IDENTIFIER));
        }

        $currentUrl = $this->uriBuilder->setAddQueryString(true)->setArgumentsToBeExcludedFromQueryString(['returnUrl'])->buildBackendUri();

        $this->view->assignMultiple([
            'moduleUrl' => \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl('web_MyRedirectsMyRedirects'),
            'currentUrl' => $currentUrl,
        ]);
    }

    /**
     * Stupid docheader buttons without fluid rendering -_-
     *
     * @return void
     */
    protected function registerDocheaderButtons()
    {
        if ($this->view instanceof \TYPO3\CMS\Backend\View\BackendTemplateView) {
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
            $parameters = GeneralUtility::explodeUrl2Array('edit[' . Redirect::TABLE . '][' . ConfigurationUtility::getDefaultRootPageId('avoid-root-page') . ']=new&returnUrl=' . $returnUrl);
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
    }

    /**
     * Initializes the action
     *
     * @return void
     */
    protected function initializeAction()
    {
        parent::initializeAction();
        if (!isset($this->settings['staticTemplate'])) {
            $this->controllerContext = $this->buildControllerContext();
            FlashMessageUtility::enqueueMessage(
                $this->translate('controller.initialize.error.no_typoscript.description'),
                $this->translate('controller.initialize.error.no_typoscript.title'),
                AbstractMessage::ERROR,
                true
            );
        } else {
            // Set constants for template rendering
            $this->settings['table']['redirects'] = Redirect::TABLE;
        }
    }

    /**
     * Initialization: Action - List arguments
     */
    protected function initializeListAction()
    {
        if (isset($this->arguments['filter'])) {
            /** @var \TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration $propertyMappingConfiguration */
            $propertyMappingConfiguration = $this->arguments['filter']->getPropertyMappingConfiguration();
            $propertyMappingConfiguration->allowProperties('search', 'status', 'domain', 'order', 'direction');
        }
    }

    /**
     * Action: List redirects
     *
     * @param \KoninklijkeCollective\MyRedirects\Domain\Model\DTO\Filter $filter
     * @return void
     */
    public function listAction($filter = null)
    {
        if ($filter === null) {
            $filter = $this->filter;
        } else {
            $this->filter = $filter;
        }

        $this->view->assignMultiple([
            'filter' => $filter,
            'redirects' => $this->getRedirectRepository()->findAllByFilter($filter)
        ]);
    }

    /**
     * Action: Delete
     *
     * @param \KoninklijkeCollective\MyRedirects\Domain\Model\Redirect $redirect
     * @param string $returnUrl
     * @return void
     * @throws \Exception
     */
    public function deleteAction($redirect, $returnUrl = '')
    {
        if ($redirect instanceof Redirect) {
            if ($this->getBackendUserAuthentication()->isInWebmount($redirect->getPid())) {
                $this->getRedirectRepository()->remove($redirect);
                FlashMessageUtility::enqueueMessage(
                    $this->translate('controller.action.success.delete.description'),
                    $this->translate('controller.action.success.delete.title'),
                    AbstractMessage::OK,
                    true
                );
            } else {
                FlashMessageUtility::enqueueMessage(
                    $this->translate('controller.action.error.delete.description'),
                    $this->translate('controller.action.error.delete.title'),
                    AbstractMessage::ERROR,
                    true
                );
            }
        }

        if (!empty($returnUrl)) {
            $this->redirectToUri($returnUrl);
        } else {
            $this->redirect('list');
        }
    }

    /**
     * Action: (Re-)Activate redirect
     *
     * @param \KoninklijkeCollective\MyRedirects\Domain\Model\Redirect $redirect
     * @param string $returnUrl
     * @return void
     * @throws \Exception
     */
    public function activateAction($redirect, $returnUrl = '')
    {
        if ($redirect instanceof Redirect) {
            $redirect->setActive(true)
                ->setInactiveReason('');
            $this->getRedirectRepository()->update($redirect);

            FlashMessageUtility::enqueueMessage(
                $this->translate('controller.action.success.activate.description'),
                $this->translate('controller.action.success.activate.title'),
                AbstractMessage::OK,
                true
            );
        }

        if (!empty($returnUrl)) {
            $this->redirectToUri($returnUrl);
        } else {
            $this->redirect('list');
        }
    }

    /**
     * Action: Check if redirect is still active and works as intended
     *
     * @param \KoninklijkeCollective\MyRedirects\Domain\Model\Redirect $redirect
     * @param string $returnUrl
     * @return void
     * @throws \Exception
     */
    public function lookupAction($redirect, $returnUrl = '')
    {
        if ($redirect instanceof Redirect) {
            $details = $this->getStatusService()->activeLookup($redirect, false);
            $this->getRedirectRepository()->update($redirect);

            FlashMessageUtility::enqueueMessage(
                $this->translate('controller.action.success.lookup.description', [($details['starting_uri'] ? $details['starting_uri'] : $redirect->getUrl())]),
                $this->translate('controller.action.success.lookup.title'),
                AbstractMessage::INFO,
                true
            );
        }

        if (!empty($returnUrl)) {
            $this->redirectToUri($returnUrl);
        } else {
            $this->redirect('list');
        }
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Domain\Repository\RedirectRepository|object
     */
    protected function getRedirectRepository()
    {
        return $this->getObjectManager()->get(\KoninklijkeCollective\MyRedirects\Domain\Repository\RedirectRepository::class);
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\StatusService|object
     */
    protected function getStatusService()
    {
        return $this->getObjectManager()->get(\KoninklijkeCollective\MyRedirects\Service\StatusService::class);
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\FilterDataService|object
     */
    protected function getFilterDataService()
    {
        return $this->getObjectManager()->get(\KoninklijkeCollective\MyRedirects\Service\FilterDataService::class);
    }
}
