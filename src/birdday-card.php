<?php 

require "functions.php";

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
     alt="loading your card..." width="800" height="540" onerror="imgfail()" onload="imgsuccess()">
<div id="audios"></div>
<p><a href="./">Make your own birdday card</a></p>
<p><a href="details.php?lat=<?php echo $lat; ?>&amp;lon=<?php echo $lon; ?>">Learn about this birdday card</a></p>
<script>
function imgfail() {
    console.log("image didn't load. Do something relevant.");
}
function imgsuccess() {
    console.log("image loaded OK, in an old browser.");
}
</script>
<script module async>
async function imgsuccess() {
    try {
        const response = await fetch("audios.php?lat=<?php echo $lat; ?>&lon=<?php echo $lon; ?>");
        if (!response.ok) {
            console.log(`Bad response from audios (${response.status}); bailing.`);
            return;
        }
        const audios = await response.json();
        const container = document.getElementById("audios");
        audios.forEach(adata => {
            const audio = document.createElement("audio");
            const acontainer = document.createElement("div");
            const span = document.createElement("span");
            audio.controls = true;
            audio.src = adata.src;
            span.append(adata.species);
            acontainer.append(span);
            acontainer.append(audio);
            container.append(acontainer);
        })
    } catch(e) {
        console.log("Error fetching audios, so ignoring.", e);
    }
}
</script>
</body>
</html>

