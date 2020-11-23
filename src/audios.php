<?php 

require "functions.php";

list($lat, $lon, $data_cache_key) = validate();

header("Content-Type: application/json");

$data = get_cache_key($data_cache_key);
if (!$data) {
    echo json_encode(array("error" => "That card has never been generated."));
    die();
}
$data = json_decode($data, true);

$audios = array();
foreach ($data["birds"] as $key => $value) {
    $audios[] = array("src" => $value["sound_url"], "species" => $value["species"]);
}
echo json_encode($audios);
