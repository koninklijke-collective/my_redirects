<?php
namespace KoninklijkeCollective\MyRedirects\ViewHelper;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Display core flash messages
 *
 * @package KoninklijkeCollective\MyRedirects\ViewHelpers
 */
class FlashMessagesViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * Renders FlashMessages and flushes the FlashMessage queue
     * Note: This disables the current page cache in order to prevent FlashMessage output
     * from being cached.
     *
     * @param string $as
     * @return string rendered Flash Messages, if there are any.
     */
    public function render($as = 'flashMessages')
    {
        $content = null;

        $flashMessages = $this->getFlashMessageQueue()->getAllMessagesAndFlush();
        if (!empty($flashMessages)) {
            $templateVariableContainer = $this->renderingContext->getTemplateVariableContainer();
            $templateVariableContainer->add($as, $flashMessages);
            $content = $this->renderChildren();
            $templateVariableContainer->remove($as);
        }

        return $content;
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

}
