<?php

namespace Littlerobinson\QueryBuilder;

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

    private $fkFrom;

    private $fields;

    private $queryResult;

    /**
     * QueryBuilderDoctrine constructor.
     * @param DoctrineDatabase $doctrineDb
     */
    public function __construct(DoctrineDatabase $doctrineDb)
    {
        $this->doctrineDb   = $doctrineDb;
        $this->queryBuilder = $this->doctrineDb->getEntityManager()->createQueryBuilder();
        $dbConfig           = $this->doctrineDb->getDatabaseYamlConfig(true);
        $this->objDbConfig  = json_decode($dbConfig);
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
     * @throws \Exception
     */
    private function addQuerySelect($fkList, $fromTable, $select)
    {
        $fromColumnsAlias = $fromTable . '_' . $this->objDbConfig->{$fromTable}->{'primary_key'};
        foreach ($select as $key => $field) {
            if (is_object($select) && is_object($field)) {
                /// Add joins
                if (is_object($field)) {
                    //if (!isset($fkList[$fromTable]->{$fkName}->{'tableName'})) {
                    if (!isset($fkList[$fromTable]->{$key}->{'tableName'})) {
                        http_response_code(400);
                        throw new \Exception('This foreign key not exist : ' . $key . '.');
                    }
                    $newFromTable        = $fkList[$fromTable]->{$key}->{'tableName'};
                    $newFrom             = array($newFromTable => $field);
                    $newfkList           = $this->getFKList($newFrom);
                    $columnsAlias        = $fkList[$fromTable]->{$key}->{'columns'};
                    $foreignColumnsAlias = $fromTable . '_' . $fkList[$fromTable]->{$key}->{'foreignColumns'};
                    $this->queryBuilder->leftJoin($newFromTable, $columnsAlias, 'ON', $columnsAlias . ' . ' . $fkList[$fromTable]->{$key}->{'foreignColumns'} . ' = ' . $foreignColumnsAlias . ' . ' . $fkList[$fromTable]->{$key}->{'columns'});
                    $this->addQuerySelect($newfkList, $newFromTable, $field);
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

                /// Feed $fields
                $this->fields[$fromTable][] = $field;

                $this->queryBuilder->addSelect(
                    $fromColumnsAlias . ' . ' .
                    $field . ' AS ' .
                    $fromTable . '_' .
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
        foreach ($this->where as $logicalOperator => $request) {
            foreach ($request as $condition => $value) {
                //if (property_exists($objDbConfig->{$table}, '_FK')) {
                $arrRequest = explode('.', $condition);

                if (!isset($this->objDbConfig->{$arrRequest[0]}->{$arrRequest[1]}->type)) {
                    http_response_code(400);
                    throw new \Exception('This field not exist : ' . $arrRequest[0] . '.' . $arrRequest[1]);
                }
                /// Get field type
                $fieldType = $this->objDbConfig->{$arrRequest[0]}->{$arrRequest[1]}->type;
                /// Add comma if not boolean or integer
                $value = ($fieldType === 'integer' || $fieldType === 'boolean') ? implode(',', $value->EQUAL) : implode(',', $value->EQUAL);

                switch ($logicalOperator) {
                    case 'AND':
                        $this->queryBuilder->andWhere($arrRequest[1] . '.' . $arrRequest[2] . ' = ' . '\'' . $value . '\'');
                        break;
                    case 'OR':
                        $this->queryBuilder->orWhere($arrRequest[1] . '.' . $arrRequest[2] . ' = ' . '\'' . $value . '\'');
                        break;
                    case 'AND_HAVING':
                        $this->queryBuilder->andHaving($arrRequest[1] . '.' . $arrRequest[2] . ' = ' . '\'' . $value . '\'');
                        break;
                    case 'OR_HAVING':
                        $this->queryBuilder->orHaving($arrRequest[1] . '.' . $arrRequest[2] . ' = ' . '\'' . $value . '\'');
                        break;
                    default:
                        break;
                }
            }
        }
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
            $fromAlias = $fromTable . '_' . $this->objDbConfig->{$fromTable}->{'primary_key'};
            /// Add From
            $this->queryBuilder->from($fromTable, $fromAlias);
            /// Add Select
            $this->addQuerySelect($this->fkFrom, $fromTable, $select);
        }

        /// Adding query conditions
        $this->addQueryCondition();

        /// Execute query and fetch result
        $result            = $this->doctrineDb->getConnection()->executeQuery($this->queryBuilder->getDQL());
        $this->queryResult = $result->fetchAll(\PDO::FETCH_ASSOC);

        return $this->queryResult;
    }

    /**
     * Execute the query and return json
     * @param string $jsonQuery
     * @return string
     */
    public function executeQueryJson(string $jsonQuery): string
    {
        $result            = $this->executeQuery($jsonQuery);
        $response['total'] = sizeof($result);
        $response['items'] = $result;

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
     * Return the SQL request
     * @return string
     */
    public function getSQLRequest(): string
    {
        return $this->queryBuilder->getDQL();
    }

    /**
     * Reset DQL parts
     */
    private function resetSQLRequest()
    {
        $this->queryBuilder->resetDQLParts();
    }
}