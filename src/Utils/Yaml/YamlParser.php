<?php
namespace Littlerobinson\QueryBuilder\Utils\Yaml;

use Symfony\Component\Yaml\Yaml;

class YamlParser
{
    public function load($filePath) {
        try {
            return Yaml::parse($filePath);
        }
        catch (\Exception $e) {
            throw new YamlParserException(
                $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function dump($array) {
        try {
            return Yaml::dump($array);
        }
        catch (\Exception $e) {
            throw new YamlParserException(
                $e->getMessage(), $e->getCode(), $e);
        }
    }
}