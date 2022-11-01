<?php
if (!isset($config))
    $config = include('../config.php');
if (!isset($mimeType))
    $mimeType = "application/json";
else
    error_reporting(E_ERROR | E_PARSE);

returnUpdateDataFormatted($config, $mimeType);

function returnUpdateDataFormatted($config, $mimeType) {
    if (!isset($config))
        $config = include('../config.php');
    if (!$mimeType)
        $mimeType = "application/json";

    $data = fopen('../logs/updatecheck.log', 'r');
    //error_reporting(E_ERROR | E_PARSE);

    $topAppCount = 10;
    $topDeviceCount = 10;
    $count = 0;
    $startDate = "";
    $lastDate = "";
    $apps = array();
    $appVersions = array();
    $devices = array();
    $uniqueClients = array();
    $uniqueDevices = array();
    $uniqueOSDevices = array();
    $osVersions = array();
    $clients = array();
    class UpdateApp
    {
        public $appName;
        public $count;
        public $appVersions;
        public $uniqueClients;
    }
    class AppVersion 
    {
        public $versionName;
        public $count;
    }
    class Device
    {
        public $deviceString;
        public $count;
        public $uniqueDevices;
    }
    class OSVersion
    {
        public $osVersionString;
        public $count;
        public $uniqueOSDevices;
    }
    class UpdateReport
    {
        public $firstDate;
        public $lastDate;
        public $totalChecks;
        public $uniqueClients;
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
                $appName = $lineParts[2];     //first non-date column is the app name
                $appParts = explode("/", $appName);
                $appName = $appParts[0];
                $appId = str_replace(" ", "", $appName);
                if (!array_key_exists($appName, $apps)) {
                    $apps[$appName] = 1;
                } else {
                    $apps[$appName] += 1;
                }
                if (count($appParts) > 1) {
                    $appVersion = $appParts[1];
                    //accumulate (or start) the count for this app version
                    if (!array_key_exists($appId, $appVersions)) {
                        $appVersions[$appId] = array();
                    }
                    $found = false;
                    foreach ($appVersions[$appId] as $key => $val) {
                        if ($val->versionName == $appVersion) {
                            //accumulate and break
                            $appVersions[$appId][$key]->count++;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {           
                        $thisVersion = new AppVersion();
                        $thisVersion->versionName = $appVersion;
                        $thisVersion->count = 1;
                        array_push($appVersions[$appId], $thisVersion);
                    }
                }

                /* Clients */
                $clientid = $lineParts[4];     //last column is the client identifier
                if (!in_array($clientid, $uniqueClients)) {
                    array_push($uniqueClients, $clientid);
                }
                //accumulate (or start) the client count for this app
                if (!array_key_exists($appId, $clients)) {
                    $clients[$appId] = array();
                }    
                if (!in_array($clientid, $clients[$appId])) {
                    array_push($clients[$appId], $clientid);
                }
                
                /* Devices */
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
                //accumulate unique device count by device name
                if (!array_key_exists($deviceName, $uniqueDevices)){
                    $uniqueDevices[$deviceName] = array($clientid);
                }
                else{
                    if (!in_array($clientid, $uniqueDevices[$deviceName]))
                        array_push($uniqueDevices[$deviceName], $clientid);
                }

                /* OS Version */
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
                //accumulate unique device account by os name
                if (!array_key_exists($osVersion, $uniqueOSDevices)){
                    $uniqueOSDevices[$osVersion] = array($clientid);
                }
                else{
                    if (!in_array($clientid, $uniqueOSDevices[$osVersion]))
                        array_push($uniqueOSDevices[$osVersion], $clientid);
                }
            }
        }
        $count++;
    }

    //format report object
    $updateReport = new UpdateReport();

    arsort($apps);  //sort apps descending by count
    $i = 1;
    foreach ($apps as $key => $val) {
        if ($i <= $topAppCount) {
            $thisApp = new UpdateApp();
            $thisApp->appName = $key;
            $appId = str_replace(" ", "", $key);
            $thisApp->appVersions = $appVersions[$appId];
            $thisApp->count = $val;
            $thisApp->uniqueClients = count($clients[$appId]);
            $updateReport->topApps[$i] = $thisApp;
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
            $thisDevice->uniqueDevices = count($uniqueDevices[$key]);
            $updateReport->topDevices[$i] = $thisDevice;
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
            $thisOS->uniqueOSDevices = count($uniqueOSDevices[$key]);
            $thisOS->uniqueOSDeviceList = $uniqueOSDevices[$key];
            $updateReport->topOSVersions[$i] = $thisOS;
            $i++;
        } else {
            break;
        }
    }
    $updateReport->firstDate = $startDate;
    $updateReport->lastDate = $lastDate;
    $updateReport->totalChecks = $count;
    $updateReport->uniqueClients = count($uniqueClients);
    $updateReport->uniqueClientDetails = $uniqueClients;

    //return report object as JSON
    header("Content-Type: " . $mimeType);
    echo(json_encode($updateReport));
}
?>