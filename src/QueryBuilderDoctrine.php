<?php

namespace Littlerobinson\QueryBuilder;

use Littlerobinson\QueryBuilder\Utils\Spreadsheet;

/**
 * Class QueryBuilderDoctrine
 * @package Littlerobinson\QueryBuilder
 */
class QueryBuilderDoctrine
{
    private $doctrineDb;

    private $queryBuilder;

    private $objDbConfig;

    private $from;

    private $where;

    private $orderBy;

    private $limit = 10;

    private $offset;

    private $fkFrom;

    private $fromAliasList;

    private $fields;

    private $queryResult;

    /**
     * QueryBuilderDoctrine constructor.
     * @param DoctrineDatabase $doctrineDb
     */
    public function __construct(DoctrineDatabase $doctrineDb)
    {
        $this->doctrineDb    = $doctrineDb;
        $this->queryBuilder  = $this->doctrineDb->getEntityManager()->createQueryBuilder();
        $dbConfig            = $this->doctrineDb->getDatabaseYamlConfig(true);
        $this->objDbConfig   = json_decode($dbConfig);
        $this->fromAliasList = [];

    }

    /**
     * Return an array with all foreign keys in show config file
     * @param $fromObject : If null get all FK
     * @throws \Exception
     * @return array
     */
    private function getFKList($fromObject = null): array
    {
        $fkList     = [];
        $fromObject = $fromObject ?? $this->objDbConfig;
        foreach ($fromObject as $table => $fields) {
            if (!isset($this->objDbConfig->{$table})) {
                http_response_code(400);
                throw new \Exception('This table not exist : ' . $table . '.');
            }
            if (property_exists($this->objDbConfig->{$table}, '_FK')) {
                $fkList[$table] = $this->objDbConfig->{$table}->_FK;
            }
        }
        return $fkList;
    }

    /**
     * Add query select
     * @param $fkList
     * @param $fromTable
     * @param $select
     * @param $fromAlias
     * @throws \Exception
     */
    private function addQuerySelect($fkList, $fromTable, $select, $fromAlias = null)
    {
        $fromAlias = $fromAlias ?? $fromTable . '_' . $this->objDbConfig->{$fromTable}->{'_primary_key'};
        foreach ($select as $key => $field) {
            if (is_object($select) && is_object($field)) {
                /// Add joins
                if (is_object($field)) {
                    //if (!isset($fkList[$fromTable]->{$fkName}->{'tableName'})) {
                    if (!isset($fkList[$fromTable]->{$key}->{'tableName'})) {
                        http_response_code(400);
                        throw new \Exception('This foreign key not exist : ' . $key . '.');
                    }
                    $newFromTable   = $fkList[$fromTable]->{$key}->{'tableName'};
                    $newFrom        = array($newFromTable => $field);
                    $newfkList      = $this->getFKList($newFrom);
                    $columns        = $fkList[$fromTable]->{$key}->{'columns'};
                    $foreignColumns = $fkList[$fromTable]->{$key}->{'foreignColumns'};
                    $this->queryBuilder->leftJoin($newFromTable, $columns, 'ON', $columns . ' . ' . $foreignColumns . ' = ' . $fromAlias . ' . ' . $columns);
                    $this->addQuerySelect($newfkList, $newFromTable, $field, $key);
                }
            } else {
                /// Exit if there is no visibility on the table in config file
                if ($this->objDbConfig->{$fromTable}->{'_table_visibility'} === false) {
                    continue;
                }
                /// Control if field exist
                if (!isset($this->objDbConfig->{$fromTable}->{$field})) {
                    http_response_code(400);
                    throw new \Exception('This field key not exist : ' . $field . '.');
                }
                /// Exit if there is no visibility on the field in config file
                if ($this->objDbConfig->{$fromTable}->{$field}->{'_field_visibility'} === false) {
                    continue;
                }

                /// Push in From Alias
                $fieldTranslation                               = $this->objDbConfig->{$fromTable}->{$field}->{'_field_translation'} ?? $this->objDbConfig->{$fromTable}->{$field}->{'name'};
                $this->fromAliasList[$fromAlias . '_' . $field] = $fieldTranslation;

                /// Feed $fields
                $this->fields[$fromTable][] = $field;

                $this->queryBuilder->addSelect(
                    $fromAlias . ' . ' .
                    $field . ' AS ' .
                    $fromAlias . '_' .
                    $field);
            }
        }
    }

