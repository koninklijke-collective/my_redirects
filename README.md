# TYPO3 Extension: Redirects management
  * Description: Maintain your own redirects in the backend
  * Extension key: my_redirects
  * TER: http://typo3.org/extensions/repository/view/my_redirects
  * Replaced in TYPO3 9.x by core ext:redirects
    * Can be installed via ```composer require typo3/cms-redirects```


Possible configuration:
```php
<?php
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['my_redirects'] = [
    // Define default root page (fallback is on RealURL configured _DEFAULT rootpage ID
    'defaultRootPageId' => 1,
    // Define HTTP response (defaults on 302)
    'defaultHeaderStatusCode' => \TYPO3\CMS\Core\Utility\HttpUtility::HTTP_STATUS_302,
];
```
