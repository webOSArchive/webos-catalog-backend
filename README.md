# webos-catalog-backend

PHP Back-end for webOS App Catalog restoration project. Front-end is here: [https://github.com/codepoet80/webos-catalog-frontend](https://github.com/codepoet80/webos-catalog-frontend)

![App Icon](assets/icon.png)

You can use this app on a Pre3 or Touchpad, or access the catalog in a browser at [http://appcatalog.webosarchive.org](http://appcatalog.webosarchive.org)

## Requirements

Apache will need `mb_internal_encoding`:
https://stackoverflow.com/questions/1216274/unable-to-call-the-built-in-mb-internal-encoding-method

Some features require `mod_rewrite` in Apache: https://stackoverflow.com/questions/869092/how-to-enable-mod-rewrite-for-apache-2-2

Copy the [WebService/config-example.php](https://github.com/codepoet80/webos-catalog-backend/blob/main/WebService/config.php) to `config.php` in the same folder, then edit to point to the subdomains that provide the requisite parts (metadata, images and app packages)

## AppData Files

The original museum used two files that listed known-to-be-saved and missing apps from the original HP/Palm App Catalog. Users could show the full catalog, or just the part of the catalog for which an IPK may exist...somewhere.

For the purposes of education and preservation, App Museum II links the Museum with actual IPKs in an archive or on its mirrors, and so introduces additional files and behaviors. Additionally, as of March 2022, the App Museum Archive itself is considered "frozen" -- although mechanisms have been added to support new listings in the UI that are independent of the historical archive.

### masterAppData.json

This file contains a record of all apps that were known to exist in the HP/Palm App Catalog at the time that the community attempted to archive it on or around [January 15, 2015 when HP shut it down](https://pivotce.com/2014/10/16/hp-to-shut-down-catalog-and-cloud-services/). This file is not used by the App Museum back-end, but may be used by cataloging and indexing tools when and if new IPKs are found.

### missingAppData.json

The subset of the masterAppData.json for which no matching IPK has been archived or found. If you have any of these files, please contact the curator.

### archivedAppData.json

Initially called extentAppData, this is the primary catalog file used by App Museum II. It lists the subset of the masterAppData that is known to exist in the Museum archive and its mirrors. As of March 2022, all of the archives on the public Internet, and over a dozen personal archives from the community have been indexed, so its now considered highly unlikely there will ever again be significant changes to this file, and its been renamed to indicate its long-term archival status.

Note this file also contains some post-shut down app development by the community that never existed in the HP/Palm App Catalog. Since such development has slowed down, as of March 2022, any new apps submitted will no longer be added to this catalog file, and will instead be added to a secondary catalog file...

### newerAppData.json

This secondary catalog file contains apps developed post-shut down and post-freeze. Apps referenced by this file cannot be stored in the App Museum Archive or its mirrors and must be hosted elsewhere. For compatibility with older devices, hosts should support HTTP access (webOS devices have trouble with modern HTTPS).

### outofdataAppData.json

This file is used when an out-of-date version of the App Museum II front-end attempts to query the catalog, and directs users to update their client app.

## The Rest of the Archive

While this project represents the back-end (and web-based front-end) of App Museum II, it depends on archived content that has been preserved by the community. webOS Archive does not host any binaries. By changing the `WebService/config.php` file you can point to community hosts for each set of content.

+ **AppMetadata**: Detailed app meta data for each app. Available in this [GitHub repository](https://www.github.com/codepoet80/webos-catalog-metadata).
+ **AppImages**: Art (icon and screenshot) files for each app. Available in this [archive](https://archive.org/details/webOSAppCatalogArchive-Complete).
+ **AppPackages**: Installable IPKs (apps) preserved from the HP/Palm App Catalog. Also available in this [archive](https://archive.org/details/webOSAppCatalogArchive-Complete).

Links to archives of the entire App Museum II can be found at [appcatalog.webosarchive.org](http://appcatalog.webosarchive.org). 

The full historical dataset includes other files that, for various reasons, are not a part of the Museum. These files are also a part of this [complete archive](https://archive.org/details/webOSAppCatalogArchive-Complete). Mirrors, like the host, must be not-for-profit.

## What is This?

This is the back-end of an app museum for the defunct mobile webOS platform, made by Palm and later acquired by HP. The platform ran on devices like the Palm Pre or Pixi, or the HP Pre3 or TouchPad. 

webOS technology was acquired by LG and repurposed for TVs and IoT devices, but they made significant changes and this app will not run on those platforms.

Releases of this app, and many other new and restored apps, can be found in the [webOS Archive App Museum](http://appcatalog.webosarchive.org).

## Why?

Aside from being a fan of the platform, the author thinks consumers have lost out now that the smart phone ecosystem has devolved into a duopoly.
Apple and Google take turns copying each other, and consumers line up to buy basically the same new phone every year. The era when webOS, Blackberry and 
Windows Phone were serious competitors was marked by creativity in form factor and software development, which has been lost. This app represents a (futile)
attempt to keep webOS mobile devices useful for as long as possible.

The website [www.webosarchive.org](http://webosarchive.org) recovers, archives and maintains material related to development, and hosts services
that restore functionality to webOS devices. A small but active [community](http://www.webosarchive.org/discord) of users take advantage of these services to keep their retro devices alive.

## How?

Mobile webOS was truly a web-derived platform. Based on Linux, and able to run properly compiled Linux binaries, developers could get raw resource access (including GPU) through a PDK (Plug-in Development Kit) but most apps were written in some flavor of Javascript, interacting with a WebKit-based browser. The toolkits were heavily optimized for the devices, and web-based apps usually felt pretty close to native. Services could be written using NodeJS and talk to each other through API calls structured to look like web service calls. App front-ends were usually written in either the Mojo (pre-tablet) or Enyo (tablet and newer phones) Javascript frameworks. Enyo apps can often be run with only minor modification in any WebKit based browser (eg: Safari or Chrome).

You can learn more about these frameworks at the restored [SDK](http://sdk.webosarchive.org).

webOS devices can be found cheaply on eBay, and while the phones will cease to be useful as phones when the 3G shutdown is through, both the phones and the Touchpad can be used around the home for a variety of [fun and useful things](http://www.webosarchive.org/docs/thingstotry/).

If you have a device, instructions for activating, getting online and getting apps installed can be found in the [webOS Archive Docs section](http://www.webosarchive.org/docs/activate/).

