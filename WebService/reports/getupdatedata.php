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

    $topAppCount = 20;
    $topDeviceCount = 10;
    $count = 0;
    $startDate = "";
    $lastDate = "";
    $apps = array();
    $excluded = array("family chat");
    $appVersions = array();
    $devices = array();
    $uniqueDevices = array();
    $uniqueDevices = array();
    $uniqueDevices = array();
    $osVersions = array();
    $clients = array();
    class UpdateApp
    {
        public $appName;
        public $count;
        public $appVersions;
        public $uniqueDevices;
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
        public $uniqueDevices;
    }
    class UpdateReport
    {
        public $firstDate;
        public $lastDate;
        public $totalChecks;
        public $uniqueDevices;
        public $topApps = array();
        public $topDevices = array();
        public $topOSVersions = array();
    }

    //get the log data
    while($line = fgets($data)) {
        $line = str_replace("\n", "", $line);
        $line = stripcslashes($line);
        $line = str_replace("//", "/", $line);

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
                if ($appName == "appmuseum.museumapp")  //handle alternate name
                    $appName = "app museum 2";

                if (!in_array($appName, $excluded)) {   //leave out private apps
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
                    if (!in_array($clientid, $uniqueDevices)) {
                        array_push($uniqueDevices, $clientid);
                    }
                    //accumulate (or start) the client count for this app
                    if (!array_key_exists($appId, $clients)) {
                        $clients[$appId] = array();
                    }    
                    if (!in_array($clientid, $clients[$appId])) {
                        array_push($clients[$appId], $clientid);
                    }
                    
                    /* Devices */
                    if (strpos($line, "Mozilla/5.0") !== false) {
                        $deviceString = extractMozillaDeviceInfo($line);
                    } else {
                        $deviceString = $lineParts[3];
                    }
                    
                    $deviceString = explode("/", $deviceString);
                    $deviceName = $deviceString[0];
                    if ($deviceName == "Prē" ) { // Pre2 lies
                        if (array_key_exists(2, $deviceString)) {
                            $carrier = $deviceString[2];
                            if ($carrier == "Verizon") {
                                $deviceName = "Pre2";
                            }
                        }
                    }
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
                    $osVersion = $deviceString[1];   
                    //accumulate (or start) the count for this device version
                    if (!array_key_exists($osVersion, $osVersions)) {
                        $osVersions[$osVersion] = 1;
                    } else {
                        $osVersions[$osVersion] += 1;
                    }
                    //accumulate unique device account by os name
                    if (!array_key_exists($osVersion, $uniqueDevices)){
                        $uniqueDevices[$osVersion] = array($clientid);
                    }
                    else{
                        if (is_array($uniqueDevices[$osVersion]) && !in_array($clientid, $uniqueDevices[$osVersion]))
                            array_push($uniqueDevices[$osVersion], $clientid);
                    }
                }
            }
        }
        $count++;
    }

    //format report object
    $updateReport = new UpdateReport();

    arsort($apps);  //sort apps descending by count
    $i = 1;
    $appVersions = [];
    foreach ($apps as $key => $val) {
        if ($i <= $topAppCount) {
            if (isset($appVersions[$appId]) && is_array($appVersions[$appId]))
                $appVersions = $appVersions[$appId];
            $thisApp = new UpdateApp();
            $thisApp->appName = $key;
            $appId = str_replace(" ", "", $key);
            $thisApp->appVersions = $appVersions;
            $thisApp->count = $val;
            $thisApp->uniqueDevices = count($clients[$appId]);
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
    $uniqueDevices = 0;
    $uniqueDeviceList = [];
    foreach ($osVersions as $key => $val) {
        if ($i <= $topDeviceCount) {
            if (isset($uniqueDevices[$key]) && is_array($uniqueDevices[$key])) {
                $uniqueDevices = count($uniqueDevices[$key]);
                $uniqueDeviceList = $uniqueDevices[$key];
            }
            $thisOS = new OSVersion();
            $thisOS->osVersionString = $key;
            $thisOS->count = $val;
            $thisOS->uniqueDevices = $uniqueDevices;
            $thisOS->uniqueOSDeviceList = $uniqueDeviceList;
            $updateReport->topOSVersions[$i] = $thisOS;
            $i++;
        } else {
            break;
        }
    }
    $updateReport->firstDate = $startDate;
    $updateReport->lastDate = $lastDate;
    $updateReport->totalChecks = $count;
    $updateReport->uniqueDevices = count($uniqueDeviceList);
    $updateReport->uniqueClientDetails = $uniqueDevices;

    //return report object as JSON
    header("Content-Type: " . $mimeType);
    echo(json_encode($updateReport));
}

function extractMozillaDeviceInfo($line) {
    $lineParts = explode("Mozilla/5.0 ", $line);

    $device = "Browser";
    $os = $lineParts[1];

    $deviceString = $lineParts[1];
    $deviceParts = array();
    if (strpos($deviceString, "hpwOS"))
        $deviceParts = explode("hpwOS/", $deviceString);
    else
        $deviceParts = explode("webOS/", $deviceString);
    if (count($deviceParts) > 1) {
        $os = $deviceParts[1];
        $deviceParts = explode(";", $os);
        $os = $deviceParts[0];
        if (strpos($deviceString, "P160UNA/1.0") !== false || strpos($deviceString, "P160UEU/1.0") !== false)
            $device = "Veer";
        if (strpos($deviceString, "TouchPad/1.0") !== false)
            $device = "TouchPad";
        if (strpos($deviceString, "Pre/1.0") !== false || strpos($deviceString, "Pre/1.1") !== false)
            $device = "Prē";
        if (strpos($deviceString, "Pre/1.2") !== false)
            $device = "Pre2";
    }
    return $device . "/" . $os;
}
?>