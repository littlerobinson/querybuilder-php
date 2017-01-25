<?php
namespace Littlerobinson\QueryBuilder\Utils\Yaml;

class YamlParserException extends \Exception
{
    public function __construct($message = "", $code = 0, $previous = NULL)
    {
        if (version_compare(PHP_VERSION, "5.3.0") < 0) {
            parent::__construct($message, $code);
        } else {
            parent::__construct($message, $code, $previous);
        }
    }
}