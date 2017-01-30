<?php
namespace Littlerobinson\QueryBuilder;

use Doctrine\ORM\Query\Expr;

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
            $this->from = (array)$queryObj->from;
            /// Get where conditions
            $this->where = (array)$queryObj->where;
            /// Get orderBy
            $this->orderBy = (array)$queryObj->orderBy;
            /// Array with FK
            $fkList = [];

            $dbConfig    = $this->doctrineDb->getDatabaseYamlConfig(true);
            $objDbConfig = json_decode($dbConfig);

            /// Create FK array
            $getFkList = function () use ($objDbConfig, &$fkList) {
                foreach ($this->from as $table => $fields) {
                    if (property_exists($objDbConfig->{$table}, '_FK')) {
                        $fkList[$table] = $objDbConfig->{$table}->_FK;
                    }
                }
            };
            $getFkList();

            foreach ($this->from as $fromTable => $fields) {
                /// Add From
                $this->queryBuilder->from($fromTable, $fromTable);
                /// Add Select
                foreach ($fields as $key => $field) {
                    if (is_object($field)) {
                        $this->queryBuilder->leftJoin($fkList[$fromTable]->{$key}->{'tableName'}, $fkList[$fromTable]->{$key}->{'tableName'}, 'ON', $fkList[$fromTable]->{$key}->{'tableName'} . '.' . $fkList[$fromTable]->{$key}->{'foreignColumns'} . ' = ' . $fromTable . '.' . $fkList[$fromTable]->{$key}->{'columns'});
                        foreach ($field as $objField) {
                            $this->queryBuilder->addSelect($fkList[$fromTable]->{$key}->{'tableName'} . '.' . $objField . ' AS ' . $fkList[$fromTable]->{$key}->{'tableName'} . '_' . $objField);
                        }
                    } else {
                        $this->queryBuilder->addSelect($fromTable . '.' . $field . ' AS ' . $fromTable . '_' . $field);
                    }
                }

                /// Search and add Join
                foreach ($fkList as $fkTable => $fkArr) {
                    foreach ($fkArr as $key => $fk) {
                        if ($fromTable === $fk->{'tableName'}) {
                            /// Add Join
                            $this->queryBuilder->leftJoin($fkTable, $key, 'ON', $fromTable . '.' . $fk->{'foreignColumns'} . ' = ' . $key . '.' . $fk->{'columns'});
                        }
                    }
                }
            }
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