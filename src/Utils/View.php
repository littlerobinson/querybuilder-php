<?php

namespace Littlerobinson\QueryBuilder\Utils;

class View
{
    private $data = array();

    private $render = FALSE;

    public function __construct($template)
    {
        try {
            $file = __DIR__ . '/../../web/' . strtolower($template) . '.php';

            if (file_exists($file)) {
                $this->render = $file;
            } else {
                throw new \Exception('Template ' . $template . ' not found!');
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function assign($variable, $value)
    {
        $this->data[$variable] = $value;
    }

    public function __destruct()
    {
        extract($this->data);
        include($this->render);
    }
}