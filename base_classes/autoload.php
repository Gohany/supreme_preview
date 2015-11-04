<?php

function supreme_autoload($className)
{
        
        if (substr($className, -5) == 'Model')
        {
                foreach (dispatcher::$environments as $environment)
                {
                        if (strpos(strtolower($className), strtolower($environment)) === 0)
                        {
                                $model = lcfirst(substr($className, strlen($environment), (strlen($className) - strlen($environment) - 5)));
                                $file = $_SERVER['_HTDOCS_'] . '/environments/' . $environment . '/models/' . $model . '.php';
                                if (is_readable($file))
                                {
                                        require_once $file;
                                }
                        }
                }
        }
}
spl_autoload_register('supreme_autoload');
