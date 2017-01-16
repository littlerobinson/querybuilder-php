<?php
namespace Littlerobinson\QuerybuilderDoctrine\Tests;

use Doctrine\DBAL\Connection;
use Littlerobinson\QuerybuilderDoctrine\DoctrineDatabase;
use Symfony\Component\Yaml\Yaml;

class DoctrineDatabaseTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var DoctrineDatabase
     */
    private $db;

    private $connection;


    public function setup()
    {
        $this->db         = new DoctrineDatabase();
        $this->connection = $this->db->getConnexion();
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
        $configPath = __DIR__ . '/config/existing-database-config.yml';
        $this->assertEquals(true, $this->db->writeDatabaseYamlConfig($configPath));

        $currentConfig = Yaml::parse(file_get_contents($configPath));
        $this->assertEquals('Replacement secu', $currentConfig['affiliation_social_security']['_table_traduction']);
        $this->assertEquals('Replacement field id', $currentConfig['affiliation_social_security']['id']['_field_traduction']);
    }

}