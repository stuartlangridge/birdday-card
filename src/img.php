<?php 

require "functions.php";

if (isset($_GET["nocache"])) {
    decho("Wiping data cache as nocache is set");
    $files = glob('/var/www/cache/*'); // get all file names
    foreach($files as $file){ // iterate files
        if(is_file($file)) {
            unlink($file); // delete file
        }
    }
}

list($lat, $lon, $data_cache_key) = validate();

$fn = get_filename($lat, $lon);
if (!file_exists($fn) || isset($_GET["debug"])) {
    create_save_image($fn, $lat, $lon, $data_cache_key);
}
if (file_exists($fn)) {
    if (isset($_GET["debug"])) {
        decho("Image saved OK but not writing it because of debug mode.");
        exit;
    }
    header('Content-Description: File Transfer');
    header('Content-Type: image/jpeg');
    header('Content-Length: ' . filesize($fn));
    readfile($fn);
    exit;
} else {
    decho("image failure");
}
