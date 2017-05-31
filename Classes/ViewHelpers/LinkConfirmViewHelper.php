<?php

namespace KoninklijkeCollective\MyRedirects\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Displays link with sprite icon with confirm message
 *
 * @package KoninklijkeCollective\MyRedirects\ViewHelpers
 */
class LinkConfirmViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument('link', 'string', 'Action when confirmed', true);
        $this->registerArgument('tooltip', 'string', 'Tooltip title', false);
        $this->registerArgument('title', 'string', 'Modal title', false);
        $this->registerArgument('message', 'string', 'Modal description', false);
        $this->registerArgument('class', 'string', 'Extra classes for wrapper', false);
        $this->registerArgument('icon', 'string', 'Used icon for interaction', false, 'actions-edit-delete');
        $this->registerArgument('iconOverlay', 'string', 'Icon overlay ', false);
        $this->registerArgument('severity', 'string', 'Modal severity when opened', false, 'warning');
        $this->registerArgument('closeButton', 'string', 'Button text for closing modal', false);
        $this->registerArgument('okButton', 'string', 'Button text for invoking action modal', false);
        $this->registerArgument('modalClass', 'string', 'Default typo3 trigger class', false, 't3js-modal-trigger');
    }

    /**
     * Render confirm link with sprite icon
     *
     * @return string
     */
    public function render()
    {
        $arguments = $this->arguments;
        if (!empty($arguments['link'])) {
            /** @var \TYPO3\CMS\Core\Imaging\IconFactory $iconFactory */
            $iconFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconFactory::class);
            $attributes = [
                'href' => $arguments['link'],
                'title' => $arguments['tooltip'],
                'data-severity' => $arguments['severity'],
                'data-title' => $arguments['title'],
                'data-content' => $arguments['message'],
                'data-button-close-text' => $arguments['closeButton'],
                'data-button-ok-text' => $arguments['okButton'],
                'class' => 'btn btn-default ' . $arguments['modalClass'] . ($arguments['class'] ? ' ' . $arguments['class'] : ''),
            ];

            return '<a ' . GeneralUtility::implodeAttributes($attributes, true) . '>'
                . $iconFactory->getIcon($arguments['icon'], \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL, $arguments['iconOverlay'])
                . '</a>';
        }
        return '';
    }

}

