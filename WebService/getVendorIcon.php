<?PHP
	include("ratelimit.php");
	
	// Rate limit: 30 requests per hour for icon fetching
	checkRateLimit(30, 3600);
	
	$url = $_GET["url"];
	$desiredSize = 32;	
	if (!isset($url)) {
		echo "";
		die();
	}
	
	// Security: Validate URL to prevent SSRF attacks
	function isValidUrl($url) {
		// Parse URL
		$parsed = parse_url($url);
		if (!$parsed) {
			return false;
		}
		
		// Only allow HTTP and HTTPS schemes
		if (!isset($parsed['scheme']) || !in_array($parsed['scheme'], ['http', 'https'])) {
			return false;
		}
		
		// Block private/internal IP ranges
		if (isset($parsed['host'])) {
			$ip = gethostbyname($parsed['host']);
			if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
				return false;
			}
		}
		
		// Block localhost variations
		$blocked_hosts = ['localhost', '127.0.0.1', '::1', '0.0.0.0'];
		if (isset($parsed['host']) && in_array(strtolower($parsed['host']), $blocked_hosts)) {
			return false;
		}
		
		return true;
	}
	
	$url = str_replace(" ", "", $url);
	$url = str_replace("%20", "", $url);
	$url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
	
	// Validate URL before proceeding
	if (!isValidUrl($url)) {
		http_response_code(400);
		echo "Invalid URL provided";
		die();
	}

	$ch  =  curl_init   (); 
		    curl_setopt ($ch, CURLOPT_URL, $url); 
		    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
		    curl_setopt ($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		    curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
    $page = curl_exec   ($ch); 
            curl_close  ($ch);   

	$re   = '/(<link).*?icon.*?(>)/i';
	preg_match_all($re, $page, $icons, PREG_SET_ORDER, 0);
	$diff     = 100000;
	$favicon  = "";
	$myUrl    = explode(".", $url);
	if (count($myUrl) > 2) {
		array_shift($myUrl) ;
	}
	$myUrl    = implode(".", $myUrl);

	foreach($icons as $icn) {
		$re = '/sizes="?\'?(\d*)/';
		preg_match($re, $icn[0], $size);

		if (!empty($size[0])) {
			$s = $size[1];
			if (abs($s-$desiredSize) < $diff) {
				$diff = abs($s-$desiredSize);
				$re = '/href="(.*)"/U';
				preg_match($re, $icn[0], $icon);

				$linker = "";
				$home = "";
				if (strpos($icon[1], $myUrl) === false) {
					$home = $url;
					if ($icon[1][0] != "/") {
						$linker = "/";
					}
				}
				if (strpos($icon[1], "http") === 0) {
					$home   = "";
					$linker = "";
				}
				$bestIcon = $home . $linker . $icon[1];

			}
		} else {
			if (strpos($icn[0], ".ico") != false || strpos($icn[0], ".png") != false) {
				$re = '/href="(.*)"/U';
				preg_match($re, $icn[0], $icon);
				$linker = "";
				$home = "";
				if (isset($icon[1])) {
					if (strpos($icon[1], $myUrl) === false) {
						$home = $url;
						if ($icon[1][0] != "/") {
							$linker = "/";
						}
					}
					if (strpos($icon[1], "http") === 0) {
						$home   = "";
						$linker = "";
					}
					$favicon = $home . $linker . $icon[1];
				}
			}
		}
	}
	if (!isset($bestIcon)) {
		$bestIcon = $favicon;
	}

	header("Location: $bestIcon", true, 301);
?>