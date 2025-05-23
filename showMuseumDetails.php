<?php include('tldchange-notice.php'); ?>
<html>
<head>
<link rel="shortcut icon" href="favicon.ico">
<meta name="viewport" content="width=760, initial-scale=0.6">
<script>
function showHelp() {
	alert("Most webOS Devices should use the App Museum II native app to browse and install from the catalog. Older devices that can't run the Museum can Option+Tap (Orange or White Key) or Long Press (if enabled) on the Preware link on this page and copy it to your clipboard. Then you can use the 'Install Package' menu option in Preware to paste in and install the app using that link.");
}
</script>

<?php
$config = include('WebService/config.php');
include('common.php');
session_start();
if (!isset($_SESSION['encode_salt']))
{
	$_SESSION['encode_salt'] = uniqid();
}
//Load catalogs
$fullcatalog = load_catalogs(array("newerAppData.json", "archivedAppData.json"));

$found_id = "null";
if (isset($_GET["app"])) {
	$search_str = $_GET["app"];
	$search_str = urldecode(strtolower($search_str));
	$search_str = preg_replace("/[^a-zA-Z0-9 ]+/", "", $search_str);
	$found_app;
	foreach ($fullcatalog as $this_app => $app_a) {
		if (strtolower($app_a["title"]) == $search_str || $app_a["id"] == $search_str) {
			$found_app = $app_a;
			$found_id = $found_app["id"];
		}
	}
}
if ($found_id == "null") {
	echo("ERROR: No matching app found");
	die;
}

//Figure out what protocol the client wanted
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    $PROTOCOL = "https://";
else
    $PROTOCOL = "http://";

$meta_path = $PROTOCOL . $config["service_host"] . "/WebService/getMuseumDetails.php?id=" . $found_id;

$meta_file = fopen($meta_path, "rb");
$content = stream_get_contents($meta_file);
fclose($meta_file);

$app_detail = json_decode($content, true);

//Improve some strings for web output
$img_path = $PROTOCOL . $config["image_host"] . "/";
if (isset($app_detail["description"])) {
	$app_detail["description"] = str_replace("\n", "<br>", $app_detail["description"]);
	$app_detail["description"] = str_replace("\r\n", "<br>", $app_detail["description"]);
} else {
	$app_detail["description"] = "";	
}
if (isset($app_detail["versionNote"])) {
	$app_detail["versionNote"] = str_replace("\n", "<br>", $app_detail["versionNote"]);
	$app_detail["versionNote"] = str_replace("\r\n", "<br>", $app_detail["versionNote"]);
} else {
	$app_detail["versionNote"] = "";
}
	
//Let's make some URLs!
$author_url = "author/" . str_replace(" " , "%20", $found_app["author"]);
$share_url = $PROTOCOL . $config["service_host"] . "/app/" . str_replace(" " , "", $found_app["title"]);
//Support absolute download paths (files hosted elsewhere)
if (isset($app_detail["filename"]) && strpos($app_detail["filename"], "://") === false) {
	if ($PROTOCOL == "https://")
		$plainURI = $PROTOCOL . $config["package_host_secure"] . "/" . $app_detail["filename"];
	else
		$plainURI = $PROTOCOL . $config["package_host"] . "/" . $app_detail["filename"];
	
} else {
	$plainURI = $app_detail["filename"];
    $plainURI = str_replace("https://",$PROTOCOL,$plainURI);
}
//alternateFileName
if (isset($app_detail["alternateFileName"]) && strpos($app_detail["alternateFileName"], "://") === false) {
	if ($PROTOCOL == "https://")
		$altPlainURI = $PROTOCOL . $config["package_host_secure"] . "/" . $app_detail["alternateFileName"];
	else
		$altPlainURI = $PROTOCOL . $config["package_host"] . "/" . $app_detail["alternateFileName"];
}
//Encode URL to reduce brute force downloads
//	The complete archive will be posted elsewhere to save my bandwidth
$downloadURI = base64_encode($plainURI);
$splitPos = rand(1, strlen($downloadURI) - 2);
$downloadURI = substr($downloadURI, 0, $splitPos) . $_SESSION['encode_salt'] . substr($downloadURI, $splitPos);
if (isset($altPlainURI)) {
	$altDownloadURI = base64_encode($altPlainURI);
	$splitPos = rand(1, strlen($altDownloadURI) - 2);
	$altDownloadURI = substr($altDownloadURI, 0, $splitPos) . $_SESSION['encode_salt'] . substr($altDownloadURI, $splitPos);
}

//Figure out where to go back to
parse_str($_SERVER["QUERY_STRING"], $query);
unset($query["app"]);
$homePath = "showMuseum.php?" . http_build_query($query);

//Figure out image paths
if (strpos($found_app["appIconBig"], "://") === false) {
	$use_icon = $img_path.strtolower($found_app["appIconBig"]);
} else {
	$use_icon = $found_app["appIconBig"];
}

//Shorten description for social media
$meta_desc = str_replace($app_detail["description"], "/r", "<br>");
$meta_desc = str_replace($app_detail["description"], "/n", "<br>");
$meta_desc = explode("<br>", $app_detail["description"]);
$meta_desc = trim($meta_desc[0]);

