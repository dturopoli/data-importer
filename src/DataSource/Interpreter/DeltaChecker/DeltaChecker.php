<?php


namespace Pimcore\Bundle\DataHubBatchImportBundle\DataSource\Interpreter\DeltaChecker;

use Pimcore\Db;
use Doctrine\DBAL\Exception\TableNotFoundException;

class DeltaChecker
{

    /**
     * @var Db\Connection|Db\ConnectionInterface
     */
    protected $db;

    const CACHE_TABLE_NAME = 'bundle_data_hub_batch_import_delta_cache';

    public function __construct(Db\ConnectionInterface $connection)
    {
        $this->db = $connection;
    }

    protected function createTableIfNotExisting(\Closure $callable = null)
    {
        $this->db->executeQuery(sprintf('CREATE TABLE IF NOT EXISTS %s (
            configName varchar(50) NOT NULL,
            id varchar(50) NOT NULL,
            hash varchar(100) NOT NULL,
            PRIMARY KEY (configName, id))
        ', self::CACHE_TABLE_NAME));

        if ($callable) {
            return $callable();
        }
    }


    protected function getCurrentHash(string $configName, string $id): string {
        try {
            return $this->db->fetchOne(
                    sprintf('SELECT hash FROM %s WHERE configName = ? AND id = ?', self::CACHE_TABLE_NAME),
                    [$configName, $id]
                ) ?? '';
        } catch (TableNotFoundException $exception) {
            return $this->createTableIfNotExisting(function() use ($configName, $id) {
                $this->getCurrentHash($configName, $id);
            });
        }
    }

    protected function updateHash(string $configName, string $id, string $hash) {

        try {
            $this->db->executeQuery(
                sprintf('INSERT INTO %s (configName, id, hash) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE hash = ?', self::CACHE_TABLE_NAME),
                [$configName, $id, $hash, $hash]
            );
        } catch (TableNotFoundException $exception) {
            return $this->createTableIfNotExisting(function() use ($configName, $id, $hash) {
                $this->updateHash($configName, $id, $hash);
            });
        }
    }

    public function hasChanged(string $configName, $idDataIndex, array $data): bool {

        $id = $data[$idDataIndex] ?? null;
        if($id === null) {
            return false;
        }

        $oldHash = $this->getCurrentHash($configName, $id);

        $newHash = md5(json_encode($data));
        if($oldHash !== $newHash) {
            $this->updateHash($configName, $id, $newHash);
            return true;
        } else {
            return false;
        }
    }

    public function cleanup(string $configName) {
        try {
            $this->db->executeQuery(
                sprintf('DELETE FROM %s WHERE configName = ?', self::CACHE_TABLE_NAME),
                [$configName]
            );
        } catch (TableNotFoundException $exception) {
            return $this->createTableIfNotExisting();
        }
    }

}