<?php
namespace Littlerobinson\QueryBuilder;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Yaml\Yaml;
use Littlerobinson\QueryBuilder\Utils\Database;

/**
 * Class DoctrineDatabase
 * Get Database config using Doctrine
 * @package Littlerobinson\QueryBuilder
 */
class DoctrineDatabase
{
    private $configuration;

    private $entityManager;

    private $connection;

    private $schemaManager;

    private $databases;

    private $tables;

    /**
     * DoctrineDatabase constructor.
     */
    public function __construct()
    {
        $this->configuration = Setup::createAnnotationMetadataConfiguration(Database::$paths, Database::$isDevMode);
        $this->entityManager = EntityManager::create(Database::$params, $this->configuration);
        $this->connection    = $this->entityManager->getConnection();
        $this->schemaManager = $this->connection->getSchemaManager();
        $this->tables        = $this->schemaManager->listTableNames();
        $this->databases     = $this->schemaManager->listDatabases();
    }

    /**
     * @return array
     */
    private function getDatabaseConfig()
    {
        $response = [];
        $tables   = $this->getTables();
        foreach ($tables as $table) {
            $response[$table] = $this->getTableColumns($table);
        }
        return $response;
    }

    /**
     * @return json|false
     */
    public function getJsonDatabaseConfig()
    {
        return json_encode($this->getDatabaseConfig());
    }

    /**
     * writeDoctrineYamlConfig method
     * Write the yaml config file for the query builder using doctrine
     * @param string $configPath
     * @param int $yamlInline
     * @return bool
     */
    public function writeDatabaseYamlConfig($configPath = __DIR__ . '/../config/database-config.yml', $yamlInline = 4)
    {
        /// Get existing configuration if exist
        $currentConfig = false;
        if (@file_get_contents($configPath)) {
            $currentConfig = Yaml::parse(file_get_contents($configPath));
        }

        /// Get database config array
        $datas = $this->getDatabaseConfig();

        /// Put current config traduction if an existing configuration file exist
        if ($currentConfig) {
            $arrDiff = array_diff(array_map('serialize', $currentConfig), array_map('serialize', $datas));
            foreach ($arrDiff as $tableKey => $tableDiff) {
                $newTableDiff = unserialize($tableDiff);

                $datas[$tableKey]['_table_traduction'] = $newTableDiff['_table_traduction'];
                foreach ($newTableDiff as $fieldKey => $fieldDiff) {
                    if (!is_array($fieldDiff) || $fieldKey === '_FK') {
                        continue;
                    }
                    try {
                        $datas[$tableKey][$fieldKey]['_field_traduction'] = $fieldDiff['_field_traduction'];
                        $datas[$tableKey][$fieldKey]['name']              = $fieldDiff['name'];
                        $datas[$tableKey][$fieldKey]['type']              = $fieldDiff['type'];
                        $datas[$tableKey][$fieldKey]['length']            = $fieldDiff['length'];
                        $datas[$tableKey][$fieldKey]['not_null']          = $fieldDiff['not_null'];
                        $datas[$tableKey][$fieldKey]['definition']        = $fieldDiff['definition'];
                    } catch (\Exception $e) {
                        return false;
                    }
                }
            }
        }
        /// Add FK
        $datas = $this->addForeignKeys($datas);

        /// Write yaml
        $yaml = Yaml::dump($datas, $yamlInline);

        $response = (@file_put_contents($configPath, $yaml) === false) ? false : true;

        return $response;
    }

    /**
     * Get database config
     * Return a json response
     * @param bool $jsonResponse
     * @param string $configPath
     * @return bool|array|string JSON
     */
    public function getDatabaseYamlConfig($jsonResponse = false, $configPath = __DIR__ . '/../config/database-config.yml')
    {
        if (@file_get_contents($configPath)) {
            $config = Yaml::parse(file_get_contents($configPath));
            if ($jsonResponse) {
                return json_encode($config);
            }
            return $config;
        } else {
            return false;
        }
    }

    /**
     * @param string $table
     * @return \Doctrine\DBAL\Schema\Table
     */
    private function getTableDetails(string $table)
    {
        return $this->schemaManager->listTableDetails($table);
    }


    /**
     * @param string $table
     * @return array
     */
    private function getTableColumns(string $table)
    {
        $response = [];
        $columns  = $this->schemaManager->listTableColumns($table);
        foreach ($columns as $key => $column) {
            $response['_table_traduction']       = null;
            $response[$key]['name']              = $column->getName();
            $response[$key]['_field_traduction'] = null;
            $response[$key]['type']              = $column->getType()->getName();
            $response[$key]['default']           = $column->getDefault();
            $response[$key]['length']            = $column->getLength();
            $response[$key]['not_null']          = $column->getNotnull();
            $response[$key]['definition']        = $column->getColumnDefinition();
        }
        return $response;
    }

    /**
     * @param string $table
     * @return array
     */
    private function getPrimaryKey(string $table)
    {
        return $this->getTableDetails($table)->getPrimaryKey()->getColumns();
    }

    /**
     * addForeignKeys method
     * @param array $datas
     * @return array|null
     */
    private function addForeignKeys(array $datas)
    {
        $listForeignKey = [];
        $listTables     = $this->getTables();

        foreach ($listTables as $table) {
            try {
                foreach ($this->getTableDetails($table)->getForeignKeys() as $key => $fk) {
                    $listForeignKey[$table][$fk->getColumns()[0]]['tableName']      = $fk->getForeignTableName();
                    $listForeignKey[$table][$fk->getColumns()[0]]['columns']        = $fk->getColumns()[0];
                    $listForeignKey[$table][$fk->getColumns()[0]]['foreignColumns'] = $fk->getForeignColumns()[0];
                    $listForeignKey[$table][$fk->getColumns()[0]]['options']        = $fk->getOptions();

                    /// Update $datas
                    $datas[$table]['_FK'][$fk->getName()] = $listForeignKey[$table][$fk->getColumns()[0]];
                }
            } catch (\Exception $e) {
                return null;
            }
        }
        return $datas;
    }

    /**
     * @return array
     */
    public function getTables(): array
    {
        return $this->tables;
    }

    /* ============================================================================================================== */
    /* ============================================== ACCESSORS ==================================================== */
    /* ============================================================================================================== */

    /**
     * @return \Doctrine\ORM\Configuration
     */
    public function getConfiguration(): \Doctrine\ORM\Configuration
    {
        return $this->configuration;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection(): \Doctrine\DBAL\Connection
    {
        return $this->connection;
    }

    /**
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    public function getSchemaManager(): \Doctrine\DBAL\Schema\AbstractSchemaManager
    {
        return $this->schemaManager;
    }

    /**
     * @return array
     */
    public function getDatabases(): array
    {
        return $this->databases;
    }


}