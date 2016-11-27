<?php


class Autoload{


    public static function loadClass($name)
    {
        $class_path = str_replace('\\', DIRECTORY_SEPARATOR, $name);
        
        $file = __DIR__.DIRECTORY_SEPARATOR.$class_path.'.php';

        if (file_exists($file)){
            require_once($file);

            if (class_exists($name, false)){

                return true;
            }
        }

        return false;
    }

}

spl_autoload_register('\Autoload::loadClass');