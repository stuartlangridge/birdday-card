<?php 

require(__DIR__ . "/../vendor/autoload.php");

use fiftyone\pipeline\geolocation\GeoLocationPipelineBuilder;

$first_debug_print = TRUE;
function decho($s) {
    global $first_debug_print;
    if ($first_debug_print) {
        echo "<pre>";
        $first_debug_print = FALSE;
    }
    if (isset($_GET["debug"])) echo $s . "\n";
}

function geoloc51d($lat, $lon) {
    /* Given a latitude and longitude, return a string suitable for looking up this location in wikidata */
    $resourceKey = $_ENV["RESOURCEKEY"];
    $settings = array("resourceKey" => $resourceKey, "locationProvider" => "fiftyonedegrees");
    $builder = new GeoLocationPipelineBuilder($settings);
    $pipeline = $builder->build();
    $flowData = $pipeline->createFlowData();
    $flowData->evidence->set('query.51D_Pos_latitude', $lat);
    $flowData->evidence->set('query.51D_Pos_longitude', $lon);

    $result = $flowData->process();

    $names_town_region = array();
    $names_town = array();
    $names_region = array();

    $town = null; $region = null; $state = null; $country = null;
    try { $town = $flowData->location->town; } catch(Exception $e) {}
    try { $region = $flowData->location->region; } catch(Exception $e) {}
    try { $state = $flowData->location->state; } catch(Exception $e) {}
    try { $country = $flowData->location->country; } catch(Exception $e) {}

    if ($town && $town->hasValue) {
        $names_town_region[] = $town->value;
        $names_town[] = $town->value;
    }
    if ($region && $region->hasValue) {
        $names_region[] = $region->value;
        $names_town_region[] = $region->value;
    }
    if ($state && $state->hasValue) {
        $names_town[] = $state->value;
        $names_region[] = $state->value;
        $names_town_region[] = $state->value;
    }
    if ($country && $country->hasValue) {
        $names_town[] = $country->value;
        $names_region[] = $country->value;
        $names_town_region[] = $country->value;
    }
    return [
        join($names_town_region, ", "),
        join($names_town, ", "),
        join($names_region, ", "),
    ];
    
}

function wikidata_image($text_array, $width) {
    // Search wikidata for our query text and get the first matching entity ID
    if (count($text_array) == 0) {
        decho("No wikidata search terms left. Bailing.");
        return null;
    }
    $item = $text_array[0];
    $items_combined = join($text_array, " | ");
    decho("Wikidata search for $item from $items_combined");
    $qsl = array(
        "action" => "query",
        "list" => "search",
        "srsearch" => $item,
        "format" => "json"
    );
    $url = "https://www.wikidata.org/w/api.php?" . http_build_query($qsl);
    decho("wd search for $item as $url");
    $wd = file_get_contents($url);
    try {
        $wdo = json_decode($wd, true);
    } catch(Exception $e) {
        decho("No JSON search for $item so trying with next one");
        \array_splice($text_array, 0, 1);
        return wikidata_image($text_array, $width);
    }
    if (count($wdo["query"]["search"]) == 0) {
        decho("No wikidata search results for $item so trying with next one");
        \array_splice($text_array, 0, 1);
        return wikidata_image($text_array, $width);
    }
    $entityid = $wdo["query"]["search"][0]["title"];
    decho("Wikidata entity: $entityid");

    // get the data for that entity ID (in theory this could be done with SPARQL but life's too short)
    $entity_url = "https://www.wikidata.org/wiki/Special:EntityData/$entityid.json";
    decho("Get entity details from $entity_url");
    $wd = file_get_contents($entity_url);
    try {
        $wdo = json_decode($wd, true);
    } catch(Exception $e) {
        decho("Couldn't find an image for $item so trying with next one");
        \array_splice($text_array, 0, 1);
        return wikidata_image($text_array, $width);
    }

    // an image is claim type P18, in wikidata language
    try {
        $imgname = $wdo["entities"][$entityid]["claims"]["P18"][0]["mainsnak"]["datavalue"]["value"];
    } catch(Exception $e) {
        decho("Couldn't find an image entry for $item so trying with next one");
        \array_splice($text_array, 0, 1);
        return wikidata_image($text_array, $width);
    }
    if (!$imgname || strlen($imgname) == 0) {
        decho("Couldn't find an image for $item so trying with next one");
        \array_splice($text_array, 0, 1);
        return wikidata_image($text_array, $width);
    }
    $picurl = "http://commons.wikimedia.org/wiki/Special:FilePath/" . rawurlencode($imgname) . "?width=" . $width;
    return $picurl;
}

