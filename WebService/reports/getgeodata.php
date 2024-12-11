<?php
if (!isset($config))
    $config = include('../config.php');
if (!isset($mimeType))
    $mimeType = "application/json";
else
    error_reporting(E_ERROR | E_PARSE);

returnUpdateDataFormatted($config, $mimeType);
$excluded = [];

function returnUpdateDataFormatted($config, $mimeType) {
    $data = fopen('../logs/updatecheck.log', 'r');

    $topGeoCount = 15;
    $count = 0;
    $startDate = "";
    $lastDate = "";
    $geoRegions = array();
    $ipRegions = array();
    class GeoRegion
    {
        public $regionCode;
        public $regionName;
        public $count;
    }
    class IPRegion
    {
        public $ip;
        public $regionCode;
    }
    class RegionReport
    {
        public $firstDate;
        public $lastDate;
        public $regionRecords;
        public $uniqueRegions;
        public $topRegions = array();
    }

    //get the log data
    while($line = fgets($data)) {
        $line = str_replace("\n", "", $line);
        $line = stripcslashes($line);
        $line = str_replace("//", "/", $line);

        if ($count > 0) {   //skip first line
            $lineParts = explode(",", $line);
            if (count($lineParts) > 1) {
                if ($count == 1) {  //the first item has our earliest date
                    $startDate = $lineParts[0];
                }
                $lastDate = $lineParts[0];  //every subsequent item has the latest date (so far)
                
                //accumulate (or start) the update check count for this app
                $IP = $lineParts[1];     //first non-date column is the ip

                if (!isset($excluded) || !in_array($IP, $excluded)) {  //leave out my IPs
                    $useCode = "??";
                    $useName = "Unknown Region";
                    if (!isset($IPRegions) || !is_array($IPRegions) || !array_key_exists($IP, $IPRegions)) {
                        //ask server for region info
                        $regionData = getRegionForIP($IP);
                        if (isset($regionData)) {
                            $regionObj = json_decode($regionData);
                            if (is_object($regionObj)) {
                                $useCode = $regionObj->country->iso_code;
                                $useName = $regionObj->country->names->en;    
                            } else {
                                //die("could not parse region data");
                            }
                        }
                        $IPRegions[$IP] = $useCode;
                    } else {
                        $useCode = $IPRegions[$IP];
                    }
                    if (!array_key_exists($useCode, $geoRegions)) {
                        $geoRegions[$useCode] = new GeoRegion();
                        $geoRegions[$useCode]->regionCode = $useCode;
                        $geoRegions[$useCode]->regionName = $useName;
                        $geoRegions[$useCode]->count = 0;
                    }
                    $geoRegions[$useCode]->count++;
                }
            }
        }
        $count++;
    }

    //format report object
    $regionReport = new RegionReport();

    arsort($geoRegions);  //sort apps descending by count
    $i = 1;
    foreach ($geoRegions as $key => $val) {
        if ($i <= $topGeoCount) {
            $thisRegion = new GeoRegion();
            $thisRegion->regionCode = $val->regionCode;
            $thisRegion->regionName = $val->regionName;
            $thisRegion->count = $val->count;
            $regionReport->topRegions[$i] = $thisRegion;
            $i++;
        } else {
            break;
        }
    }

    $regionReport->firstDate = $startDate;
    $regionReport->lastDate = $lastDate;
    $regionReport->regionRecords = $count;
    $regionReport->uniqueRegions = count($geoRegions);

    //return report object as JSON
    header("Content-Type: " . $mimeType);
    echo(json_encode($regionReport));
}

function getRegionForIP($ip) {
    $serviceURL = "http://museum.weboslives.eu/dqidqsrwpnhotjldxljdhkxubidheffi/fhlyggephfhwaljgtxwqxmyhuvdexcjr.php?ip=" . $ip;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $serviceURL);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $regionData = curl_exec($ch);
    curl_close($ch);
    return $regionData;
}

?>
