<?php
namespace KoninklijkeCollective\MyRedirects\ViewHelpers;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Edit Record ViewHelper, see FormEngine logic
 *
 * @internal
 */
class EditRecordViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Returns a URL to link to FormEngine
     *
     * @param string $parameters Is a set of GET params to send to FormEngine
     * @return string URL to FormEngine module + parameters
     * @see \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl()
     */
    public function render($parameters)
    {
        $parameters = GeneralUtility::explodeUrl2Array($parameters);
        return BackendUtility::getModuleUrl('record_edit', $parameters);
    }
}
