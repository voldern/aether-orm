<?php

class Inflector {

    // Cached inflections
    protected static $cache = array();

    // Uncountable and irregular words
    protected static $uncountable;
    protected static $irregular;

    /**
     * Checks if a word is defined as uncountable.
     *
     * @param string $str word to check
     * @return boolean
     */
    public static function uncountable($str) {
        if (self::$uncountable === NULL) {
            // Cache uncountables
            self::$uncountable =
                AetherDatabaseConfig::retrieve('inflector.uncountable');

            // Make uncountables mirroed
            self::$uncountable = array_combine(self::$uncountable,
                                               self::$uncountable);
        }

        return isset(self::$uncountable[strtolower($str)]);
    }

    /**
     * Makes a plural word singular.
     *
     * @param string $str word to singularize
     * @param integer $count number of things
     * @return string
     */
    public static function singular($str, $count = NULL) {
        // Remove garbage
        $str = strtolower(trim($str));

        if (is_string($count)) {
            // Convert to integer when using a digit string
            $count = (int)$count;
        }

        // Do nothing with a single count
        if ($count === 0 || $count > 1)
            return $str;

        // Cache key name
        $key = 'singular_' . $str . $count;

        if (isset(self::$cache[$key]))
            return self::$cache[$key];

        if (self::uncountable($str))
            return self::$cache[$key] = $str;

        if (empty(self::$irregular)) {
            // Cache irregular words
            self::$irregular =
                AetherDatabaseConfig::retrieve('inflector.irregular');
        }

        if ($irregular = array_search($str, self::$irregular)) {
            $str = $irregular;
        }
        elseif (preg_match('/[sxz]es$/', $str) ||
                preg_match('/[^aeioudgkprt]hes$/', $str)) {
            // Remove "es"
            $str = substr($str, 0, -2);
        }
        elseif (preg_match('/[^aeiou]ies$/', $str)) {
            $str = substr($str, 0, -3).'y';
        }
        elseif (substr($str, -1) === 's' && substr($str, -2) !== 'ss') {
            $str = substr($str, 0, -1);
        }

        return self::$cache[$key] = $str;
    }

    /**
     * Makes a singular word plural.
     *
     * @param string $str word to pluralize
     * @param int $count
     * @return string
     */
    public static function plural($str, $count = NULL) {
        // Remove garbage
        $str = strtolower(trim($str));

        if (is_string($count)) {
            // Convert to integer when using a digit string
            $count = (int)$count;
        }

        // Do nothing with singular
        if ($count === 1)
            return $str;

        // Cache key name
        $key = 'plural_' . $str . $count;

        if (isset(self::$cache[$key]))
            return self::$cache[$key];

        if (self::uncountable($str))
            return self::$cache[$key] = $str;

        if (empty(self::$irregular)) {
            // Cache irregular words
            self::$irregular =
                AetherDatabaseConfig::retrieve('inflector.irregular');
        }

        if (isset(self::$irregular[$str])) {
            $str = self::$irregular[$str];
        }
        elseif (preg_match('/[sxz]$/', $str) ||
                preg_match('/[^aeioudgkprt]h$/', $str)) {
            $str .= 'es';
        }
        elseif (preg_match('/[^aeiou]y$/', $str)) {
            // Change "y" to "ies"
            $str = substr_replace($str, 'ies', -1);
        }
        else {
            $str .= 's';
        }

        // Set the cache and return
        return self::$cache[$key] = $str;
    }

    /**
     * Makes a phrase camel case.
     *
     * @param string $str phrase to camelize
     * @return string
     */
    public static function camelize($str) {
        $str = 'x' . strtolower(trim($str));
        $str = ucwords(preg_replace('/[\s_]+/', ' ', $str));

        return substr(str_replace(' ', '', $str), 1);
    }

    /**
     * Makes a phrase underscored instead of spaced.
     *
     * @param   string  phrase to underscore
     * @return  string
     */
    public static function underscore($str) {
        return preg_replace('/\s+/', '_', trim($str));
    }

    /**
     * Makes an underscored or dashed phrase human-reable.
     *
     * @param   string  phrase to make human-reable
     * @return  string
     */
    public static function humanize($str) {
        return preg_replace('/[_-]+/', ' ', trim($str));
    }

} // End inflector