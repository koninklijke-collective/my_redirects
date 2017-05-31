<?php

namespace KoninklijkeCollective\MyRedirects\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility: FlashMessage
 *
 * @package KoninklijkeCollective\MyRedirects\Utility
 */
class FlashMessageUtility
{

    const QUEUE_IDENTIFIER = 'ext_my_redirects_queue';

    /**
     * Queue message for extension
     *
     * @param string $message
     * @param string $title
     * @param integer $severity
     * @param boolean $storeInSession
     * @return void
     */
    public static function enqueueMessage($message, $title = '', $severity = \TYPO3\CMS\Core\Messaging\AbstractMessage::OK, $storeInSession = false)
    {
        /** @var $flashMessage \TYPO3\CMS\Core\Messaging\FlashMessage */
        $flashMessage = GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Messaging\FlashMessage::class,
            $message,
            $title,
            $severity,
            $storeInSession
        );

        /** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessageService::class);
        /** @var $flashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
        $flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier(ConfigurationUtility::FLASH_MESSAGE_QUEUE_IDENTIFIER);
        $flashMessageQueue->enqueue($flashMessage);
    }
}
