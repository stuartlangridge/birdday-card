<?php
require(__DIR__ . "/../vendor/autoload.php");
use fiftyone\pipeline\geolocation\GeoLocationPipelineBuilder;

$first_debug_print = TRUE;
function decho($s) {
    global $first_debug_print;
    if (isset($_GET["debug"])) {
        if ($first_debug_print) {
            echo "<pre>";
            $first_debug_print = FALSE;
        }
        echo $s . "\n";
    }
}

function get_filename($lat, $lon) {
    $fn = __DIR__ . "/../images/img_" . $lat . "__" . $lon . ".jpg";
    decho("Filename from $lat and $lon is $fn");
    return $fn;
}

function get_cache_filename($key) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '=', $key)));
    if (strlen($slug) > 100) {
        $nslug = substr($slug, 0, 100) . "-hash-" . md5($slug);
        decho("Slug $slug is too long; shortening with hash to $nslug");
        $slug = $nslug;
    }
    $fn = __DIR__ . "/../cache/$slug";
    return $fn;
}

function get_cache_key($key) {
    decho("Checking cache for key $key");
    if (isset($_GET["skipcache"])) {
        decho("Skipping cache as commanded");
        return null;
    }
    $fn = get_cache_filename($key);
    if (file_exists($fn)) {
        decho("Returning cached data for $key");
        return file_get_contents($fn);
    }
    decho("No cache for key $key");
    return null;
}

function store_cache_key($key, $data) {
    decho("Storing data in cache for key $key");
    $fn = get_cache_filename($key);
    file_put_contents($fn, $data);
}

function cached_json($url) {
    $data = get_cache_key($url);
    if (!$data) {
        $data = file_get_contents($url);
        store_cache_key($url, $data);
    }
    $as_json = json_decode($data, true);
    return $as_json;
}

function geoloc51d($lat, $lon) {
    /* Given a latitude and longitude, return a string suitable for looking up this location in wikidata */

    $cache_key = "51d," . $lat . "," . $lon;
    $cached_locations = get_cache_key($cache_key);
    if ($cached_locations) {
        return json_decode($cached_locations);
    }

    $resourceKey = $_ENV["RESOURCEKEY"];
    $settings = array("resourceKey" => $resourceKey, "locationProvider" => "fiftyonedegrees");
    $builder = new GeoLocationPipelineBuilder($settings);
    $pipeline = $builder->build();
    $flowData = $pipeline->createFlowData();
    $flowData->evidence->set('query.51D_Pos_latitude', $lat);
    $flowData->evidence->set('query.51D_Pos_longitude', $lon);

    $result = $flowData->process();

    $town = null; $region = null; $state = null; $country = null;
    try { $town = $flowData->location->town; } catch(Exception $e) {}
    try { $region = $flowData->location->region; } catch(Exception $e) {}
    try { $state = $flowData->location->state; } catch(Exception $e) {}
    try { $country = $flowData->location->country; } catch(Exception $e) {}

    if (!$town->hasValue) $town = null;
    if (!$region->hasValue) $region = null;
    if (!$state->hasValue) $state = null;
    if (!$country->hasValue) $country = null;

    // names we use to look for imagery, falling back to the next one if one has no image or no search result
    // only include a name if all parts of it are present
    // town+region+state+country, town+region+country, town+state+country, region+state+country,
    // town+country, region+country, state+country, country
    $names = array();

    if ($town && $region && $state && $country) {
        $names[] = [join(", ", [$town->value, $region->value, $state->value, $country->value]), "town+region+state+country"];
    }
    if ($town && $region && $country) {
        $names[] = [join(", ", [$town->value, $region->value, $country->value]), "town+region+country"];
    }
    if ($town && $state && $country) {
        $names[] = [join(", ", [$town->value, $state->value, $country->value]), "town+state+country"];
    }
    if ($region && $state && $country) {
        $names[] = [join(", ", [$region->value, $state->value, $country->value]), "region+state+country"];
    }
    if ($town && $country) {
        $names[] = [join(", ", [$town->value, $country->value]), "town+country"];
    }
    if ($region && $country) {
        $names[] = [join(", ", [$region->value, $country->value]), "region+country"];
    }
    if ($state && $country) {
        $names[] = [join(", ", [$state->value, $country->value]), "state+country"];
    }
    if ($country) {
        $names[] = [$country->value, "country"];
    }
    store_cache_key($cache_key, json_encode($names));
    return $names;
}

