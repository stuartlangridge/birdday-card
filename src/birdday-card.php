<?php

require "functions.php";

list($lat, $lon, $data_cache_key) = validate();

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>What three birds? - Birdday Card</title>
<style>
html {
    background: #6dac8530;
    min-height: 100vh;
    font-family: Roboto, sans-serif;
    padding: 1.5rem 0;
    font-size: min(max(1rem, 4vw), 22px);
}
body {
    padding-left: 1.5rem; padding-right: 1.5rem;
}
h1 {
    color: #522D7E;
    font-family: "Luckiest Guy", cursive;
    font-size: 2rem;
    line-height: 2rem;
    margin-top: calc((1.5rem - 2rem) + 1.5rem);
    margin-bottom: 1.5rem;
    font-size: min(max(1rem, 8vw), 60px);
    text-align: center;
    text-shadow: 0 0 2px rgba(255, 255, 255, 0.8);
}
#card {
    background: #6dac85;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
}
figure {
    position: relative;
    width: 90vw;
    height: calc(90vw * 0.635);
    background: url(decoration/Tropical-Foliage-Quadrilateral.png);
    background-size: contain;
}
figure img {
    position: absolute;
    top: 19.64%;
    left: 18.88%;
    width: 57.1%;
    height: 60.606%;
}
figure::after {
    content: "";
    position: absolute;
    z-index: 2;
    top: 19.64%;
    left: 18.88%;
    width: 57.1%;
    height: 60.606%;
    box-shadow: inset 0px 0px 50px #6dac85;
}
</style>
</head>
<body>
<h1>What Three Birds?</h1>
<figure>
    <img id="card" src="img.php?lat=<?php echo $lat; ?>&amp;lon=<?php echo $lon; ?>"
         alt="loading your card..." width="800" height="540" onerror="imgfail()" onload="imgsuccess()">
</figure>
<div id="audios"></div>
<p><a href="./">Make your own “birdday card”</a></p>
<p><a href="details.php?lat=<?php echo $lat; ?>&amp;lon=<?php echo $lon; ?>">Learn about this birdday card</a></p>
<p><a href="about.html">Learn about <strong>What Three Birds?™&copy;&reg;</strong> and how it works</a></p>
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

