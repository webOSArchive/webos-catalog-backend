<html>
<?php
$config = include('WebService/config.php');

$string = file_get_contents("extantAppData.json");
if ($string === false) {
	echo ("ERROR: Could not find catalog file");
	die;
}

$json_a = json_decode($string, true);
if ($json_a === null) {
	echo ("ERROR: Could not parse catalog file");
	die;
}

$found_id = "null";
$search_str = $_SERVER["QUERY_STRING"];
$search_str = urldecode(strtolower($search_str));
$found_app;
foreach ($json_a as $this_app => $app_a) {
	if (strtolower($app_a["title"]) == $search_str || $app_a["id"] == $search_str) {
		$found_app = $app_a;
		$found_id = $found_app["id"];
	}
}

if ($found_id == "null") {
	echo("ERROR: No matching app found");
	die;
}
$meta_path = "http://" . $config["package_host"] . "/WebService/getMuseumDetails.php?id=" . $found_id;

$meta_file = fopen($meta_path, "rb");
$content = stream_get_contents($meta_file);
fclose($meta_file);

$app_detail = json_decode($content, true);
$downloadURI = "http://" . $config["package_host"] . "/AppPackages/" . $app_detail["filename"];
$imgPath = "http://" . $config["package_host"] . "/AppImages/";

?>
<head>
<title><?php echo $found_app["title"] ?> - webOS App Museum II</title>
<link rel="stylesheet" href="webmuseum.css">
</head>
<body>
<h2><a href="showMuseumCategories.php"><img src="icon.png" style="height:64px;width:64px;margin-top:-10px;" align="middle"> &nbsp;webOS App Museum II</a></h2>
<table border="0" >
<tr><td colspan="2"><h1><?php echo $found_app["title"] ?></h1></td>
	<td rowspan="2">
	<img src="<?php echo $imgPath. $found_app["appIconBig"]?>" class="appIcon" >
</td></tr>
<tr><td class="rowTitle">Museum ID</td><td><?php echo $found_app["id"] ?></td></tr>
<tr><td class="rowTitle">Application ID</td><td colspan="2"><?php echo $app_detail["publicApplicationId"] ?></td></tr>
<tr><td class="rowTitle">Author</td><td colspan="2"><?php echo "<a href='" . $app_detail["homeURL"] . "'>" . $found_app["author"] . "</a>"?></td></tr>
<tr><td class="rowTitle">Version</td><td><?php echo $app_detail["version"] ?></td><td></td></tr>
<tr><td class="rowTitle">Description</td><td colspan="2"><?php echo str_replace("\r\n", "<br>", $app_detail["description"]) ?></td></tr>
<tr><td class="rowTitle">Version Note</td><td colspan="2"><?php echo str_replace("\r\n", "<br>", $app_detail["versionNote"]) ?></td></tr>
<tr><td class="rowTitle">Download</td><td colspan="2"><a href="<?php echo $downloadURI ?>" target="_blank">Direct Link</a></td></tr>
<tr><td class="rowTitle">Device Support</td>
<td>
	<ul>
	<li class="deviceSupport<?php echo $found_app["Pre"] ?>">Pre: 
	<li class="deviceSupport<?php echo $found_app["Pixi"] ?>">Pixi: 
	<li class="deviceSupport<?php echo $found_app["Pre2"] ?>">Pre2: 
	<li class="deviceSupport<?php echo $found_app["Veer"] ?>">Veer:
	<li class="deviceSupport<?php echo $found_app["Pre3"] ?>">Pre3:
	<li class="deviceSupport<?php echo $found_app["TouchPad"] ?>">TouchPad:
	</ul>
</td>
<td></td>
</tr>
<tr><td class="rowTitle">Screenshots</td>
<td colspan="2">
<?php
foreach ($app_detail["images"] as $value) {
    echo("<a href='" . $imgPath . $value["screenshot"] . "' target='_blank'><img class='screenshot' src='" . $imgPath. $value["thumbnail"] . "' style='width:64px'></a>");
}
?>
</td></tr>
<tr><td class="rowTitle">License</td><td colspan="2"><?php echo $app_detail["licenseURL"] ?></td></tr>
<tr><td class="rowTitle">Copyright</td><td colspan="2"><?php echo $app_detail["copyright"] ?></td></tr>
</table>

<div style="display:none;margin-top:18px">
<?php
echo (json_encode($app_a) . "<br><br>");
echo $content;
?>
</body>
</html>