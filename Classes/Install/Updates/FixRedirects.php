<?php

namespace KoninklijkeCollective\MyRedirects\Install\Updates;

use KoninklijkeCollective\MyRedirects\Domain\Model\Redirect;
use KoninklijkeCollective\MyRedirects\Exception\InvalidRedirectException;
use KoninklijkeCollective\MyRedirects\Functions\ObjectManagerTrait;
use KoninklijkeCollective\MyRedirects\Functions\QueryBuilderTrait;
use KoninklijkeCollective\MyRedirects\Service\RedirectService;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Install\Updates\AbstractUpdate;

/**
 * Fix Redirects (hashes and unavailable items)
 *
 * @package KoninklijkeCollective\MyRedirects\Install\Updates
 */
class FixRedirects extends AbstractUpdate
{
    use ObjectManagerTrait;
    use QueryBuilderTrait;

    /**
     * @var \KoninklijkeCollective\MyRedirects\Service\RedirectService
     */
    protected $redirectService;

    /**
     * FixRedirects constructor.
     */
    public function __construct()
    {
        $this->setTitle('Recalculate the redirect required dynamic fields');
    }

    /**
     * @param string &$description The description for the update
     * @return bool False is always returned. This should be a manually invoke.
     */
    public function checkForUpdate(&$description): bool
    {
        $description = 'Loop through all current redirects and see if there are fields that needs to be fixed (URL Hashes, storage folders)';
        $executed = $this->isWizardDone();
        if ($this->isWizardDone() === null) {
            $this->markWizardAsDone();
        }
        return !$executed;
    }

    /**
     * Adjust all redirects that needs fixing
     *
     * @param array &$databaseQueries Queries done in this update
     * @param string &$customMessage Custom error message when failed
     * @return bool Whether everything went smoothly or not
     */
    public function performUpdate(array &$databaseQueries, &$customMessage): bool
    {
        $queryBuilder = static::getQueryBuilderForTable(Redirect::TABLE);
        $queryBuilder->getRestrictions()->removeAll();
        $query = $queryBuilder
            ->select('*')
            ->from(Redirect::TABLE)
            ->execute();

        while ($row = $query->fetch()) {
            try {
                $fields = $this->getFixedFieldsForRedirect($row);

                // Starting statement
                $statement = $queryBuilder
                    ->update(Redirect::TABLE)
                    ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($row['uid'], \PDO::PARAM_INT)))
                    ->set('tstamp', time(), false);

                // Add new fixed values to statement
                foreach ($fields as $field => $value) {
                    $statement->set($field, $value);
                }

                // Store query in log for debugging/output
                $sql = $statement->getSQL() . ';';
                if ($statement->execute()) {
                    $databaseQueries[] = $sql;
                }
            } catch (\Exception $e) {
                // Do nothing..
            }
        }

        $updated = count($databaseQueries);
        // Only return the first sum queries due to heavy fluid memory usage..
        $displayTotal = 25;
        if ($updated > $displayTotal) {
            array_splice(
                $databaseQueries,
                $displayTotal,
                $updated,
                ' -- and ' . ($updated - $displayTotal) . ' similar queries'
            );
        }

        $customMessage = '<p>A total of ' . $updated . ' redirects are fixed.</p>';

        $this->markWizardAsDone();
        return true;
    }

    /**
     * @param array $row
     * @return array
     * @throws InvalidRedirectException
     * @throws \Exception
     */
    protected function getFixedFieldsForRedirect(array $row): array
    {
        if (empty($row['url'])) {
            throw new InvalidRedirectException('Url can not be empty..', 1541594907513);
        }

        $fields = [];
        try {
            // Recalculate correct hash
            $hash = (string)$this->getRedirectService()->generateUrlHash($row['url']);
            if ($row['url_hash'] !== $hash) {
                $fields['url_hash'] = $hash;
            }
        } catch (\Exception $e) {
            // Do nothing when url is not correct..
        }

        // Check rootpage & domain based on user selection
        if (!empty($row['root_page_domain'])) {
            $info = Redirect::getDomainInfo($row['root_page_domain']);
            // Set Page ID & domain based on configured domain selection
            if ($info['storage'] && $info['storage'] !== $row['pid']) {
                $fields['pid'] = $info['storage'];
            }
            if ($info['domain'] && $info['domain'] !== $row['domain']) {
                $fields['domain'] = $info['domain'];
            }
        }

        // Fix destination from older redirects links (only page id)
        if (MathUtility::canBeInterpretedAsInteger($row['destination'])) {
            $fields['destination'] = 't3://page?uid=' . $row['destination'];
        }

        if (empty($fields)) {
            throw new \Exception('No update needed.', 1541601434787);
        }
        return $fields;
    }

    /**
     * @return RedirectService
     */
    protected function getRedirectService(): RedirectService
    {
        return static::getObjectManager()->get(RedirectService::class);
    }
}
