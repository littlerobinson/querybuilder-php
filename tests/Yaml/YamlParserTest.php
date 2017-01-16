<?php
namespace Littlerobinson\QuerybuilderDoctrine\Tests;

use Littlerobinson\QuerybuilderDoctrine\Utils\Yaml\YamlParser;

class YamlParserTest extends \PHPUnit_Framework_TestCase
{
    private $yamlParser;

    public function setup()
    {
        $this->yamlParser = new YamlParser();
    }

    public function testMainArrayKeys()
    {
        $parsedYaml    = $this->yamlParser->load(file_get_contents('tests/Yaml/good-syntax.yml'));
        $mainArrayKeys = array_keys($parsedYaml);
        $expectedKeys  = array('author', 'category', 'article', 'articleCategory');

        $this->assertEquals($expectedKeys, $mainArrayKeys);

    }

    public function testSecondLevelElement()
    {
        $parsedYaml    = $this->yamlParser->load(file_get_contents('tests/Yaml/good-syntax.yml'));
        $actualArticle = $parsedYaml['article'][0];
        $title         = 'How to Use YAML in Your Next PHP Project';
        $content       = 'YAML is a less-verbose data serialization format.';

        $expectedArticle = array('id' => 1, 'title' => $title, 'content' => $content, 'author' => 1, 'status' => 2);

        $this->assertEquals($expectedArticle, $actualArticle);
    }

    /**
     * @expectedException Littlerobinson\QuerybuilderDoctrine\Utils\Yaml\YamlParserException
     */
    public function testExceptionForWrongSyntax()
    {
        $this->yamlParser->load(file_get_contents('tests/Yaml/wrong-syntax.yml'));
    }
}