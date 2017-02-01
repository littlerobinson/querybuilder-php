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

    public function __construct()
    {
        $this->doctrineDb   = new DoctrineDatabase();
        $this->queryBuilder = $this->doctrineDb->getEntityManager()->createQueryBuilder();
        $dbConfig           = $this->doctrineDb->getDatabaseYamlConfig(true);
        $this->objDbConfig  = json_decode($dbConfig);
    }

    private function getFKList($fromObject)
    {
        $fkList = [];
        try {
            foreach ($fromObject as $table => $fields) {
                if (property_exists($this->objDbConfig->{$table}, '_FK')) {
                    $fkList[$table] = $this->objDbConfig->{$table}->_FK;
                }
            }
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            die;
        }
        return $fkList;
    }

    private function addSelect($fkList, $fromTable, $select)
    {
        foreach ($select as $key => $field) {
            if (is_object($field)) {
                foreach ($field as $fkName => $object) {
                    $newFromTable = $fkList[$fromTable]->{$fkName}->{'tableName'};
                    $newFrom      = array($newFromTable => $field->{$fkName});
                    $newfkList    = $this->getFKList($newFrom);
                    $this->queryBuilder->leftJoin($newFromTable, $newFromTable, 'ON', $newFromTable . '.' . $fkList[$fromTable]->{$fkName}->{'foreignColumns'} . ' = ' . $fromTable . '.' . $fkName);
                    $this->addSelect($newfkList, $newFromTable, $object);
                }
            } else {
                $this->queryBuilder->addSelect(
                    $fromTable . '.' .
                    $field . ' AS ' .
                    $fromTable . '_' .
                    $field);
            }
        }
    }

    public function prepare(string $jsonQuery)
    {
        try {
            /// Try to decode json
            $queryObj = json_decode($jsonQuery);
            /// Get From
            $this->from = (array)$queryObj->from;
            /// Get where conditions
            $this->where = (array)$queryObj->where;
            /// Get orderBy
            $this->orderBy = (array)$queryObj->orderBy;

            /// Create FK array
            $fkList = $this->getFKList($this->from);

            foreach ($this->from as $fromTable => $select) {
                /// Add From
                $this->queryBuilder->from($fromTable, $fromTable);
                /// Add Select
                $this->addSelect($fkList, $fromTable, $select);
            }


            /// Adding query conditions
            foreach ($this->where as $logicalOperator => $request) {
                foreach ($request as $condition => $value) {
                    //if (property_exists($objDbConfig->{$table}, '_FK')) {
                    $arrRequest = explode('.', $condition);
                    /// Get field type
                    $fieldType = $this->objDbConfig->{$arrRequest[0]}->{$arrRequest[1]}->type;
                    /// Add comma if not boolean or integer
                    $value = ($fieldType === 'integer' || $fieldType === 'boolean') ? implode(',', $value->EQUAL) : implode(',', $value->EQUAL);

                    switch ($logicalOperator) {
                        case 'AND':
                            $this->queryBuilder->andWhere($condition . ' = ' . '\'' . $value . '\'');
                            break;
                        case 'OR':
                            $this->queryBuilder->orWhere($condition . ':' . $i);
                            $this->queryBuilder->setParameter($i, $value);
                            break;
                        case 'AND_HAVING':
                            $this->queryBuilder->andHaving($condition . ':' . $i);
                            $this->queryBuilder->setParameter($i, $value);
                            break;
                        case 'OR_HAVING':
                            $this->queryBuilder->orHaving($condition . ':' . $i);
                            $this->queryBuilder->setParameter($i, $value);
                            break;
                        default:
                            break;
                    }
                }
            }

            var_dump($this->queryBuilder->getDQL());
            $result = $this->doctrineDb->getConnection()->executeQuery($this->queryBuilder->getDQL());
            var_dump($result->fetchAll(\PDO::FETCH_ASSOC));
            die();
        } catch (\Exception $e) {
            http_response_code(400);
            echo 'ERROR : ' . $e->getMessage();
        }
    }


    /* ============================================================================================================== */
    /* ============================================== ACCESSORS ==================================================== */
    /* ============================================================================================================== */


}