function xeno_canto($lat, $lon) {
    $url = "https://www.xeno-canto.org/api/2/recordings?query=lat:$lat%20lon:$lon";
    $xd = file_get_contents($url);
    try {
        $xdo = json_decode($xd, true);
    } catch(Exception $e) { decho("no xeno json"); return null; }
    $birds = array();
    $species = array();
    $i = 0;
    while (true) {
        decho("Checking bird recording $i");
        if (count($xdo["recordings"]) > $i) {
            $birdspec = $xdo["recordings"][$i]["gen"] . " " . $xdo["recordings"][$i]["sp"];
            decho("Bird is $birdspec");
            if (!in_array($birdspec, $species)) {
                $species[] = $birdspec;
                $birdimg = wikidata_image([$birdspec], 400);
                if ($birdimg) {
                    decho("Got bird image $birdimg");
                    $birds[] = array(
                        "image" => $birdimg,
                        "sound_url" => $xdo["recordings"][$i]["file"]
                    );
                    if (count($birds) == 3) break;
                } else {
                    decho("No image for $birdspec");
                }
            } else {
                decho("Already got $birdspec");
            }
        } else {
            decho("No such recording; bail.");
            break;
        }
        $i += 1;
    }
    return $birds;
}

function create_save_image($fn, $lat, $lon) {
    $loc_descriptions = geoloc51d($lat, $lon);
    $loc_descriptions[] = "field"; // ultimate fallback if we have no location images
    $loc_descriptions_combined = join($loc_descriptions, " | ");
    decho("Location descriptions are $loc_descriptions_combined");
    $image_url = wikidata_image($loc_descriptions, 1000);
    decho("Base image URL is $image_url");
    $birds = xeno_canto($lat, $lon);
    decho("Got birds");
    $base = imagecreatefromjpeg($image_url);
    if ($base === FALSE) {
        decho("COuldn't load $image_url");
        die();
    }

    $outw = 800;
    $outh = 600;
    $origw = imagesx($base);
    $origh = imagesy($base);
    $scalew = $outw / $origw;
    $scaleh = $outh / $origh;
    if ($scalew > $scaleh) {
        $nbase = imagescale($base, $outw, -1);
    } else {
        $nbase = imagescale($base, $origw * $scaleh);
    }
    imagejpeg($nbase, $fn);
}

function get_filename($lat, $lon) {
    $fn = "/var/www/images/img_" . $lat . "__" . $lon . ".jpg";
    decho("Filename from $lat and $lon is $fn");
    return $fn;
}

/* Validate parameters */
$lat_options = array('options' => array('default' => 0, 'min_range' => -90, 'max_range' => 90));
$lon_options = array('options' => array('default' => 0, 'min_range' => -180, 'max_range' => 180));
$lat = filter_var($_GET["lat"], FILTER_VALIDATE_FLOAT, $lat_options);
$lon = filter_var($_GET["lon"], FILTER_VALIDATE_FLOAT, $lon_options);

/* Serve HTML, or image */
if ($_GET["type"] == "img") {
    $fn = get_filename($lat, $lon);
    if (!file_exists($fn)) {
        create_save_image($fn, $lat, $lon);
    }
    if (file_exists($fn)) {
        header('Content-Description: File Transfer');
        header('Content-Type: image/jpeg');
        header('Content-Length: ' . filesize($fn));
        readfile($fn);
        exit;
    } else {
        decho("image failure");
    }
} else {
    echo "<html><body>page! <img src='index.php?lat=$lat&amp;lon=$lon&amp;type=img' alt='bad img' style='background-color:red' width='800' height='600'>";
}

