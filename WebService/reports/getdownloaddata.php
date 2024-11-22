<?php
if (!isset($config))
    $config = include('../config.php');
if (!isset($mimeType))
    $mimeType = "application/json";
else
    error_reporting(E_ERROR | E_PARSE);

returnDownloadDataFormatted($config, $mimeType);

function returnDownloadDataFormatted($config, $mimeType) {
    $data = fopen('../logs/downloadcount.log', 'r');

    $topAppCount = 20;
    $topClientCount = 10;
    $count = 0;
    $startDate = "";
    $lastDate = "";
    $apps = array();
    $clients = array();
    class App
    {
        public $appId;
        public $appName;
        public $count;
    }
    class Client
    {
        public $clientString;
        public $count;
    }
    class DownloadReport
    {
        public $firstDate;
        public $lastDate;
        public $totalDownloads;
        public $topApps = array();
        public $topClients = array();
    }

    //get extra data
    function getDetailData($host, $myIdx) {
        if (is_numeric($myIdx)) {
            if (!isset($myIdx)) {$myIdx = $id;}
            //Get the JSON file over HTTP to the configured server,
            $mypath = "http://{$host}/{$myIdx}.json";
            $myfile  = fopen($mypath, "rb");
            $content = stream_get_contents($myfile);
            fclose($myfile);
            return json_decode($content, true);
        } else {
            return $myIdx;
        }
    }

    //get the log data
    while($line = fgets($data)) {
        $line = str_replace("\n", "", $line);
        if ($count > 0) {   //skip first line
            $lineParts = explode(",", $line);
            if (count($lineParts) > 2) {
                if ($count == 1) {  //the first item has our earliest date
                    $startDate = $lineParts[0];
                }
                $lastDate = $lineParts[0];  //every subsequent item has the latest date (so far)

                //accumulate (or start) the count for this app
                $appid = $lineParts[1];     //first non-date column is the app id
                if ($appid != ".env") {
                        if (!array_key_exists($appid, $apps)) {
                            $apps[$appid] = 1;
                        } else {
                            $apps[$appid] += 1;
                        }
                }

                $clientstring = $lineParts[2];     //last column is client data
                if (strpos($clientstring, "Windows NT 10.0") !== false) {
                    $clientstring = "Windows 10";
                }
                if (strpos($clientstring, "Windows NT 6.1") !== false) {
                    $clientstring = "Windows 7";
                }
                if (strpos($clientstring, "Linux x86_64") !== false) {
                    $clientstring = "Linux PC";
                }
                if (strpos($clientstring, "Linux; NetCast; U") !== false) {
                    $clientstring = "Linux Smart TV";
                }
                if (strpos($clientstring, "CrOS x86_64") !== false) {
                    $clientstring = "ChromeOS";
                }
                if (strpos($clientstring, "Macintosh; Intel Mac OS X") !== false) {
                    $clientstring = "Mac Intel";
                }
                if (strpos($clientstring, "Linux; Android") !== false) {
                    $clientstring = "Android";
                }
                if (strpos($clientstring, "iPhone;") !== false) {
                    $clientstring = "iPhone";
                }
                if (strpos($clientstring, "app") !== false) {
                    $clientstring = "LuneOS";
                }
                if (strpos($clientstring, "webos") !== false) {
                    $clientstring = "webOS";
                }

                //accumulate (or start) the count for this client
                if (!array_key_exists($clientstring, $clients)) {
                    $clients[$clientstring] = 1;
                } else {
                    $clients[$clientstring] += 1;
                }
            }
        }
        $count++;
    }

    //format report object, with extra data
    $downloadReport = new DownloadReport();

    arsort($apps);  //sort apps descending by count
    $i = 1;
    foreach ($apps as $key => $val) {
        try {
            if ($i <= $topAppCount) {
                $appDetail = getDetailData($config["metadata_host"], $key);
                if (!isset($appDetail)) {
                    error_log("Could not get app detail during app catalog report");
                }
                if (!is_array($appDetail)) {
                    error_log("Could not parse app detail during app catalog report: " . $appDetail);
                }
                else {
                    $appName = $appDetail['publicApplicationId'];
                    $thisApp = new App();
                    $thisApp->appId = $key;
                    $thisApp->appName = $appName;
                    $thisApp->count = $val;
                    $downloadReport->topApps[$i] = $thisApp;
                    $i++;    
                }
            } else {
                break;
            }
        }
        catch (Exception $ex) {
            error_log("Could not parse app catalog report data: " . $ex->getMessage());
        }
    }

    arsort($clients);  //sort clients descending by count
    $i = 1;
    foreach ($clients as $key => $val) {
        if ($i <= $topClientCount) {
            $thisClient = new Client();
            $thisClient->clientString = $key;
            $thisClient->count = $val;
            $downloadReport->topClients[$i] = $thisClient;
            $i++;
        } else {
            break;
        }
    }
    $downloadReport->firstDate = $startDate;
    $downloadReport->lastDate = $lastDate;
    $downloadReport->totalDownloads = $count;

    header("Content-Type: " . $mimeType);
    echo(json_encode($downloadReport));
}
?>
