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

$results = search_apps($fullcatalog, $search_str, false);
$app_response = create_app_response($results);

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
