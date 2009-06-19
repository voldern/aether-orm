<?php

/**
 * Run all the tests
 *
 * Created: 2009-04-23
 * @author Espen Volden
 */

$dir = dirname(__FILE__);
chdir($dir . "/tests");
system("phpunit AllTests.php", $exitCode);
exit($exitCode);
