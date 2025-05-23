<?php
//copy this file to config.php
//  put global config here, subdirectories are supported, but no trailing slahes
//  you must host these repositories over HTTPS AND HTTP without redirecting to HTTPS
//  (you can use the Upgrade-Insecure-Requests header in your server config to serve HTTPS to modern web clients)
//  Note: the contents of this file are available via an API call to any client, do not embed any secrets
$image_mirrors = array(
        'appcatalog.webosarchive.org/AppImages'
);
$package_mirror_plain = array(
        'appstorage.webosarchive.org/packages'
);
$package_mirror_secure = 'appstorage.webosarchive.org/packages';

return array(
        'service_host' => 'appcatalog.webosarchive.org',
        'metadata_host' => 'appmetadata.webosarchive.org',
        'image_host' => select_lb_resource($image_mirrors),
        'package_host' => select_lb_resource($package_mirror_plain),
        'package_host_secure' => $package_mirror_secure,
        'contact_email' => 'webosarchive@gmail.com'
);

function select_lb_resource($resource_array) {
        return($resource_array[array_rand($resource_array)]);
}
?>
