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

    private $fkList;

    /**
     * QueryBuilderDoctrine constructor.
     */
    public function __construct()
    {
        $this->doctrineDb   = new DoctrineDatabase();
        $this->queryBuilder = $this->doctrineDb->getEntityManager()->createQueryBuilder();
        $dbConfig           = $this->doctrineDb->getDatabaseYamlConfig(true);
        $this->objDbConfig  = json_decode($dbConfig);
    }

    /**
     * Return an array with all foreign keys in show config file
     * @param $fromObject
     * @throws \Exception
     * @return array
     */
    private function getFKList($fromObject): array
    {
        $fkList = [];
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
    private function addQuerySelect($fkList, $fromTable, $select): void
    {
        foreach ($select as $key => $field) {
            if (is_object($field)) {
                /// Add joins
                foreach ($field as $fkName => $object) {
                    if (!isset($fkList[$fromTable]->{$fkName}->{'tableName'})) {
                        http_response_code(400);
                        throw new \Exception('This foreign key not exist : ' . $fkName . '.');
                    }
                    $newFromTable = $fkList[$fromTable]->{$fkName}->{'tableName'};
                    $newFrom      = array($newFromTable => $field->{$fkName});
                    $newfkList    = $this->getFKList($newFrom);
                    $this->queryBuilder->leftJoin($newFromTable, $newFromTable, 'ON', $newFromTable . ' . ' . $fkList[$fromTable]->{$fkName}->{'foreignColumns'} . ' = ' . $fromTable . ' . ' . $fkName);
                    $this->addQuerySelect($newfkList, $newFromTable, $object);
                }
            } else {
                /// Exit if there is no visibility on the table in config file
                if ($this->objDbConfig->{$fromTable}->{'_table_visibility'} === false) {
                    continue;
                }
                /// Exit if there is no visibility on the field in config file
                if ($this->objDbConfig->{$fromTable}->{$field}->{'_field_visibility'} === false) {
                    continue;
                }
                $this->queryBuilder->addSelect(
                    $fromTable . ' . ' .
                    $field . ' AS ' .
                    $fromTable . '_' .
                    $field);
            }
        }
    }

    /**
     * Add query conditions
     */
    private function addQueryCondition(): void
    {
        if ($this->where === null) {
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
                        $this->queryBuilder->andWhere($condition . ' = ' . '\'' . $value . '\'');
                        break;
                    case 'OR':
                        $this->queryBuilder->orWhere($condition . ' = ' . '\'' . $value . '\'');
                        break;
                    case 'AND_HAVING':
                        $this->queryBuilder->andHaving($condition . ' = ' . '\'' . $value . '\'');
                        break;
                    case 'OR_HAVING':
                        $this->queryBuilder->orHaving($condition . ' = ' . '\'' . $value . '\'');
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
    private function prepareJsonQuery(string $jsonQuery): void
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
        $this->fkList = (property_exists($queryObj, 'from')) ? $this->getFKList($this->from) : null;
    }

    /**
     * Execute the query
     * @param string $jsonQuery
     * @return array
     */
    public function executeQuery(string $jsonQuery): ?array
    {
        /// Prepare query
        $this->prepareJsonQuery($jsonQuery);

        /// Loop on tables
        foreach ($this->from as $fromTable => $select) {
            /// Add From
            $this->queryBuilder->from($fromTable, $fromTable);
            /// Add Select
            $this->addQuerySelect($this->fkList, $fromTable, $select);
        }

        /// Adding query conditions
        $this->addQueryCondition();

        /// Execute query and fetch result
        $result = $this->doctrineDb->getConnection()->executeQuery($this->queryBuilder->getDQL());
        return $result->fetchAll(\PDO::FETCH_ASSOC);
    }
}