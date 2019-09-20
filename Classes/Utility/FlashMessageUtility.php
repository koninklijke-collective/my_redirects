<?php

namespace KoninklijkeCollective\MyRedirects\Utility;

use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility: FlashMessage
 */
class FlashMessageUtility
{

    /**
     * Queue message for extension
     *
     * @param string $message
     * @param string $title
     * @param integer $severity
     * @param boolean $storeInSession
     * @return void
     * @throws \TYPO3\CMS\Core\Exception
     */
    public static function enqueueMessage($message, $title = '', $severity = AbstractMessage::OK, $storeInSession = false)
    {
        /** @var $flashMessage \TYPO3\CMS\Core\Messaging\FlashMessage */
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $message,
            $title,
            $severity,
            $storeInSession
        );

        /** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        /** @var $flashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
        $flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier(ConfigurationUtility::FLASH_MESSAGE_QUEUE_IDENTIFIER);
        $flashMessageQueue->enqueue($flashMessage);
    }
}
