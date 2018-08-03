<?php

namespace KoninklijkeCollective\MyRedirects\Functions;

/**
 * Trait: QueryBuilder
 *
 * @package KoninklijkeCollective\MyRedirects\Functions
 */
trait QueryBuilderTrait
{

    /**
     * @param string $table
     * @return \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    protected static function getQueryBuilderForTable($table)
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable($table);
    }
}
