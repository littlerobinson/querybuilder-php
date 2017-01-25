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

    private $from;

    private $where;

    private $orderBy;

    public function __construct()
    {
        $this->doctrineDb   = new DoctrineDatabase();
        $this->queryBuilder = $this->doctrineDb->getEntityManager()->createQueryBuilder();
    }

    public function prepare(string $jsonQuery)
    {
        try {
            /// Try to decode json
            $queryObj = json_decode($jsonQuery);
            /// Get From
            $this->from = $queryObj->from;
            /// Get where conditions
            $this->where = $queryObj->where;
            /// Get orderBy
            $this->orderBy = $queryObj->orderBy;
            /// Array with FK
            $fkList = [];

            $dbConfig    = $this->doctrineDb->getDatabaseYamlConfig(true);
            $objDbConfig = json_decode($dbConfig);

            /// Create FK array
            foreach ($this->from as $table => $fields) {
                if (property_exists($objDbConfig->{$table}, '_FK')) {
                    $fkList[$table] = $objDbConfig->{$table}->_FK;
                }
            }


            /// Create select Query
            foreach ($fkList as $table => $tableFK) {
                foreach ($tableFK as $key => $value) {
                    if ((isset($fkList[$value->tableName]))) {
                        array_filter(
                            $fkList,
                            function ($e) use (&$key, &$table) {
                                if (property_exists($e, $key)) {
                                    /// Add select
                                    foreach ($this->from->{$table} as $field) {
                                        $this->queryBuilder->addSelect($table . '.' . $field);
                                    }
                                    /// Add From
                                    $this->queryBuilder->from($e->{$key}->{'tableName'}, $e->{$key}->{'tableName'});
                                    /// Add Join
                                    $this->queryBuilder->leftJoin($table, $table, 'ON', $table . '.' . $e->{$key}->{'columns'} . ' = ' . $e->{$key}->{'tableName'} . '.' . $e->{$key}->{'foreignColumns'});
                                }
                            }
                        );
                    }
                }
            }
            var_dump($this->queryBuilder->getDQL());
            $result = $this->doctrineDb->getConnection()->executeQuery($this->queryBuilder->getDQL());
            var_dump($result->fetchAll());
            die;

            /// Adding query conditions
            foreach ($this->where as $logicalOperator => $request) {
                foreach ($request as $condition => $value) {
                    //if (property_exists($objDbConfig->{$table}, '_FK')) {
                    $arrRequest = explode('.', $condition);
                    /// Get field type
                    $fieldType = $objDbConfig->{$arrRequest[0]}->{$arrRequest[1]}->type;
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
            var_dump($result->fetchAll());
            die;
            echo '<hr>';
            echo '<hr>';
            var_dump($this->getQuery());
            echo '<hr>';
            foreach ($this->orderBy as $key => $field) {
                var_dump($key);
                var_dump($field);
                echo '<hr>';
            }
        } catch (\Exception $e) {
            http_response_code(400);
            echo 'ERROR : ' . $e->getMessage();
        }
    }

    /* ============================================================================================================== */
    /* ============================================== ACCESSORS ==================================================== */
    /* ============================================================================================================== */


}