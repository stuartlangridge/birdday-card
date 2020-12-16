<?php

require "functions.php";

list($lat, $lon, $data_cache_key) = validate();

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>What three birds? - Birdday Card</title>
<link rel="stylesheet" href="styles.css">
<style>

#card {
    background: rgba(255, 255, 255, 0.2);
    color: black;
    display: flex;
    align-items: center;
    justify-content: center;
}
figure {
    position: relative;
    width: 90vw;
    height: calc(90vw * 0.635);
 /*   background: url(decoration/Tropical-Foliage-Quadrilateral.png); */
    background-size: contain;
    overflow: hidden;
    clip-path: polygon(0 0, 800px 0, 800px 100%, 0 100%);
}
/*
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
*/
figure::before, figure::after {
    content: "";
    background-image: url(decoration/bird-cells.svg);
    background-size: auto 100%;
    width: 800px;
    clip-path: polygon(0 0, 88px 0, 88px 125px, 0 125px);
    height: 125px;
    will-change: background-position;
    animation-name: fly-cycle, scrolldown;
    animation-timing-function: steps(10), linear;
    animation-duration: 1s, 2.8s;
    animation-iteration-count: infinite, infinite;
    position: absolute;
    top: 160px;
    left: 0;
    background-color: transparent;
    z-index: -1;
}
figure::after {
    top: 400px
    animation-delay: 0.2s, -0.6s;
    animation-duration: 0.51s, 2.3s;
    animation-name: fly-cycle, scrollup;
}
@keyframes fly-cycle { 100% { background-position: -900px 0; } }
@keyframes scrollup { 0% { transform: translateX(-88px); } 100% { transform: translateX(100%) translateY(-150%); } }
@keyframes scrolldown { 0% { transform: translateX(-88px); } 100% { transform: translateX(100%) translateY(150%); } }
</style>
</head>
<body>
	<header>
<h1>What Three Birds?</h1>
</header>
<main>
<figure>
    <img id="card" src="img.php?lat=<?php echo $lat; ?>&amp;lon=<?php echo $lon; ?>"
         alt="loading your card..." width="800" height="540" onerror="imgfail()" onload="imgsuccess()">
</figure>
<div id="audios"></div>
<p><a href="./">Make your own “birdday card”</a></p>
<p><a href="details.php?lat=<?php echo $lat; ?>&amp;lon=<?php echo $lon; ?>">Learn about this birdday card</a></p>
<p><a href="about.html">Learn about <strong>What Three Birds?™&copy;&reg;</strong> and how it works</a></p>
</main>
<footer><small>Made by <a href="https://kryogenix.org/">Stuart Langridge</a> (<a
	href="https://twitter.com/sil">@sil</a>) and <a href="https://brucelawson.co.uk">Bruce Lawson</a> (<a
	href="https://twitter.com/brucel">@brucel</a>). The header drawing is by <a
	href="https://openclipart.org/detail/219787/owl-and-a-birds">Rones</a>. Not many birds were harmed during the coding of this website (Bruce ate a chicken sandwich while writing the CSS). Source is on Github, licensed under the <a href="https://web.archive.org/web/20140924010836/http://wiseearthpublishers.com/sites/wiseearthpublishers.com/files/PeacefulOSL.txt">Peaceful Open Source License</a>.</small>
</footer>
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

