<?php

require "functions.php";

list($lat, $lon, $data_cache_key) = validate();

$data = get_cache_key($data_cache_key);
if (!$data) {
    echo "That card has never been generated.";
    die();
}
$data = json_decode($data, true);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Birdday Card details</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
        integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
        crossorigin="" />
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Luckiest+Guy&family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
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

    img+p,
    dt {
        margin-top: 1em;
    }
    </style>
</head>

<body>
    <header>
        <h1>Birdday Card details</h1>
    </header>
    <div>
        <main>
            <img id="card" src="img.php?lat=<?php echo $lat; ?>&amp;lon=<?php echo $lon; ?>" alt="loading your card...">

            <p>This card was constructed from data taken from various sources.</p>

            <dl>
                <dt>Location:</dt>
                <dd><?php echo $data["location"][0]; ?></dd>
                <dd>provided by <a href="https://51degrees.com">51Degrees</a></dd>
                <dt>Location imagery:</dt>
                <dd><a href="<?php echo $data["town"]; ?>">Local image</a></dd>
                <dd>provided by <a href="https://wikidata.org">Wikidata</a></dd>
                <dt>Local birds:</dt>
                <dd>
                    <?php if (count($data["birds"]) > 0) { ?>
                    <a href='<?php echo $data["birds"][0]["wikidata"]; ?>'><?php
            echo htmlspecialchars($data["birds"][0]["species"]); ?></a>,
                    <?php } ?>
                    <?php if (count($data["birds"]) > 1) { ?>
                    <a href='<?php echo $data["birds"][1]["wikidata"]; ?>'><?php
            echo htmlspecialchars($data["birds"][1]["species"]); ?></a>,
                    <?php } ?>
                    <?php if (count($data["birds"]) > 2) { ?>
                    <a href='<?php echo $data["birds"][2]["wikidata"]; ?>'><?php
            echo htmlspecialchars($data["birds"][2]["species"]); ?></a>
                    <?php } ?>
                    <?php if (count($data["birds"]) == 0) { ?>
                        We couldn't find any local birds. Sorry!
                    <?php } ?>
                </dd>
                <dd>
                    <?php if (count($data["birds"]) > 0 && $data["birds"][0]["species"] == "Raphus cucullatus (dodo)") {
                        echo " (that is, we couldn't find any local birds. Sorry!)";
                    } else { ?>
                    provided by <a href="<?php echo $data["xenourl"]; ?>">Xeno Canto</a>
                    <?php } ?>
                </dd>
            </dl>
            <nav>
                <p><a href="birdday-card.php?lat=<?php echo $lat; ?>&amp;lon=<?php echo $lon; ?>">Show this birdday
                        card</a>
                </p>
                <p><a href="about.html">Learn about the birdday card app</a></p>
            </nav>
        </main>
    </div>
    <footer><p><small>Made by <a href="https://kryogenix.org/">Stuart Langridge</a> (<a
                href="https://twitter.com/sil">@sil</a>) and <a href="https://brucelawson.co.uk">Bruce Lawson</a> (<a
                href="https://twitter.com/brucel">@brucel</a>).</small><p><small>The header drawing is by <a
                href="https://openclipart.org/detail/219787/owl-and-a-birds">Rones</a>.</small></p>Not many birds were harmed
            during the coding of this website (but Bruce ate a chicken sandwich while writing the CSS). <br>Source is on
            Github,
            licensed under the <a
                href="https://web.archive.org/web/20140924010836/http://wiseearthpublishers.com/sites/wiseearthpublishers.com/files/PeacefulOSL.txt">Peaceful
                Open Source License</a>.</small>
    </footer>
</body>

</html>
