<?php
function render_social($link, $basePath) {
	$imgsrc = $basePath. "/social/web.png";
	if (strpos($link, "discord.com") !== false || strpos($link, "webosarchive.org/discord") !== false)
		$imgsrc = $basePath. "/social/discord.png";
	if (strpos($link, "facebook.com") !== false)
		$imgsrc = $basePath. "/social/facebook.png";
	if (strpos($link, "github.com") !== false)
		$imgsrc = $basePath. "/social/github.png";
	if (strpos($link, "instagram.com") !== false)
		$imgsrc = $basePath. "/social/instagram.png";
	if (strpos($link, "linkedin.com") !== false)
		$imgsrc = $basePath. "/social/linkedin.png";
	if (strpos($link, "reddit.com") !== false)
		$imgsrc = $basePath. "/social/reddit.png";
	if (strpos($link, "snapchat.com") !== false)
		$imgsrc = $basePath. "/social/snapchat.png";
	if (strpos($link, "twitter.com") !== false)
		$imgsrc = $basePath. "/social/twitter.png";
	if (strpos($link, "youtube.com") !== false)
		$imgsrc = $basePath. "/social/youtube.png";
	return "<img src='" . $imgsrc . "' class='authorSocial'>";
}

function load_catalogs($catalogs) {
	$fullcatalog = array();
	foreach ($catalogs as $catalog) {
		$string = file_get_contents($catalog);
		if ($string === false) {
			echo ("ERROR: Could not find catalog file: " . $catalog);
			die;
		}
		$apps = json_decode($string, true);
		if ($apps === null) {
			echo ("ERROR: Could not parse catalog file: ". $catalog);
			die;
		}
		foreach ($apps as $app) {
			$found = false;
			foreach ($fullcatalog as $exist) {
				if ($app["id"] == $exist["id"])
					$found = true;
			}
			if (!$found)
				array_push($fullcatalog, $app);
		}
	}
	return $fullcatalog;
}

/**
 * Search apps by title/id with fuzzy matching
 * @param array $catalog - Full app catalog
 * @param string $search_str - Search term (already lowercased and sanitized)
 * @param bool $adult - Whether to include adult content
 * @return array - Matching apps
 */
function search_apps($catalog, $search_str, $adult = false) {
	$results = [];
	foreach ($catalog as $app_a) {
		// Look for matches
		if (strtolower($app_a["title"]) == $search_str || 
			$search_str == $app_a["id"] ||
			(strpos(strtolower($app_a["title"]), $search_str) !== false) || 
			(strpos(strtolower(str_replace(" ", "", $app_a["title"])), $search_str) !== false) 
		  ) 
		{
			// Filter adult content
			if (!$adult && $app_a['Adult']) {
				continue;
			}
			array_push($results, $app_a);
		}
	}
	return $results;
}

/**
 * Search apps by author with fuzzy matching
 * @param array $catalog - Full app catalog  
 * @param string $search_str - Search term (already lowercased and sanitized)
 * @param bool $adult - Whether to include adult content
 * @return array - Matching apps
 */
function search_apps_by_author($catalog, $search_str, $adult = false) {
	$results = [];
	foreach ($catalog as $app_a) {
		// Look for author matches
		if (strtolower($app_a["author"]) == $search_str || 
			(strpos(strtolower($app_a["author"]), $search_str) !== false) ||
			(strtolower(str_replace(" ", "", $app_a["author"])) == $search_str) || 
			(strpos(strtolower(str_replace(" ", "", $app_a["author"])), $search_str) !== false)
		 ) 
		{
			// Filter adult content
			if (!$adult && $app_a['Adult']) {
				continue;
			}
			array_push($results, $app_a);
		}
	}
	return $results;
}

/**
 * Filter apps by category with adult content filtering
 * @param array $catalog - Full app catalog
 * @param string $category - Category to filter by ('All' for no filter)
 * @param bool $adult - Whether to include adult content
 * @param int $limit - Maximum number of results (0 for no limit)
 * @return array - Filtered apps
 */
function filter_apps_by_category($catalog, $category, $adult = false, $limit = 0) {
	$results = [];
	foreach ($catalog as $app_a) {
		// Filter by category
		if ($category !== 'All' && $app_a['category'] !== $category) {
			continue;
		}
		// Filter adult content
		if (!$adult && $app_a['Adult']) {
			continue;
		}
		array_push($results, $app_a);
		if ($limit > 0 && count($results) >= $limit) {
			break;
		}
	}
	return $results;
}

/**
 * Create a standard response object for app search results
 * @param array $results - Array of app results
 * @return array - Standardized response format
 */
function create_app_response($results) {
	$responseObj = new stdClass();
	$responseObj->data = $results;
	return json_decode(json_encode($responseObj), true);
}
?>