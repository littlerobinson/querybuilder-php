<?php
namespace Littlerobinson\QueryBuilder\Tests;

use Doctrine\DBAL\Connection;
use Littlerobinson\QueryBuilder\DoctrineDatabase;
use Symfony\Component\Yaml\Yaml;

class DoctrineDatabaseTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var DoctrineDatabase
     */
    private $db;

    private $connection;

    private $configPath;


    public function setup()
    {
        $this->configPath       = __DIR__ . '/config/test-database-config.yml';
        $this->db         = new DoctrineDatabase($this->configPath);
        $this->connection = $this->db->getConnection();
    }

    public function testGetConnection()
    {
        $this->assertInstanceOf(Connection::class, $this->connection);
    }

    public function testGetJsonConfig()
    {
        $this->assertJson($this->db->getJsonDatabaseConfig());
    }

    public function testGetListTables()
    {
        $listTables = $this->db->getTables();
        $this->assertContains('registration', $listTables);
        $this->assertContains('registrant', $listTables);
    }

    public Function testWriteDatabaseYamlConfig()
    {
        $this->assertEquals(true, $this->db->writeDatabaseYamlConfig());

        $currentConfig = Yaml::parse(file_get_contents($this->configPath));
        $this->assertEquals('Replacement secu', $currentConfig['affiliation_social_security']['_table_translation']);
        $this->assertEquals('Replacement field id', $currentConfig['affiliation_social_security']['id']['_field_translation']);
    }

}