<?php

class AetherDatabaseConfig {

    private static $configuration = array();

    /**
     *
     * @param string $name
     * @param bool $required
     */
    public static function retrieve($name, $required = true) {
        $group = explode('.', $name, 2);
        $group = $group[0];

        if (!isset(self::$configuration[$group])) {
            // Load the configuration group
            $file = dirname(__FILE__) . '/config/' . $group . '.php';
            

            if (!file_exists($file) && $required === true)
                throw new Exception("$file does not exists");
            elseif (!file_exists($file))
                return false;

            include($file);

            if (!isset($config) || empty($config))
                throw new Exception("Could not find config array in $file");
            
            self::$configuration[$group] = $config;
        }

        $value = self::retrieveKey(self::$configuration, $name);

        if ($required === true && ($value === NULL || empty($value)))
            throw new Exception("Could not find $name in $file");

        return $value;
    }

    private static function retrieveKey($array, $keys) {
        if (empty($array) || empty($keys))
            return NULL;
        
        $keys = explode('.', $keys);

        while(!empty($keys)) {
            $key = array_shift($keys);

            if (isset($array[$key])) {
                if (is_array($array[$key]) && !empty($keys)) {
                    // Dig down array to prepare the next loop
                    $array = $array[$key];
                } else {
                    return $array[$key];
                }
            } else {
                // Requested key was not found
                break;
            }
        }

        return NULL;
    }
    
    public static function autoLoad($class) {
        if (class_exists($class, false))
            return true;

        // Get this directory
        $dir = dirname(__FILE__) . '/';

        // Split up the class name into logical parts
        // MUST BE CAMELCASE!
        $matches = preg_split('/([A-Z][^A-Z]+)/', $class, -1, PREG_SPLIT_NO_EMPTY |
                              PREG_SPLIT_DELIM_CAPTURE);

        // We dont use the aether part to find the filename
        if ($matches[0] == 'Aether') {
            array_shift($matches);
            $class = implode($matches);
        }

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
            if (file_exists($dir . $class . '.php')) {
                require($dir . $class . '.php');
                return true;
            }
            else
                return false;
        }

        // Check that the file exists
        if (!file_exists($dir . $type . $file))
            return false;

        require $dir . $type . $file;

        return true;
    }
}
