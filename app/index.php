<?php
$config = include('../WebService/config.php');
include("../common.php");

//figure out what protocol to use
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    $protocol = "https://";
else
    $protocol = "http://";

//figure out what they're looking for
$req = explode('/', $_SERVER['REQUEST_URI']);
$query = end($req);
$query = str_replace("+", "", $query);
$dest_page = $protocol. $config["service_host"];

//get the results directly without HTTP request to avoid rate limiting
$fullcatalog = load_catalogs(array("../newerAppData.json", "../archivedAppData.json"));
$search_str = urldecode(strtolower($query));
$search_str = preg_replace("/[^a-zA-Z0-9 ]+/", "", $search_str);

$results = [];
//Loop through all apps
foreach ($fullcatalog as $this_app => $app_a) {
	//Look for matches
	if (strtolower($app_a["title"]) == $search_str || 
		$search_str == $app_a["id"] ||
		(strpos(strtolower($app_a["title"]), $search_str) !== false) || 
		(strpos(strtolower(str_replace(" ", "", $app_a["title"])), $search_str) !== false) 
	  ) 
	{
		array_push($results, $app_a);
	}
}
$responseObj = new stdClass();
$responseObj->data = $results;
$app_response = json_decode(json_encode($responseObj), true);

//send them to result if exact match, or search page if not
$dest_page = $protocol. $config["service_host"];
echo "count: " . count($app_response['data']);
if (isset($app_response) && isset($app_response['data'][0]) && count($app_response['data']) == 1) {
    $dest_page .= "/showMuseumDetails.php?app=" . $app_response['data'][0]['id'];
} else {
    $dest_page .= "/showMuseum.php?search=" . $query;
}
//echo $dest_page;
header("Location: $dest_page");
?>
