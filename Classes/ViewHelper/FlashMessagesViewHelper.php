<?php
namespace Serfhos\MyRedirects\ViewHelper;

/**
 * Display core flash messages
 *
 * @package Serfhos\MyRedirects\ViewHelpers
 */
class FlashMessagesViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\FlashMessagesViewHelper
{

    /**
     * @var \TYPO3\CMS\Core\Messaging\FlashMessageService
     * @inject
     */
    protected $flashMessageService;

    /**
     * Renders FlashMessages and flushes the FlashMessage queue
     * Note: This disables the current page cache in order to prevent FlashMessage output
     * from being cached.
     *
     * @see \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::no_cache
     * @param string $renderMode one of the RENDER_MODE_* constants
     * @return string rendered Flash Messages, if there are any.
     */
    public function render($renderMode = self::RENDER_MODE_UL)
    {
        $content = parent::render($renderMode);

        // get core messages and do the same rendering

        $flashMessages = $this->flashMessageService->getMessageQueueByIdentifier()->getAllMessagesAndFlush();
        if (!empty($flashMessages)) {
            switch ($renderMode) {
                case self::RENDER_MODE_UL:
                    $content .= $this->renderUl($flashMessages);
                    break;
                case self::RENDER_MODE_DIV:
                    $content .= $this->renderDiv($flashMessages);
                    break;
            }
        }

        return $content;
    }
}