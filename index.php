<!DOCTYPE html>
<html lang="en">
<?php
//This file is only used for advertising on a hosting webserver

//App Details
$title = "webOS App Museum II";
$subtitle = " | webOS Archive";
$description = "The App Museum is a community project to archive, restore and provide access to the historical catalog of apps for Palm/HP's defunct mobile platform, webOS.";
$github = "https://github.com/webosarchive/";
$icon = "assets/icon.png";

//Figure out what protocol the client wanted
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
	$PROTOCOL = "https";
} else {
	$PROTOCOL = "http";
}

$config = include('WebService/config.php');

//Get the app info
if ($PROTOCOL == "https://")
  $download_path = $PROTOCOL . $config["package_host_secure"] . "/";
else
  $download_path = $PROTOCOL . $config["package_host"] . "/";
$meta_path = "http://" . $config["metadata_host"] . "/0.json";
$meta_file = fopen($meta_path, "rb");
$content = stream_get_contents($meta_file);
fclose($meta_file);
$outputObj = json_decode($content, true);
if (strpos($outputObj["filename"], "://") === false) {
  $use_uri = $download_path . $outputObj["filename"];
} else {
  $use_uri = $outputObj["filename"];
}
?>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

  <meta name="description" content="<?php echo $description; ?>">
  <meta name="keywords" content="webos, firefoxos, pwa, rss">
  <meta name="author" content="webOS Archive">
  <meta property="og:title" content="<?php echo $title; ?>">
  <meta property="og:description" content="<?php echo $description; ?>">
  <meta property="og:image" content="https://<?php echo $_SERVER['SERVER_NAME'] ?>/hero.png">

  <meta name="twitter:card" content="app">
  <meta name="twitter:site" content="@webOSArchive">
  <meta name="twitter:title" content="<?php echo $title; ?>">
  <meta name="twitter:description" content="<?php echo $description; ?>">

  <title><?php echo $title . $subtitle; ?></title>
  
  <link id="favicon" rel="icon" type="image/png" sizes="64x64" href="<?php echo $icon;?>">
  <link href="<?php echo $PROTOCOL . "://www.webosarchive.org/app-template/"?>web.css" rel="stylesheet" type="text/css" >
  <style>
    a { text-decoration: none; }
    a:hover { text-decoration: underline; }
    #hero { padding-top:60px }
    @media all and (max-width: 599px) {
        #hero { padding-top: 0px !important; }
    }
  </style>
</head>
<body>
<?php

$docRoot = "./";
echo file_get_contents("https://www.webosarchive.org/menu.php?docRoot=" . $docRoot . "&protocol=" . $PROTOCOL);
?>

  <table width="100%" border=0 style="width:100%;border:0px"><tr><td align="center" style="width:100%;height:100%;border:0px">
  <div id="row">
    <div id="content" align="left">
      <h1><img src="<?php echo $icon;?>" width="60" height="60" alt=""/><?php echo $title; ?></h1>
      <p><?php echo $description; ?></p>
      <p>The recovered catalog items are stored on the <a href="https://archive.org/details/@webos_archive">Internet Archive</a>, and can be browsed on the web, or on-device via Preware or a native client. </p>
      <p>
        <a class="download-link" href="<?php echo $use_uri?>">
          <img src="assets/icon.png" style="vertical-align:middle" alt="Download for webOS" title="Download for webOS" width="48" height="48"/> Download for webOS
        </a> | <a href="http://www.webosarchive.org/docs/appstores/">Help</a>
        <br>
        <a class="download-link" href="https://github.com/h8pewou/legacy-webos-feeds/blob/main/README.md#wosa-feed">
           <img src="assets/preware-icon.png" style="vertical-align:middle" alt="Add to Preware" title="Add to Preware" width="48" height="48"/> Add to Preware</a> | <a href="https://archive.org/details/webosappcatalog"> Download Archive
        </a>
        <br>
        <a class="download-link" href="showMuseum.php">
           <img src="assets/browser-icon.png" style="vertical-align:middle" alt="Browse Online" title="Browse Online" width="48" height="48"/> Browse Online </a> | <a href="https://weboslives.eu/feeds/">Alternate Site
		</a>
      </p>
    </div>
    <div id="hero">
      <img src="hero.png" width="480" alt="<?php echo $title ?>" />
      <p><i><small>Catalog metadata is available on <?php echo "<a href='" . $github . "'>GitHub</a>"?> | <a href="https://appcatalog.webosarchive.org/WebService/reports/">View Stats</a><br>
      Many items are still missing! If you an old device or personal archive, check the <a href="wanted.txt">wanted</a> <a href="wanted.csv">list</a>, or run the <a href="https://appcatalog.webosarchive.org/app/webOSAppScanner">App Scanner</a> on your device, and <a href="mailto:webosarchive@gmail.com">email us</a> if you have any matches!</small></i></p>
    </div>
  </div>
  <div id="footer">
    &copy; webOSArchive <?php echo date("Y"); ?>
    <div id="footer-links">
    <a href="<?php echo $PROTOCOL . "://www.webosarchive.org/privacy.html"?>">Privacy Policy</a>
    </div>
  </div>
  </td></tr></table>
</body>
</html>
