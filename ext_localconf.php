<?php
defined('TYPO3_MODE') or die ('Access denied.');

call_user_func(function ($extension) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][$extension] = \KoninklijkeCollective\MyRedirects\Command\ActiveLookupCommandController::class;

    switch (TYPO3_MODE) {
        case 'BE':
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][$extension] = \KoninklijkeCollective\MyRedirects\Hook\DataHandlerHook::class;

            $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
            $signalSlotDispatcher->connect(
                \TYPO3\CMS\Backend\Controller\EditDocumentController::class,
                'initAfter',
                \KoninklijkeCollective\MyRedirects\Hook\CollisionSignal::class,
                'hasCollision'
            );

            break;

        case 'FE':
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/index_ts.php']['preprocessRequest'][$extension] = \KoninklijkeCollective\MyRedirects\Controller\RequestController::class . '->redirectAction';

            break;
    }
}, $_EXTKEY);
