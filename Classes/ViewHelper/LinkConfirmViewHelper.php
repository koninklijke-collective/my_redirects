<?php
namespace Serfhos\MyRedirects\ViewHelper;

use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Displays link with sprite icon with confirm message
 *
 * @package Serfhos\MyRedirects\ViewHelpers
 */
class LinkConfirmViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Render confirm link with sprite icon
     *
     * @param string $link
     * @param string $message
     * @param string $title
     * @param string $icon
     * @return string
     */
    public function render($link, $message = '', $title = '', $icon = 'actions-edit-delete')
    {
        if (!empty($link)) {
            return '<a href="' . $link . '"  onclick="return confirm(' . GeneralUtility::quoteJSvalue($message) . ')" title="' . $title . '">'
            . IconUtility::getSpriteIcon($icon)
            . '</a>';
        }
        return '';
    }
}
