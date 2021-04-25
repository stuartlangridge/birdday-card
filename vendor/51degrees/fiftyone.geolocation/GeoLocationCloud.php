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
 * ********************************************************************* */

namespace fiftyone\pipeline\geolocation;

require(__DIR__ . "/vendor/autoload.php");

use fiftyone\pipeline\cloudrequestengine\CloudEngine;

class GeoLocationCloud extends CloudEngine
{

    /**
        * Constructor for GeolocationCloud
        * If property not found, call the attached missing property service
        * @param string locationProvider (digitalelement or fiftyonedegrees)
    */
    public function __construct($settings = array("locationProvider" => "fiftyonedegrees"))
    {
        $locationProvider = $settings["locationProvider"];

        if ($locationProvider !== "fiftyonedegrees" && $locationProvider !== "digitalelement") {
            throw new \Exception("Location provider should be fiftyonedegrees or digitalelement");
        }

        if ($locationProvider === "digitalelement") {
            $this->dataKey = "location_digitalelement";
        } else {
            $this->dataKey = "location";
        }
    }
}
