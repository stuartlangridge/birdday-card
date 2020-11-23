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

?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Birdday Card</title>
<style>
#card {
    background:
        radial-gradient(hsl(0, 100%, 27%) 4%, hsl(0, 100%, 18%) 9%, hsla(0, 100%, 20%, 0) 9%) 0 0,
        radial-gradient(hsl(0, 100%, 27%) 4%, hsl(0, 100%, 18%) 8%, hsla(0, 100%, 20%, 0) 10%) 50px 50px,
        radial-gradient(hsla(0, 100%, 30%, 0.8) 20%, hsla(0, 100%, 20%, 0)) 50px 0,
        radial-gradient(hsla(0, 100%, 30%, 0.8) 20%, hsla(0, 100%, 20%, 0)) 0 50px,
        radial-gradient(hsla(0, 100%, 20%, 1) 35%, hsla(0, 100%, 20%, 0) 60%) 50px 0,
        radial-gradient(hsla(0, 100%, 20%, 1) 35%, hsla(0, 100%, 20%, 0) 60%) 100px 50px,
        radial-gradient(hsla(0, 100%, 15%, 0.7), hsla(0, 100%, 20%, 0)) 0 0,
        radial-gradient(hsla(0, 100%, 15%, 0.7), hsla(0, 100%, 20%, 0)) 50px 50px,
        linear-gradient(45deg, hsla(0, 100%, 20%, 0) 49%, hsla(0, 100%, 0%, 1) 50%, hsla(0, 100%, 20%, 0) 70%) 0 0,
        linear-gradient(-45deg, hsla(0, 100%, 20%, 0) 49%, hsla(0, 100%, 0%, 1) 50%, hsla(0, 100%, 20%, 0) 70%) 0 0;
    background-color: #300;
    background-size: 100px 100px;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
</head>
<body>
<h1>Birdday Card</h1>
<img id="card" src="img.php?lat=<?php echo $lat; ?>&amp;lon=<?php echo $lon; ?>"
     alt="loading your card..." width="800" height="540">
<p><a href="./">Make your own birdday card</a></p>
<p><a href="details.php?lat=<?php echo $lat; ?>&amp;lon=<?php echo $lon; ?>">Learn about this birdday card</a></p>
</body>
</html>

