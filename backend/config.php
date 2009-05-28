<?php
/**
 * 
 * Example projectconfig for backend
 * 
 * Created: 2009-05-27
 * @author Simen Graaten
 * @package pg2.backend
 */

define("AETHER_PATH", "/home/simeng/aether/");
define("LIB_PATH", "/home/simeng/lib/");

define("PG_PATH", "/home/simeng/public_html/pg2/backend");

$_AUTOLOAD_CONFIG = array(
    "path" => array(
        "Priceguide" => array("/home/simeng/lib/priceguide"),
        "AetherModule" => array("/home/simeng/public_html/pg2/backend/modules"),
        "" => array("/home/simeng/lib", "/home/simeng/lib/article/new2"),
    )
);

?>
