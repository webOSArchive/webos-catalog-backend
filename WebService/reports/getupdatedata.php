<?php
$config = include('../config.php');
$data = fopen('../logs/updatecheck.log', 'r');

$topAppCount = 15;
$topDeviceCount = 15;
$count = 0;
$startDate = "";
$lastDate = "";
$apps = array();
$appVersions = array();
$devices = array();
$osVersions = array();
$clients = array();
class App
{
    public $appName;
    public $count;
    public $appVersions;
    public $uniqueClients;
}
class Device
{
    public $deviceString;
    public $count;
}
class OSVersion
{
    public $osVersionString;
    public $count;
}
class DownloadReport
{
    public $firstDate;
    public $lastDate;
    public $totalChecks;
    public $topApps = array();
    public $topDevices = array();
    public $topOSVersions = array();
}

//get the log data
while($line = fgets($data)) {
    $line = str_replace("\n", "", $line);
    $line = stripcslashes($line);
    if ($count > 0) {   //skip first line
        $lineParts = explode(",", $line);
        if (count($lineParts) > 3) {
            if ($count == 1) {  //the first item has our earliest date
                $startDate = $lineParts[0];
            }
            $lastDate = $lineParts[0];  //every subsequent item has the latest date (so far)
            
            //accumulate (or start) the update check count for this app
            $appid = $lineParts[2];     //first non-date column is the app id
            $appParts = explode("/", $appid);
            $appid = $appParts[0];
            if (!array_key_exists($appid, $apps)) {
                $apps[$appid] = 1;
            } else {
                $apps[$appid] += 1;
            }
            if (count($appParts) > 1) {
                $appVersion = $appParts[1];
                //accumulate (or start) the count for this app version
                if (!array_key_exists($appid, $appVersions)) {
                    $appVersions[$appid] = array();
                }
                if (!in_array($appVersion, $appVersions[$appid])) { 
                    array_push($appVersions[$appid], $appVersion);
                }
            }

            $deviceString = stripcslashes($lineParts[3]);     //this column is device string
            $deviceString = str_replace("//", "/", $deviceString);
            $deviceString = explode("/", $deviceString);
            $deviceName = $deviceString[0];
            //accumulate (or start) the count for this device
            if (!array_key_exists($deviceName, $devices)) {
                $devices[$deviceName] = 1;
            } else {
                $devices[$deviceName] += 1;
            }

            if ($deviceName != "Mozilla") { //  Enyo 2 behaves differently
                $osVersion = $deviceString[1];   
            } 
            else {
                $osVersion = $deviceString[2];
                $osVersion = explode(";", $osVersion);
                $osVersion = $osVersion[0];
            }
            //accumulate (or start) the count for this device version
            if (!array_key_exists($osVersion, $osVersions)) {
                $osVersions[$osVersion] = 1;
            } else {
                $osVersions[$osVersion] += 1;
            }

            //accumulate (or start) the client count for this app
            $clientid = $lineParts[4];     //last column is the client identifier
            if (!array_key_exists($appid, $clients)) {
                $clients[$appid] = array();
            }    
            if (!in_array($clientid, $clients[$appid])) {
                array_push($clients[$appid], $clientid);
            }
        }
    }
    $count++;
}

//format report object
$downloadReport = new DownloadReport();

arsort($apps);  //sort apps descending by count
$i = 1;
foreach ($apps as $key => $val) {
    if ($i <= $topAppCount) {
        $thisApp = new App();
        $thisApp->appName = $key;
        if ($appVersions[$key])
            $thisApp->appVersions = $appVersions[$key];
        $thisApp->count = $val;
        $thisApp->uniqueClients = count($clients[$key]);
        $downloadReport->topApps[$i] = $thisApp;
        $i++;
    } else {
        break;
    }
}

arsort($devices);  //sort device types descending by count
$i = 1;
foreach ($devices as $key => $val) {
    if ($i <= $topDeviceCount) {
        $thisDevice = new Device();
        $thisDevice->deviceString = $key;
        $thisDevice->count = $val;
        $downloadReport->topDevices[$i] = $thisDevice;
        $i++;
    } else {
        break;
    }
}

arsort($osVersions);  //sort os versions descending by count
$i = 1;
foreach ($osVersions as $key => $val) {
    if ($i <= $topDeviceCount) {
        $thisOS = new OSVersion();
        $thisOS->osVersionString = $key;
        $thisOS->count = $val;
        $downloadReport->topOSVersions[$i] = $thisOS;
        $i++;
    } else {
        break;
    }
}
$downloadReport->firstDate = $startDate;
$downloadReport->lastDate = $lastDate;
$downloadReport->totalChecks = $count;

//return report object as JSON
header('Content-Type: application/json');
echo(json_encode($downloadReport));
?>