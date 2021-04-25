<?php 

require "functions.php";

$species = $_GET["s"];
$fn = get_bird_image_filename($species);
if ($species == "Raphus cucullatus (dodo)") {
    $fn = __DIR__ . "/about-images/dodo.jpg";
}
if ($species == 'Charlie “Bird” Parker') {
    $fn = __DIR__ . "/about-images/charlie_parker.jpg";
}
if ($species == "Thunderbird 2") {
    $fn = __DIR__ . "/about-images/thunderbird_2.jpg";
}


if (!file_exists($fn)) {
    die("No such bird image.");
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
