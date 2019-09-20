<?php

namespace KoninklijkeCollective\MyRedirects\Install\Updates;

use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;
use KoninklijkeCollective\MyRedirects\Service\DomainService;
use KoninklijkeCollective\MyRedirects\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\ConfirmableInterface;
use TYPO3\CMS\Install\Updates\Confirmation;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Upgrade scripts for extension deprecation
 */
final class MigrateDeprecatedRedirects implements UpgradeWizardInterface, ConfirmableInterface
{
    /**
     * Process cache for domain lookup
     *
     * @var array
     */
    private $domains = [
        0 => '*',
    ];

    /**
     * @return string[] All new fields and tables must exist
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    /**
     * Return a confirmation message instance
     *
     * @return \TYPO3\CMS\Install\Updates\Confirmation
     */
    public function getConfirmation(): \TYPO3\CMS\Install\Updates\Confirmation
    {
        return new Confirmation(
            'Please make sure to read the following carefully:',
            '[ONE TIME ONLY!] Running this upgrade scripts moves redirects to the new core'
            . ' redirects table and will delete the record in the my_redirects table. Please backup'
            . ' the tables before running this script.',
            false,
            'Yes, I have a backup of the tables!',
            '',
            true
        );
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return ConfigurationUtility::EXTENSION . '_deprecate_extension';
    }

    /**
     * Return the speaking name of this wizard
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'Deprecate ext:my_redirects and use ext:redirect from the TYPO3 core';
    }

    /**
     * Return the description for this wizard
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'The extension my_redirects provided a module to redirect URLs to other URLs. This '
            . ' functionality is now included in the core of TYPO3 9LTS. The records can be migrated '
            . ' to core redirects to make upgrading to TYPO3 v9 easier. The system extension '
            . ' "Redirects" must be installed and the database tables "sys_domain" and'
            . ' "tx_myredirects_domain_model_redirect" must be present.';
    }

    /**
     * Execute the update
     *
     * @return bool
     */
    public function executeUpdate(): bool
    {
        $connection = $this->getConnectionForTable(Redirect::TABLE);
        $targetConnection = $this->getConnectionForTable('sys_redirects');

        $query = $connection->select(['*'], Redirect::TABLE);
        while ($row = $query->fetch()) {
            if ($targetConnection->insert('sys_redirect', [
                'updatedon' => time(),
                'createdon' => (int)$row['crdate'],
                'deleted' => 0,
                'disabled' => (int)((bool)$row['active'] === 0),
                'starttime' => 0,
                'endtime' => 0,
                'is_regexp' => 0,
                'force_https' => 0,
                'keep_query_parameters' => 0,
                'disable_hitcount' => 0,

                'source_host' => $this->getDomainName($row['domain']),
                'source_path' => '/' . $row['url'],
                'target' => $row['destination'],
                'target_statuscode' => $row['http_response'] > 0
                    ? $row['http_response']
                    : 302,
                'hitcount' => (int)$row['counter'],
                'lasthiton' => (int)$row['last_hit'],
            ])) {
                $connection->delete(Redirect::TABLE, ['uid' => $row['uid']]);
            }
        }

        return true;
    }

    /**
     * Check if data for migration exists.
     *
     * @return bool
     */
    public function updateNecessary(): bool
    {
        if (!$this->checkIfTableIsPresentAndNotEmpty(Redirect::TABLE, 'url')) {
            return false;
        }

        return true;
    }

    /**
     * @param  int  $domainId
     * @return string
     */
    protected function getDomainName(int $domainId): string
    {
        if (!isset($this->domains[$domainId])) {
            $row = $this->getDomainService()->getDomain($domainId);
            $this->domains[$domainId] = $row['domainName'];
        }

        return $this->domains[$domainId];
    }

    /**
     * @param  string  $table
     * @return \TYPO3\CMS\Core\Database\Connection
     */
    protected function getConnectionForTable(string $table): Connection
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($table);
    }

    /**
     * @param  string  $tableName
     * @param  string  $checkField
     * @return bool
     */
    protected function checkIfTableIsPresentAndNotEmpty(string $tableName, string $checkField): bool
    {
        try {
            $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
            $connection = $connectionPool->getConnectionByName('Default');
            $columns = $connection->getSchemaManager()->listTableColumns($tableName);
            if (isset($columns[strtolower($checkField)])) {
                // Table is present
                $connection = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getConnectionForTable($tableName);
                $numberOfEntries = $connection->count('*', $tableName, []);
                if ($numberOfEntries > 0) {
                    return true;
                }
            }
        } catch (\Exception $e) {
        }

        return false;
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\DomainService
     */
    protected function getDomainService(): DomainService
    {
        return GeneralUtility::makeInstance(DomainService::class);
    }
}
