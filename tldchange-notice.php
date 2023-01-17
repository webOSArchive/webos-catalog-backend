<?php
if (strpos($_SERVER['HTTP_HOST'], "webosarchive.com") !== false) {
        $newURL = "http://";
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
                $newURL = "https://";
        $newURL .= str_replace("webosarchive.com", "webosarchive.org", $_SERVER['HTTP_HOST']);
        $newURL .= $_SERVER['REQUEST_URI'];
        if ($_SERVER['QUERY_STRING'] !== '')
                $newURL .= "&redir=tld";
        else
                $newURL .= "?redir=tld";
        header("Location: " . $newURL);
        die();
}
?>