//Add social media meta tags
include('meta-social-app.php');
?>
<title><?php echo $found_app["title"] ?> - webOS App Museum II</title>
<link rel="stylesheet" href="webmuseum.css">
<script src="downloadHelper.php"></script>
</head>
<body onload="populateLink()">
<?php include("menu.php") ?>
<div class="show-museum" style="margin-left:auto;margin-right:auto">
	<h2><a href="<?php echo ($homePath); ?>"><img src="assets/icon.png" style="height:64px;width:64px;margin-top:-10px;" align="middle"></a> &nbsp;<a href="<?php echo ($homePath); ?>">webOS App Museum II</a></h2>
	<br>
	<table border="0" style="margin-left:1.3em;">
	<tr><td colspan="2"><h1><?php echo $found_app["title"]; ?></h1></td>
		<td rowspan="2">
		<img src="<?php echo $use_icon; ?>" class="appIcon" >
	</td></tr>
	<tr><td class="rowTitle">Museum ID</td><td class="rowDetail"><?php echo $found_app["id"] ?></td></tr>
	<tr><td class="rowTitle">Application ID</td><td colspan="2" class="rowDetail"><?php echo $app_detail["publicApplicationId"] ?></td></tr>
	<tr><td class="rowTitle">Share Link</td><td colspan="2" class="rowDetail"><?php echo "<a href='" . $share_url . "'>" . $share_url . "</a>"?></td></tr>
	<tr><td class="rowTitle">Author</td><td colspan="2" class="rowDetail"><?php echo "<a href='" . $author_url . "'>" . $found_app["author"] . "</a>"?></td></tr>
	<tr><td class="rowTitle">Version</td><td class="rowDetail"><?php echo $app_detail["version"] ?></td><td></td></tr>
	<tr><td class="rowTitle">Description</td><td colspan="2" class="rowDetail"><?php echo $app_detail["description"]; ?></td></tr>
	<tr><td class="rowTitle">Version Note</td><td colspan="2" class="rowDetail"><?php echo $app_detail["versionNote"]; ?></td></tr>
	<?php
	$browserAsString = $_SERVER['HTTP_USER_AGENT'];
	if (strstr(strtolower($browserAsString), "webos") || strstr(strtolower($browserAsString), "hpwos")) {
		$plainURI = str_replace("https://", "http://", $plainURI);
	?>
		<tr><td class="rowTitle">Download</td><td colspan="2" class="rowDetail">
			<a href="<?php echo $plainURI ?>">Preware Link</a> 
			&nbsp;<a href="javascript:showHelp()">(?)</a>
		</td></tr>
	<?php
	} else {
	?>
		<tr><td class="rowTitle">Download</td><td colspan="2" class="rowDetail" id="tdDownloadLink" title="Download Link Decoded by Javascript" data-encoded-uri="<?php echo $downloadURI ?>" data-app-id="<?php echo $found_app["id"] ?>"><i>Requires Javascript</i></td></tr>
	<?php
	    if (isset($altDownloadURI)) {
			?>
			<tr><td class="rowTitle">Alternate Version</td><td colspan="2" class="rowDetail" id="tdAltDownloadLink" title="Download Link Decoded by Javascript" data-encoded-uri="<?php echo $altDownloadURI ?>" data-app-id="<?php echo $found_app["id"] ?>"><i>Requires Javascript</i></td></tr>
			<?php
		}
	}
	?>

	<tr><td class="rowTitle">Device Support</td>
	<td class="rowDetail">
		<ul>
		<li class="deviceSupport<?php echo $found_app["Pre"] ?>">Pre: 
		<li class="deviceSupport<?php echo $found_app["Pixi"] ?>">Pixi: 
		<li class="deviceSupport<?php echo $found_app["Pre2"] ?>">Pre2: 
		<li class="deviceSupport<?php echo $found_app["Veer"] ?>">Veer:
		<li class="deviceSupport<?php echo $found_app["Pre3"] ?>">Pre3:
		<li class="deviceSupport<?php echo $found_app["TouchPad"] ?>">TouchPad:
		<li class="deviceSupport<?php echo $found_app["LuneOS"] ?>">LuneOS:
		</ul>
	</td>
	<td></td>
	</tr>
	<tr><td class="rowTitle">Screenshots</td>
	<td colspan="2" class="rowDetail">
	<?php
	foreach ($app_detail["images"] as $value) {
		if (strpos($value["screenshot"], "://") === false) {
			$use_screenshot = $img_path.strtolower($value["screenshot"]);
		} else {
			$use_screenshot = $value["screenshot"];
		}
		if (strpos($value["thumbnail"], "://") === false) {
			$use_thumb = $img_path.strtolower($value["thumbnail"]);
		} else {
			$use_thumb = $value["thumbnail"];
		}
		echo("<a href='" . $use_screenshot . "' target='_blank'><img class='screenshot' src='" . $use_thumb . "' style='width:64px'></a>");
	}
	?>
	</td></tr>
	<tr><td class="rowTitle">Home Page</td><td colspan="2" class="rowDetail"><a href="<?php echo $app_detail["homeURL"] ?>" target="_blank"><?php echo $app_detail["homeURL"] ?></a></td></tr>
	<tr><td class="rowTitle">Support URL</td><td colspan="2" class="rowDetail"><a href="<?php echo $app_detail["supportURL"] ?>" target="_blank"><?php echo $app_detail["supportURL"] ?></a></td></tr>
	<tr><td class="rowTitle">File Size</td><td colspan="2" class="rowDetail"><?php echo round($app_detail["appSize"]/1024,2) ?> KB</td></tr>
	<tr><td class="rowTitle" class="rowDetail">License</td><td colspan="2"><?php echo $app_detail["licenseURL"] ?></td></tr>
	<tr><td class="rowTitle" class="rowDetail">Copyright</td><td colspan="2"><?php echo $app_detail["copyright"] ?></td></tr>
	</table>
	<?php
	include 'footer.php';
	?>
	<div style="display:none;margin-top:18px">
	<?php
	echo (json_encode($app_a) . "<br><br>");
	echo $content;
	?>
</div>
</body>
</html>
