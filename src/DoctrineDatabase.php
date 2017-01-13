<?php
namespace Littlerobinson\QuerybuilderDoctrine;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Yaml\Yaml;
use Littlerobinson\QuerybuilderDoctrine\Utils\Database;

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
     * @return int
     */
    public function writeDatabaseYamlConfig()
    {
        /// Get existing configuration if exist
        $currentConfig = false;
        if (@file_get_contents(__DIR__ . '/../config/database-config.yml')) {
            $currentConfig = Yaml::parse(file_get_contents(__DIR__ . '/../config/database-config.yml'));
        }
        /// Get database config array
        $datas = $this->getDatabaseConfig();

        /// Put current config traduction if an existing configuration file exist
        if ($currentConfig) {
            $arrDiff = array_diff(array_map('serialize', $currentConfig), array_map('serialize', $datas));
            foreach ($arrDiff as $tableKey => $tableDiff) {
                $newTableDiff                          = unserialize($tableDiff);
                $datas[$tableKey]['_table_traduction'] = $newTableDiff['_table_traduction'];
                foreach ($newTableDiff as $fieldKey => $fieldDiff) {
                    if (!is_array($fieldDiff)) {
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

        /// Write yaml
        $yaml = Yaml::dump($datas, 3);

        return @file_put_contents(__DIR__ . '/../config/database-config.yml', $yaml);
    }

    /**
     * @param string $table
     * @return \Doctrine\DBAL\Schema\Table
     */
    public function getTableDetails(string $table)
    {
        return $this->schemaManager->listTableDetails($table);
    }


    /**
     * @param string $table
     * @return array
     */
    public function getTableColumns(string $table)
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
    public function getPrimaryKey(string $table)
    {
        return $this->getTableDetails($table)->getPrimaryKey()->getColumns();
    }

    /**
     * @param string $table
     * @return array
     */
    public function getForeignKeys(string $table)
    {
        $listForeignKey = [];
        foreach ($this->getTableDetails($table)->getForeignKeys() as $fk) {
            $listForeignKey['tableName']      = $fk->getForeignTableName();
            $listForeignKey['columns']        = $fk->getForeignTableName();
            $listForeignKey['foreignColumns'] = $fk->getForeignTableName();
            $listForeignKey['name']           = $fk->getForeignTableName();
            $listForeignKey['options']        = $fk->getForeignTableName();
        }
        return $listForeignKey;
    }

    /* ============================================================================================================== */
    /* ============================================== ACCESSORS ==================================================== */
    /* ============================================================================================================== */

    public function getTables()
    {
        return $this->tables;
    }

}