    /**
     * Add query conditions
     */
    private function addQueryCondition()
    {
        if (null === $this->where) {
            return;
        }
        foreach ($this->where as $key => $condition) {
            foreach ($condition as $logicalOperator => $request) {
                foreach ($request as $row => $equality) {
                    $arrRequest = explode('.', $row);

                    if (!isset($this->objDbConfig->{$arrRequest[0]}->{$arrRequest[1]}->type)) {
                        http_response_code(400);
                        throw new \Exception('This field not exist : ' . $arrRequest[0] . '.' . $arrRequest[1]);
                    }

                    /// Get field type
                    $fieldType = $this->objDbConfig->{$arrRequest[0]}->{$arrRequest[1]}->type;
                    /// Get primary key
                    $primaryKey = $this->objDbConfig->{$arrRequest[0]}->_primary_key;
                    /// Get select alias
                    $alias = $arrRequest[0] . '_' . $primaryKey . '.' . $arrRequest[1];
                    /// Get operator equality
                    $operator = key($equality);
                    /// Add comma if not boolean or integer
                    $value = ($fieldType === 'integer' || $fieldType === 'boolean') ? implode(',', $equality->{$operator}) : implode(',', $equality->{$operator});
                    /// Get the condition
                    $condition = $this->getCondition($operator, $alias, $value);

                    switch ($logicalOperator) {
                        case 'AND':
                            $this->queryBuilder->andWhere($condition);
                            break;
                        case 'OR':
                            $this->queryBuilder->orWhere($condition);
                            break;
                        case 'AND_HAVING':
                            $this->queryBuilder->andHaving($condition);
                            break;
                        case 'OR_HAVING':
                            $this->queryBuilder->orHaving($condition);
                            break;
                        default:
                            break;
                    }
                }
            }
        }
    }

    /**
     * @param string $operator
     * @param string $alias
     * @param string $value
     * @return null|string
     */
    private function getCondition(string $operator, string $alias, string $value)
    {
        $condition = null;
        switch ($operator) {
            case 'EQUAL':
                $condition = $alias . ' = ' . '\'' . $value . '\'';
                break;
            case 'LIKE':
                $condition = $alias . ' LIKE ' . '\'%' . $value . '%\'';
                break;
            case 'BEGINS_WITH':
                $condition = $alias . ' LIKE ' . '\'' . $value . '%\'';
                break;
            case 'ENDS_WITH':
                $condition = $alias . ' LIKE ' . '\'%' . $value . '\'';
                break;
        }
        return $condition;
    }

    /**
     * Prepare the query with the json request
     * @param string $jsonQuery
     * @throws \Exception
     */
    private function prepareJsonQuery(string $jsonQuery)
    {
        /// Try to decode json
        $queryObj = json_decode($jsonQuery);
        /// Get From
        $this->from = (property_exists($queryObj, 'from')) ? (array)$queryObj->from : null;
        /// Exception if there is no From
        if ($this->from === null) {
            http_response_code(400);
            throw new \Exception('No From in request.');
        }
        /// Get where conditions
        $this->where = (property_exists($queryObj, 'where')) ? (array)$queryObj->where : null;
        /// Get orderBy
        $this->orderBy = (property_exists($queryObj, 'orderBy')) ? (array)$queryObj->orderBy : null;
        /// Get limit
        $this->limit = (property_exists($queryObj, 'limit')) ? (int)$queryObj->limit : null;
        /// Get offset
        $this->offset = (property_exists($queryObj, 'offset')) ? (int)$queryObj->offset : null;
        /// Create FK array
        $this->fkFrom = (property_exists($queryObj, 'from')) ? $this->getFKList($this->from) : null;
    }

