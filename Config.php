<?php

class Config {

    private static $configuration = array();

    public static function retrieve($name) {
        if (!isset($configuration[$name])) {
            include 'DatabaseConfig.php';

            if (!isset($config[$name]))
                throw new Exception($name . ' config does not exist');

            self::$configuration[$name] = $config[$name];
        }

        return self::$configuration[$name];
    }
    
    public static function autoLoad($class) {
        echo "Autloaded looking for $class\n";
        if (class_exists($class, false))
            return true;

        // Split up the class name into logical parts
        // MUST BE CAMELCASE!
        $matches = preg_split('/([A-Z][^A-Z]+)/', $class, -1, PREG_SPLIT_NO_EMPTY |
                              PREG_SPLIT_DELIM_CAPTURE);
        
        $suffix = array_pop($matches);

        if ($suffix == 'Driver') {
            $type = 'drivers/';

            if (count($matches) == 1)
                $file = array_shift($matches) . '.php';
            elseif (count($matches) == 2)
                $file = array_shift($matches) . '/' . array_shift($matches) . '.php';
            else
                return false;
        }
        else {
            // Try to check if there is a file with the name of the class
            if (file_exists($class . '.php'))
                require($class . '.php');
            else
                return false;
        }

        // Check that the file exists
        if (!file_exists($type . $file))
            return false;

        require $type . $file;

        return true;
    }
}
