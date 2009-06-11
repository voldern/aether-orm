<?php
/**
 * 
 * Example projectconfig for backend
 * 
 * Created: 2009-05-27
 * @author Simen Graaten
 * @package pg2.backend
 */

define("AETHER_PATH", "/home/sites/shared/aether/");
define("LIB_PATH", "/home/lib/");

define("PG_PATH", "/home/sites/shared/prisguide/");

$_AUTOLOAD_CONFIG = array(
    "path" => array(
        "Priceguide" => array(LIB_PATH . "priceguide"),
        "AetherModule" => array(PG_PATH . "modules"),
        "" => array(LIB_PATH, LIB_PATH . 'activerecord', PG_PATH . 'lib'),
    )
);

?>