function wikidata_image($text_array, $width) {
    // Search wikidata for our query text and get the first matching entity ID
    if (count($text_array) == 0) {
        decho("No wikidata search terms left. Bailing.");
        return null;
    }
    $item = $text_array[0];
    if (is_array($item)) {
        $itemtype = $item[1];
        $item = $item[0];
    } else {
        $itemtype = "unspecified";
    }
    decho("Wikidata search for $item of type $itemtype");
    $qsl = array(
        "action" => "query",
        "list" => "search",
        "srsearch" => $item,
        "format" => "json"
    );
    $url = "https://www.wikidata.org/w/api.php?" . http_build_query($qsl);
    decho("wd search for $item as $url");
    try {
        $wdo = cached_json($url);
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
    $entitypublicurl = "https://www.wikidata.org/wiki/Special:EntityData/$entityid";
    decho("Get entity details from $entity_url");
    try {
        $wdo = cached_json($entity_url);
    } catch(Exception $e) {
        decho("Couldn't find an image for $item so trying with next one");
        \array_splice($text_array, 0, 1);
        return wikidata_image($text_array, $width);
    }

    // an image is claim type P18, in wikidata language
    // a banner image is claim type P948, which we check for as well
    try {
        $imgname = $wdo["entities"][$entityid]["claims"]["P18"][0]["mainsnak"]["datavalue"]["value"];
    } catch(Exception $e) {
        decho("Couldn't find an image entry for $item so trying with next one");
        \array_splice($text_array, 0, 1);
        return wikidata_image($text_array, $width);
    }
    if (!$imgname) {
        try {
            decho("Looking for page banner image because no image was found");
            $imgname = $wdo["entities"][$entityid]["claims"]["P948"][0]["mainsnak"]["datavalue"]["value"];
        } catch(Exception $e) {
            decho("Couldn't find an image entry for $item so trying with next one");
            \array_splice($text_array, 0, 1);
            return wikidata_image($text_array, $width);
        }
    }
    if (!$imgname || strlen($imgname) == 0 || !stripos($imgname, ".jpg")) {
        decho("Couldn't find a .jpg image for $item so trying with next one");
        \array_splice($text_array, 0, 1);
        return wikidata_image($text_array, $width);
    }
    $picurl = "http://commons.wikimedia.org/wiki/Special:FilePath/" . rawurlencode($imgname) . "?width=" . $width;
    return array($picurl, $entitypublicurl);
}

function xeno_canto($lat, $lon) {
    $url = "https://www.xeno-canto.org/api/2/recordings?query=lat:$lat%20lon:$lon";
    $publicurl = "https://www.xeno-canto.org/explore?query=lat%3A$lat%20lon%3A$lon";
    try {
        $xdo = cached_json($url);
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
                list($birdimg, $birdpublic) = wikidata_image([$birdspec], 400);
                if ($birdimg) {
                    decho("Got bird image $birdimg");
                    $birds[] = array(
                        "image" => $birdimg,
                        "wikidata" => $birdpublic,
                        "species" => $birdspec,
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
    return array($birds, $publicurl);
}



function scaler($im, $w, $h) {
    // scales and centre crops to the required size
    $origw = imagesx($im);
    $origh = imagesy($im);
    $scalew = $origw / $w;
    $scaleh = $origh / $h;
    if ($scalew > $scaleh) {
        $nw = ceil($origw / $scaleh);
        $nh = ceil($origh / $scaleh);
    } else {
        $nw = ceil($origw / $scalew);
        $nh = ceil($origh / $scalew);
    }
    decho("Scaling image from $origw $origh to $nw $nh to be bigger than $w $h");
    $n = imagescale($im, $nw, $nh);
    decho("Scaled image to new handle");
    if ($n === FALSE) {
        decho("Image scale failed!");
    }
    if ($nw > $w) {
        $cx = ceil(($nw - $w) / 2);
        $cy = 0;
    } else if ($nh > $h) {
        $cx = 0;
        $cy = ceil(($nh - $h) / 2);
    }
    decho("Cropping image of size $nw $nh to be size $w $h from point $cx $cy");
    $n = imagecrop($n, array("x" => $cx, "y" => $cy, "width" => $w, "height" => $h));
    return $n;
}

function add_bird($base, $bird, $w, $h, $x, $y, $border) {
    decho("Loading bird image: " . $bird);
    $b1 = @imagecreatefromjpeg($bird);
    if ($b1 === FALSE) {
        decho("Couldn't load bird image $bird!");
        return;
    }
    $b1 = scaler($b1, $w, $h);
    imagecopymerge($base, $b1, $x, $y, 0, 0, $w, $h, 100);
    $borderimg = @imagecreatefrompng($border);
    imagecopy($base, $borderimg, $x, $y, 0, 0, $w, $h);
}

function create_save_image($fn, $lat, $lon, $data_cache_key) {
    $loc_descriptions = geoloc51d($lat, $lon);
    $loc_descriptions[] = ["Area 51", "last fallback"]; // ultimate fallback if we have no location images
    $loc_descriptions_combined = [];
    foreach ($loc_descriptions as $tn) {
        $loc_descriptions_combined[] = $tn[0];
    }
    $loc_descriptions_combined = join(" | ", $loc_descriptions_combined);
    decho("Location descriptions are $loc_descriptions_combined");
    list($image_url, $townurl) = wikidata_image($loc_descriptions, 1000);
    decho("Base image URL is $image_url");
    list($birds, $xenourl) = xeno_canto($lat, $lon);
    decho("Got birds");

    store_cache_key($data_cache_key, json_encode(array(
        "birds" => $birds,
        "xenourl" => $xenourl,
        "location" => $loc_descriptions[0],
        "town" => $townurl
    )));

    // required size for output image
    $outw = 800;
    $outh = 540;

    // Get base image into a GD image object
    decho("Loading base image $image_url");
    $base = imagecreatefromjpeg($image_url);
    if ($base === FALSE) {
        decho("Couldn't load $image_url");
        die();
    }

    // Scale base image to our desired size
    decho("Scaling base image $image_url to size $outw $outh");
    $base = scaler($base, $outw, $outh);

    // composite birds onto base image
    add_bird($base, $birds[0]["image"], 200, 120, 300, 30, "decoration/coolasianframe.png");
    add_bird($base, $birds[1]["image"], 200, 120, 133, 180, "decoration/simpleoldornateframe.png");
    add_bird($base, $birds[2]["image"], 200, 120, 466, 180, "decoration/wonderfulcelcticdragons.png");

    // slogan
    $slogan = "HAPPY BIRDDAY";
    $font = "bettynoir.ttf";
    $distance_from_bottom = 0;
    $fontsize = 80;
    $col = imagecolorallocate ($base, 255, 255, 0);
    $tb = imagettfbbox($fontsize, 0, $font, $slogan);
    $tw = $tb[4] - $tb[6];
    $th = $tb[1] - $tb[7];
    $x = ($outw - $tw) / 2;
    $y = $outh - $th - $distance_from_bottom;
    decho("Slogan bounding box is: " . json_encode($tb) . " (i.e., $tw x $th in size)");
    decho("So positioning at $x $y");
    imagettftext ($base, $fontsize, 0, $x, $y, $col, $font, $slogan);

    // write final image to disk
    imagejpeg($base, $fn);
}

function validate() {
    /* Validate parameters */
    $lat_options = array('options' => array('default' => 0, 'min_range' => -90, 'max_range' => 90));
    $lon_options = array('options' => array('default' => 0, 'min_range' => -180, 'max_range' => 180));
    $lat = filter_var($_GET["lat"], FILTER_VALIDATE_FLOAT, $lat_options);
    $lon = filter_var($_GET["lon"], FILTER_VALIDATE_FLOAT, $lon_options);
    $data_cache_key = "data," . $lat . "," . $lon;
    return array($lat, $lon, $data_cache_key);
}