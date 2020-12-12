<?php
/* *********************************************************************
 * This Original Work is copyright of 51 Degrees Mobile Experts Limited.
 * Copyright 2019 51 Degrees Mobile Experts Limited, 5 Charlotte Close,
 * Caversham, Reading, Berkshire, United Kingdom RG4 7BY.
 *
 * This Original Work is licensed under the European Union Public Licence (EUPL)
 * v.1.2 and is subject to its terms as set out below.
 *
 * If a copy of the EUPL was not distributed with this file, You can obtain
 * one at https://opensource.org/licenses/EUPL-1.2.
 *
 * The 'Compatible Licences' set out in the Appendix to the EUPL (as may be
 * amended by the European Commission) shall be deemed incompatible for
 * the purposes of the Work and the provisions of the compatibility
 * clause in Article 5 of the EUPL shall not apply.
 *
 * If using the Work as, or as part of, a network application, by
 * including the attribution notice(s) required under Article 5 of the EUPL
 * in the end user terms of the application under an appropriate heading,
 * such notice(s) shall fulfill the requirements of that article.
 * ********************************************************************* /*
/*
 * @example cloud/combiningservices.php
 *
 * Example of using the 51Degrees geo-location Cloud alongside 51Degrees device
 * detection to determine the country and device for a given longitude, latitude and
 *  User-Agent.
 *
 * To run this example you will need to include the Device Detection Cloud engine from the device detection package.
 *
 * This example is available in full on [GitHub](https://github.com/51Degrees/location-php/blob/master/examples/cloud/combiningservices.php).
 *
 * To run this example, you will need to create a **resource key**.
 * The resource key is used as short-hand to store the particular set of
 * properties you are interested in as well as any associated license keys
 * that entitle you to increased request limits and/or paid-for properties.
 * You can create a resource key using the 51Degrees [Configurator](https://configure.51degrees.com/vHk79wZn).
 * 
 * This example uses the 'Country' and 'IsMobile' properties, which are
 * pre-populated when creating a key using the link above.
 *
 * Expected output
 * ```
 * Which country is the location [51.458048,-0.9822207999999999] in?
 * UK
 *
 * Is user agent 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:78.0) Gecko/20100101 Firefox/78.0' a mobile device?
 * No
 * ```
*/

// First we include the GeolocationPipelineBuilder

require(__DIR__ . "/../../vendor/autoload.php");

use fiftyone\pipeline\geolocation\GeoLocationPipelineBuilder;
use fiftyone\pipeline\devicedetection\DeviceDetectionCloud;


// Check if there is a resource key in the environment variable and use
// it if there is one. (This is used for automated testing)
if (isset($_ENV["RESOURCEKEY"])) {
    $resourceKey = $_ENV["RESOURCEKEY"];
} else {
    $resourceKey = "!!YOUR_RESOURCE_KEY!!";
}

if (substr($resourceKey, 0, 2) === "!!") {
    echo "You need to create a resource key at " .
        "https://configure.51degrees.com/vHk79wZn and paste it into " . 
        "the code, replacing !!YOUR_RESOURCE_KEY!!.";
    return;
}

// Create settings for the pipeline builder
// This includes:
// * The location provider: "digitalelement" or "fiftyonedegrees"
// * The resource key
$settings = array(
    "resourceKey" => $resourceKey,
    "locationProvider" => "fiftyonedegrees"
);

// We then create a pipeline with the builder. 
$builder = new GeoLocationPipelineBuilder($settings);

// Now we add the device detection cloud engine as well

// First check if it exists
if (!class_exists("fiftyone\pipeline\devicedetection\DeviceDetectionCloud")) {
    echo "You will need to include the 51degrees/fiftyone.devicedetection package for this example to run.";
    return;
};

$builder->add(new DeviceDetectionCloud());

$pipeline = $builder->build();

$flowData = $pipeline->createFlowData();

// Example longitude and latitude data to look up
$latitude = "51.458048";
$longitude = "-0.9822207999999999";

$flowData->evidence->set('query.51D_Pos_latitude', $latitude);
$flowData->evidence->set('query.51D_Pos_longitude', $longitude);

// Example user agent to test for ismobile property

$userAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:78.0) Gecko/20100101 Firefox/78.0";

$flowData->evidence->set('header.user-agent', $userAgent);

$flowData->process();

// Show all the properties in the device element

$country = $flowData->location->country;

if ($country->hasValue) {
    echo "Which country is the location [" . $latitude . "," . $longitude . "] in?";
    echo "<br />";
    echo $country->value;
    echo "<br />";
} else {
    // Echo out why the value isn't meaningful
    echo $country->noValueMessage;
    echo "<br />";
};

echo "Is user agent '" . $userAgent . "' a mobile device?";
echo "<br />";

if ($flowData->device->ismobile->hasValue) {
    if ($flowData->device->ismobile->value) {
        print("Yes");
    } else {
        print("No");
    }
} else {
    // If it doesn't have a meaningful result, we echo out the reason why
    // it wasn't meaningful
    print($flowData->device->ismobile->noValueMessage);
}
