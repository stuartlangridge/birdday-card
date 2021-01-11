<?php

require "functions.php";

list($lat, $lon, $data_cache_key) = validate();

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
	<title>Which Three Birdies? - Birdday Card</title>
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Luckiest+Guy&family=Roboto&display=swap" rel="stylesheet">
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
		margin-bottom:2em;
        width: 90vw;
/*        height: calc(90vw * 0.635); */
        background-size: contain;
        overflow: hidden;
        clip-path: polygon(0 0, 800px 0, 800px 100%, 0 100%);
    }

    figure::before,
    figure::after {
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
        top: 400px animation-delay: 0.2s, -0.6s;
        animation-duration: 0.51s, 2.3s;
        animation-name: fly-cycle, scrollup;
    }

    @keyframes fly-cycle {
        100% {
            background-position: -900px 0;
        }
    }

    @keyframes scrollup {
        0% {
            transform: translateX(-88px);
        }

        100% {
            transform: translateX(100%) translateY(-150%);
        }
    }

    @keyframes scrolldown {
        0% {
            transform: translateX(-88px);
        }

        100% {
            transform: translateX(100%) translateY(150%);
        }
    }

    /* loading spinner */
    figure:not[aria-busy="true"] div {
        display: none;
    }

    figure[aria-busy="true"] div::before {
        content: "loading";
    }


    </style>
</head>

<body>
    <script>
    /* Scripts come first in the body, despite the slight delay, because imgsuccess has to be defined when the image loads */
    function imgfail() {
        console.log("image didn't load. Do something relevant.");
    }

    function imgsuccess() {
        console.log("image loaded OK, in an old browser.");
        document.getElementsByTagName("figure")[0].setAttribute("aria-busy", "false");
    }
    </script>
    <script module async>
    async function imgsuccess() {
        document.getElementsByTagName("figure")[0].setAttribute("aria-busy", "false");
        function squawk(count) {
            let ret = [];
            let noises = ["tweet", "twitter", "squawk", "SQUAWK", "chirp", "cheep",
                "whistle", "brrr-ha-ha-ha", "peep", "cuckoo", "hoot", "pip-pip"];
            for (var i=0; i<count; i++) {
                ret.push(noises[Math.floor(Math.random() * noises.length)]);
            }
            return ret.join(" ");
        }
        try {
            const response = await fetch("audios.php?lat=<?php echo $lat; ?>&lon=<?php echo $lon; ?>");
            if (!response.ok) {
                console.log(`Bad response from audios (${response.status}); bailing.`);
                return;
            }
            const audios = await response.json();
            console.log("got audios", audios);
            const all_container = document.getElementById("audios");
            audios.forEach(adata => {
                const bird_container = document.createElement("div");

                const audio = document.createElement("audio");
                const img = document.createElement("img");
                const species_name = document.createElement("p");
                const ds = document.createElement("details");
                const sum = document.createElement("summary");
                sum.append("Transcript");
                ds.append(sum);
                if (adata.species.indexOf("Raphus cucullatus") > -1) {
                    ds.append("do-do-do-da-do (nobody knows what a dodo sounds like. might have been this. you don't know.)")
                } else if (adata.species.indexOf("Charlie “Bird” Parker") > -1) {
                    ds.append("sublime saxophony")
                } else if (adata.species.indexOf("Thunderbird 2") > -1) {
                    ds.append("F-A-B!")
                } else {
                    ds.append(squawk(Math.ceil(Math.random() * 3 + 3)))
                }
                audio.controls = true;
                audio.src = adata.src;
                img.src = "birdimg.php?s=" + encodeURIComponent(adata.species);
                img.alt = "a bird.";
                species_name.append(adata.species);
                bird_container.append(img);
                bird_container.append(audio);
                bird_container.append(species_name);
                bird_container.append(ds);
                all_container.append(bird_container);
            })
        } catch (e) {
            console.log("Error fetching audios, so ignoring.", e);
        }
    }
    </script>

    <header>
        <h1>Which <span>Three</span> Birdies?</h1>
    </header>
    <div><main>
        <figure aria-busy="true">
            <div></div>
            <img id="card" src="img.php?lat=<?php echo $lat; ?>&amp;lon=<?php echo $lon; ?>" alt="loading your card..."
                width="800" height="540" onerror="imgfail()" onload="imgsuccess()">
        </figure>
        <div id="audios"></div>

		<nav>
		<p><a href="./">Make your own “birdday card”</a></p>
        <p><a href="details.php?lat=<?php echo $lat; ?>&amp;lon=<?php echo $lon; ?>">Learn about this birdday card</a>
        </p>
		<p><a href="about.html">Learn about <strong>Which Three Birdies?™&copy;&reg;</strong> and how it works</a></p>
		</nav>
    </main></div>
    <footer><small>Made by <a href="https://kryogenix.org/">Stuart Langridge</a> (<a
                href="https://twitter.com/sil">@sil</a>) and <a href="https://brucelawson.co.uk">Bruce Lawson</a> (<a
                href="https://twitter.com/brucel">@brucel</a>). The header drawing is by <a
                href="https://openclipart.org/detail/219787/owl-and-a-birds">Rones</a>. <br>Not many birds were harmed
            during the coding of this website (but Bruce ate a chicken  sandwich while writing the CSS). <br>Source is on Github,
            licensed under the <a
                href="https://web.archive.org/web/20140924010836/http://wiseearthpublishers.com/sites/wiseearthpublishers.com/files/PeacefulOSL.txt">Peaceful
                Open Source License</a>.</small>
    </footer>
    <script>
            document.querySelector("figure").setAttribute("aria-busy", "true");
    </script>
</body>

</html>
