#!/usr/bin/php -q
<?php
/**
 *
 * Download pricelists from shops to local dir
 *
 * @author Simen Graaten
 */

require_once("/home/lib/Autoload.php");
require_once(LIB_PATH . "time/Time.lib.php");
require_once(LIB_PATH . "product/Shop.lib.php");

$pgdb = new Database("prisguide");

// Make sure connection attepts don't last too long
ini_set('default_socket_timeout', 300);
// Shouldn't take ages to download some pricelists.
ini_set("max_execution_time", 1800);
ini_set("memory_limit", "512M");
error_reporting(E_ERROR | E_PARSE | E_WARNING);

echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

// define directories
$minute = date('Hi');
$pricelist_path_orig = PG_PATH . "/pricelists/original/" . date('Y') . "/" . 
        date('m') . "/" . date('d') . "/" . $minute . "/";

// verify that all directories are created
if (!is_dir($pricelist_path_orig) && !mkdir($pricelist_path_orig, 0775, true)) {
    // this works also as a check if the user has the right privileges:
    echo "You do not have permissions to access this script.";
    echo "Run the script as prisguide-user";
    exit(1);
}

$options = getopt("hs:");

if (isset($options['h'])) {
    echo "{$argv[0]} [-s <shopId/shopName>]";
    exit(1);
}

// if specific shop, set shopId from commandline args
if (isset($options['s'])) {
    if (!is_array($options['s'])) 
        $options['s'] = array($options['s']);

    $lookupShops = array();
    $updateShopIds = array();
    foreach ($options['s'] as $shop) {
        if (is_numeric($shop)) {
            $updateShopIds[] = $shop;
        }
        else {
            $shopId = $db->queryValue(sprintf("
                SELECT shop_id FROM shops WHERE shop_name LIKE '%.2s%%'
            ", $db->escapeString($shop)));
            if ($shopId) {
                $updateShopIds[] = $shopId;
            }
            else {
                echo "Didn't find shop $shop\n";
                exit(1);
            }
        }
    }
}

$sql = "SELECT shop_id FROM shops WHERE shop_pricefeed_active = 1";
// use specific shop_id if specified
if (isset($updateShopIds)) {
    $sql .= " AND shop_id IN " . join(", ", $updateShopIds);
}
$shopIds = $pgdb->query($sql);

$shopTotal = array();
$shopTotal['start'] = Time2::getMicroTime();

$url = new URLDownloader();

foreach ($shopIds as $s) {
    $shop = new Shop($s['shop_id']);

    $charset = ($shop->shopPricefeedUtf8) ? "UTF-8" : "ISO-8859-1";
    echo "Processing [{$shop->shopId}] {$shop->shopUsername} {$shop->shopPricefeedUrl} [{$charset}]\n";
    
    if (!empty($shop->shopUsername)) {
        $pricelist_original = $pricelist_path_orig . strtolower($shop->shopUsername) . ".txt";
    }
    else {
        $pricelist_original = $pricelist_path_orig . $shop->shopId . ".txt";
    }


    $pid = pcntl_fork();
    
    if ($pid == -1) {
        print "Fork call failed, something fishy is about.\n";
        continue;
    } else if ($pid) {
        $shopStatus[$pid] = array();
        $shopStatus[$pid]['start'] = Time2::getMicroTime();
        $shopStatus[$pid]['shopId'] = $shop->shopId;
        $children[$pid] = $pid;

        // Be a little nice and not start every download at the same time
        if (count($children) % 10 == 0)
            sleep(1);

    } else {
        $data = $url->fetch($shop->shopPricefeedUrl);
        if (isset($data['httpCode'])) {
            logFetch($shop, $pricelist_original, $data);
        }
        if (isset($data['error'])) {
            logFetchError($shop, $pricelist_original, $data['error']);
        }

        $outfp = fopen($pricelist_original, "w+");
        if (!$outfp) {
            echo "FATAL ERROR: Cannot create outputfile {$pricelist_original}\n";
            exit(1);
        }
        if (fwrite($outfp, $data['response']) === false) {
            echo "FATAL ERROR: Couldn't write to file: {$pricelist_original}, disk full?\n";
            exit(1);
        }
        exit(0);
    }
 
}

$waitingForChildren = true;
// Wait for all our children to finish what they're doing
while (count($children) > 0) {
    $pid = pcntl_wait($status, WNOHANG); 
    sleep(1);

    if ($pid > 0) {
        $shopStatus[$pid]['end'] = Time2::getMicroTime();
        unset($children[$pid]);
    }

    if (Time2::getMicroTime() - $shopTotal['start'] > $scriptTimeout) {
        echo "Downloader timed out after {$scriptTimeout}s\n";
        echo "Didn't finish download for the following shopIds: ";
        foreach ($children as $pid) {
            echo $shopStatus[$pid]['shopId'] . " ";
        }
        echo "\n";
        $children = array();
    }
}

$shopTotal['end'] = Time2::getMicroTime();


foreach ($shopStatus as $pid => $ss) {
    echo "Downloadtime shopid {$ss['shopId']}: " . round($ss['end'] - $ss['start'], 1) . "s\n";
}

echo "Total downloadtime: " . round($shopTotal['end'] - $shopTotal['start'], 1) . "s\n";

function logFetchError($shop, $pricelist_original, $error) {
    $pgdb = new Database("prisguide", true);
    $pgdb->queryf("
        INSERT INTO
            pricelist_log
        SET
            shop_id = ?,
            original_datetime = ?,
            original_path = ?,
            error_code = ?,
            error_message = ?
    ", $shop->shopId, date('Y-m-d H:i:s'), $pricelist_original, $error['id'], 
        $error['message']);
}

function logFetch($shop, $pricelist_original, $data) {
    $pgdb = new Database("prisguide", true);
    $pgdb->queryf("
        INSERT INTO 
            pricelist_log 
        SET
            shop_id = ?,
            original_datetime = ?,
            original_path = ?,
            http_code = ?,
            size_download = ?,
            speed_download = ?
    ", $shop->shopId, date('Y-m-d H:i:s'), $pricelist_original, 
        $data['httpCode'], $data['sizeDownload'], $data['speedDownload']);
    $pgdb->query($sql);
}
?>
