<?php
namespace Littlerobinson\QueryBuilder\Tests;

use Littlerobinson\QueryBuilder\DoctrineDatabase;
use Littlerobinson\QueryBuilder\QueryBuilderDoctrine;

class QueryBuilderDoctrineTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DoctrineDatabase
     */
    private $db;

    /**
     * @var QueryBuilderDoctrine
     */
    private $qb;

    private $connection;


    public function setup()
    {
        $configPath       = __DIR__ . '/config/test-database-config.yml';
        $this->db         = new DoctrineDatabase($configPath);
        $this->connection = $this->db->getConnection();
        $this->qb         = new QueryBuilderDoctrine($this->db);
    }

    public Function testGoodJsonQuery()
    {
        $jsonQuery = file_get_contents(__DIR__ . '/Json/qb-good-syntax.json');
        $result    = $this->qb->executeQuery($jsonQuery);
        $this->assertInternalType('array', $result);
    }

    /**
     * @expectedException \Exception
     */
    public Function testBadFromJsonQuery()
    {
        $jsonQuery = file_get_contents(__DIR__ . '/Json/qb-bad-from-syntax.json');
        $result    = $this->qb->executeQuery($jsonQuery);
    }

    /**
     * @expectedException \Exception
     */
    public Function testBadSelectJsonQuery()
    {
        $jsonQuery = file_get_contents(__DIR__ . '/Json/qb-bad-select-syntax.json');
        $result    = $this->qb->executeQuery($jsonQuery);
    }

    /**
     * @expectedException \Exception
     */
    public Function testBadConditionJsonQuery()
    {
        $jsonQuery = file_get_contents(__DIR__ . '/Json/qb-bad-condition-syntax.json');
        $result    = $this->qb->executeQuery($jsonQuery);
    }


    public Function testTableVisibility()
    {
        $jsonQuery = file_get_contents(__DIR__ . '/Json/qb-good-syntax.json');
        $result    = $this->qb->executeQuery($jsonQuery);
        $this->assertArrayNotHasKey('user_id', $result[0]);
    }

    public Function testFieldVisibility()
    {
        $jsonQuery = file_get_contents(__DIR__ . '/Json/qb-good-syntax.json');
        $result    = $this->qb->executeQuery($jsonQuery);
        $this->assertArrayNotHasKey('country_id', $result[0]);
    }

}