    /**
     * Execute the query
     * @param string $jsonQuery
     * @return array
     */
    public function executeQuery(string $jsonQuery): array
    {
        /// Reset DQL parts if exist
        $this->resetSQLRequest();
        /// Prepare query
        $this->prepareJsonQuery($jsonQuery);

        /// Loop on tables
        foreach ($this->from as $fromTable => $select) {
            /// Create From Alias
            $fromAlias = $fromTable . '_' . $this->objDbConfig->{$fromTable}->{'_primary_key'};
            /// Add From
            $this->queryBuilder->from($fromTable, $fromAlias);
            /// Add Select
            $this->addQuerySelect($this->fkFrom, $fromTable, $select);
        }

        /// Adding query conditions
        $this->addQueryCondition();

        /// Execute query and fetch result
        try {
            $result            = $this->doctrineDb->getConnection()->executeQuery($this->getSQLRequest());
            $this->queryResult = $result->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Doctrine\DBAL\DBALException $e) {
            echo $e->getMessage();
        }
        return $this->queryResult;
    }

    /**
     * Execute the query and return json
     * @param string $jsonQuery
     * @return string
     */
    public function executeQueryJson(string $jsonQuery): string
    {
        $result = $this->executeQuery($jsonQuery);
        if (count($result) > 0) {
            $columns = [];
            $i       = 0;
            foreach ($result[0] as $key => $value) {
                $columns[$i]['key']   = $key;
                $columns[$i]['label'] = $this->fromAliasList[$key];
                $i++;
            }
            $response['total']   = sizeof($result);
            $response['items']   = $result;
            $response['columns'] = $columns;
            $response['pages']   = 1;
            $response['page']    = 1;
            $response['request'] = $this->getSQLRequest();
        } else {
            $response['total']   = 0;
            $response['items']   = [];
            $response['columns'] = [];
            $response['pages']   = 1;
            $response['page']    = 1;
            $response['request'] = $this->getSQLRequest();;
        }
        return json_encode($response);
    }

    /**
     * Get the query columns list
     * @param bool $getTranslationName
     * @return array
     * @throws \Exception
     */
    public function getQueryColumns(bool $getTranslationName = false): array
    {
        if (null === $this->fields) {
            http_response_code(400);
            throw new \Exception('There is no query.');
        }
        $columns = [];
        foreach ($this->fields as $table => $rows) {
            foreach ($rows as $field) {
                $columns[] = ($getTranslationName && null !== $this->objDbConfig->{$table}->{$field}->{'_field_translation'})
                    ?
                    $this->objDbConfig->{$table}->{$field}->{'_field_translation'}
                    :
                    $table . '_' . $this->objDbConfig->{$table}->{$field}->{'name'};
            }
        }
        return $columns;
    }

    /**
     * @param bool $getTranslationName
     * @return string
     */
    public function getJsonQueryColumns(bool $getTranslationName = false): string
    {
        return json_encode($this->getQueryColumns($getTranslationName));
    }

    /**
     * Adding extra DQL and return the SQL request
     * @return string
     */
    public function getSQLRequest(): string
    {
        $sqlRequest = $this->queryBuilder->getDQL();
        $sqlRequest = null !== $this->limit && 0 !== $this->limit ? $sqlRequest . ' LIMIT ' . $this->limit : $sqlRequest;
        $sqlRequest = null !== $this->offset && 0 !== $this->limit ? $sqlRequest . ' OFFSET ' . $this->offset : $sqlRequest;
        return $sqlRequest;
    }

    /**
     * Reset DQL parts
     */
    private function resetSQLRequest()
    {
        $this->queryBuilder->resetDQLParts();
    }

    /**
     * Extract in XLS spreadsheet
     *
     * @param array $columns
     * @param array $data
     */
    public function spreadsheet(array $columns, array $data)
    {
        $spreadsheet = new Spreadsheet($columns, $data);
        $spreadsheet->setCreator('Eductive GROUP');
        $spreadsheet->setLastModifiedBy('Eductive GROUP');
        $spreadsheet->setSubject('Résultat requête');
        $spreadsheet->setTitle('Requête');
        $spreadsheet->generate('Excel5', 'resultat_' . date('YmdHis'));
    }
}