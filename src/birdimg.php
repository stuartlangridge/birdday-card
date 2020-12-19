<?php 

require "functions.php";

$species = $_GET["s"];
$fn = get_bird_image_filename($species);

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
