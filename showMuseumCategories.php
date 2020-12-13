<html>
<head>
<title>webOS App Museum II - Catalog</title>

<?php
$config = include('WebService/config.php');

//Get the category list
$category_path = "http://" . $config["package_host"] . "/WebService/getMuseumMaster.php?count=All&device=All&category=All&query=&page=0&blacklist=&key=web_categories&hide_missing=false";
$category_file = fopen($category_path, "rb");
$category_content = stream_get_contents($category_file);
fclose($category_file);
$category_master = json_decode($category_content, true);
$category_list = array_keys($category_master["appCount"]);
sort($category_list);

//Get the app list if there is a category query
if ($_GET['category'] != null && $_GET['count'] != null)
{
	$app_path = "http://" . $config["package_host"] . "/WebService/getMuseumMaster.php?count=". $_GET['count'] ."&device=All&category=". urlencode($_GET['category']) ."&query=&page=0&blacklist=&key=webapp_". uniqid() ."&hide_missing=false";
	$app_file = fopen($app_path, "rb");
	$app_content = stream_get_contents($app_file);
	fclose($app_file);
	$app_response = json_decode($app_content, true);
}
?>
<link rel="stylesheet" href="webmuseum.css">
</head>
<body>
<h2><a href="showMuseumCategories.php"><img src="icon.png" style="height:64px;width:64px;margin-top:-10px;" align="middle"> &nbsp;webOS App Museum II</a></h2>
<div class="museumMaster">
	<div class="categoryMenu">
		<?php
			foreach ($category_list as $array_key) {
				$catname = $array_key;
				$catcount = $category_master["appCount"][$array_key];
				if ($catname != "All" && $catname != "Missing Apps" && $catcount > 0)
				{
					$catencode = (urlencode($array_key));
					echo ("<a href='showMuseumCategories.php?category={$catencode}&count={$catcount}'>{$catname} - {$catcount}</a><br/>");
				}
			}
		?>
	</div>

	<div class="appsList">
		<?php
		if (count($app_response["data"]) > 0)
		{
			foreach($app_response["data"] as $app) {
				echo("<a href='showMuseumDetails.php?{$app["id"]}'>{$app["title"]}</a><br/>");
			}
		}
		else
		{
			echo "Choose a category to view apps...";
		}
		?>
	</div>
</div>
<div style="display:none">
<?php
echo ($app_content);
?>
</div>
</body>
</html>