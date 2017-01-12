<?php
namespace KoninklijkeCollective\MyRedirects\Hook;

use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;
use KoninklijkeCollective\MyRedirects\Service\MatchingService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * DatabaseUserPermissionCheck: Hook: Redirect collision lookup
 *
 * @package KoninklijkeCollective\MyRedirects\Hook
 * @see \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseUserPermissionCheck
 */
class CollisionSignal implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * Validate given redirect has collisions in database
     *
     * @param \TYPO3\CMS\Backend\Controller\EditDocumentController $editDocumentController
     * @return \TYPO3\CMS\Backend\Controller\EditDocumentController
     */
    public function hasCollision($editDocumentController)
    {
        foreach ($editDocumentController->editconf as $table => $conf) {
            if ($table === Redirect::TABLE) {
                $uid = (int)key($conf);
                if ($uid > 0) {
                    $redirect = BackendUtility::getRecord(
                        Redirect::TABLE,
                        $uid
                    );

                    $this->findCollisions($redirect);
                }
            }
        }

        return [$editDocumentController];
    }

    /**
     * @param array $redirect
     * @return void
     */
    protected function findCollisions($redirect)
    {
        if (!empty($redirect)) {
            $collisions = $this->getMatchingService()->getCollisions($redirect);
            if ($collisions) {
                $this->enqueueCollisionWarning($redirect['url'], $collisions);
            }
        }
    }

    /**
     * Enqueue flash message for backend warning
     *
     * @param string $url
     * @param array $collisions
     * @return void
     */
    protected function enqueueCollisionWarning($url, $collisions)
    {
        $collisionData = [];
        foreach ($collisions as $collision) {
            $collisionData[] = $collision['url'] . ' (' . $collision['uid'] . ')';
        }

        $label = sprintf(
            LocalizationUtility::translate('collisions-detected', 'my_redirects'),
            $url,
            implode("', '", $collisionData)
        );

        $message = $this->getObjectManager()->get(
            FlashMessage::class,
            $label,
            '',
            FlashMessage::WARNING,
            true
        );
        if (!empty($message)) {
            $flashMessageService = $this->getObjectManager()->get(FlashMessageService::class);
            $flashMessageService->getMessageQueueByIdentifier()->enqueue($message);
        }
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\MatchingService
     */
    protected function getMatchingService()
    {
        return $this->getObjectManager()->get(MatchingService::class);
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }
}
