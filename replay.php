<?php 

error_reporting(0);

function randomName() {
    $adjectives = ["Awesome", "Epic", "Great", "Tryhard", "Lame", "Cool", "Lame", "Loud", "Anonymous"];
    $names = ["Guy", "Dude", "Bro", "Beast", "Mole", "Rabbit", "Human", "Person", "Turtle"];
    $randomNumber = rand(1000, 9999);
    return $adjectives[rand(0, count($adjectives) - 1)] . $names[rand(0, count($names) - 1)] . $randomNumber;
}

function random_str(
    int $length = 64,
    string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
): string {
    if ($length < 1) {
        throw new \RangeException("Length must be a positive integer");
    }
    $pieces = [];
    $max = strlen($keyspace) - 1;
    for ($i = 0; $i < $length; ++$i) {
        $pieces []= $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
}

function sessionGet($token, $key) {
    $sessiondata = file_get_contents("sessions.json");
    $sessiondata = Decrypted($sessiondata);
    $sessiondata = json_decode($sessiondata, true);
    if (isset($sessiondata[$token][$key])) {
        return $sessiondata[$token][$key];
    } else {
        return null;
    }
    //return $sessiondata[$token][$key];
}

function sessionSet($token, $key, $value) {
    $sessiondata = file_get_contents("sessions.json");
    $sessiondata = Decrypted($sessiondata);
    $sessiondata = json_decode($sessiondata, true);
    $sessiondata[$token][$key] = $value;
    //$sessiondata = json_encode($sessiondata);
    // $sessiondata = Encrypted($sessiondata);
    //file_put_contents("sessions.json", $sessiondata);

    $finalJson = json_encode($sessiondata);
    $finalJson = Encrypted($finalJson);
    $myfile = fopen("sessions.json", "w") or die("Unable to open file!");

    fwrite($myfile, $finalJson);
    fclose($myfile);
}


function Encrypted($text) {
    $key = getenv('key');
    $string = $text;
    $pass = $key;
    $method = 'aes128';
    return openssl_encrypt($string, $method, $pass);

}

function Decrypted($text) {
    $key = getenv('key');
    $string = $text;
    $pass = $key;
    $method = 'aes128';
    return openssl_decrypt($string, $method, $pass);
}

function _bot_detected() {

    return (
      isset($_SERVER['HTTP_USER_AGENT'])
      && preg_match('/bot|crawl|slurp|spider|mediapartners/i', $_SERVER['HTTP_USER_AGENT'])
    );
}

function getLangText($id, $lang) {
    $text = file_get_contents("lang/$lang.json");
    $text = json_decode($text, true);
    return $text[$id];
}

if ($_GET["match"] == "" or $_GET["match"] == null) {
    header("Location: index.php");
    die();
}

if (!isset($_COOKIE["token"])) {
    $random = random_str();
    setcookie("token", $random, time() + (86400 * 30), "/");
    $cookie = $random;
} else {
    $cookie = $_COOKIE["token"];
}


if (sessionGet($cookie, "lang") == null) {
    header("Location: launguage.php?replay=" . $_GET["match"]);
    die();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <title>Mole's Five-In-a-Row</title>

    <?php 
    if (sessionGet($cookie, "theme") == "dark") {
        echo '<link rel="stylesheet" href="themes/dark.css">';
    }
    ?>

</head>
<body>
<header>
        <?php echo getLangText("name", sessionGet($cookie, "lang"))?>
        <a href="index.php"><?php echo getLangText("home", sessionGet($cookie, "lang"))?></a>
        <a href="mygames.php"><?php echo getLangText("myGames", sessionGet($cookie, "lang"))?></a>
        <a href="profile.php"><?php echo getLangText("myProfile", sessionGet($cookie, "lang"))?></a>
        <a href="settings.php"><?php echo getLangText("settings", sessionGet($cookie, "lang"))?></a>
    </header>



<div id="main">
<?php 


for ($i=0; $i < 15; $i++) {
    //echo $i;

    for ($j=0; $j < 15; $j++) {
        $func = "click($i, $j)";
        echo <<<END
            <div class="field" id="$i/$j"> </div>
        END;
    }
    echo "<br>";
}


?>
</div>

<div id="replaycontrols"> 
    <?php echo getLangText("replayControls", sessionGet($cookie, "lang"))?> <br>
    <p id="turn"><?php echo getLangText("turn", sessionGet($cookie, "lang"))?>: 0/0</p>
    <span class="replayButton" onclick="toStart()">⏮️</span>
    <span class="replayButton" onclick="back()">⏪</span>
    <span class="replayButton" id="toggleplay" onclick="togglePlay()">▶️</span>
    <span class="replayButton" onclick="forward()">⏩</span>
    <span class="replayButton" onclick="toEnd()">⏭️</span>
</div> <br>






<table id="matchdata">
    <tr>
        <td><a id="p1name" target="_blank"><?php echo getLangText("connecting", sessionGet($cookie, "lang"))?></a></td>
        <td class="mid"> vs </td>
        <td><a id="p2name" target="_blank"><?php echo getLangText("connecting", sessionGet($cookie, "lang"))?></a></td>
    </tr>

    <tr>
        <td id="matches1"><?php echo getLangText("connecting", sessionGet($cookie, "lang"))?></td>
        <td class="mid"><?php echo getLangText("matches", sessionGet($cookie, "lang"))?></td>
        <td id="matches2"><?php echo getLangText("connecting", sessionGet($cookie, "lang"))?></td>
    </tr>

    <tr>
        <td id="wins1"><?php echo getLangText("connecting", sessionGet($cookie, "lang"))?></td>
        <td class="mid"><?php echo getLangText("wins", sessionGet($cookie, "lang"))?></td>
        <td id="wins2"><?php echo getLangText("connecting", sessionGet($cookie, "lang"))?></td>
    </tr>

    <tr>
        <td id="loses1"><?php echo getLangText("connecting", sessionGet($cookie, "lang"))?></td>
        <td class="mid"><?php echo getLangText("losses", sessionGet($cookie, "lang"))?></td>
        <td id="loses2"><?php echo getLangText("connecting", sessionGet($cookie, "lang"))?></td>
    </tr>

    <tr>
        <td id="draws1"><?php echo getLangText("connecting", sessionGet($cookie, "lang"))?></td>
        <td class="mid"><?php echo getLangText("draws", sessionGet($cookie, "lang"))?></td>
        <td id="draws2"><?php echo getLangText("connecting", sessionGet($cookie, "lang"))?></td>
    </tr>

    <tr>
        <td id="winp1"><?php echo getLangText("connecting", sessionGet($cookie, "lang"))?></td>
        <td class="mid"><?php echo getLangText("winPercent", sessionGet($cookie, "lang"))?></td>
        <td id="winp2"><?php echo getLangText("connecting", sessionGet($cookie, "lang"))?></td>
    </tr>

    <tr>
        <td class="statTitle" colspan="3"><?php echo getLangText("matchupStats", sessionGet($cookie, "lang"))?></td>
    </tr>

    <tr>
        <td id="matchwins1"><?php echo getLangText("connecting", sessionGet($cookie, "lang"))?></td>
        <td class="mid"><?php echo getLangText("wins", sessionGet($cookie, "lang"))?></td>
        <td id="matchwins2"><?php echo getLangText("connecting", sessionGet($cookie, "lang"))?></td>
    </tr>

    <tr>
        <td id="matchloses1"><?php echo getLangText("connecting", sessionGet($cookie, "lang"))?></td>
        <td class="mid"><?php echo getLangText("losses", sessionGet($cookie, "lang"))?></td>
        <td id="matchloses2"><?php echo getLangText("connecting", sessionGet($cookie, "lang"))?></td>
    </tr>

    <tr>
        <td id="matchdraw1"><?php echo getLangText("connecting", sessionGet($cookie, "lang"))?></td>
        <td class="mid"><?php echo getLangText("draws", sessionGet($cookie, "lang"))?></td>
        <td id="matchdraw2"><?php echo getLangText("connecting", sessionGet($cookie, "lang"))?></td>
    </tr>

    <tr>
        <td id="matchwinp1"><?php echo getLangText("connecting", sessionGet($cookie, "lang"))?></td>
        <td class="mid"><?php echo getLangText("winPercent", sessionGet($cookie, "lang"))?></td>
        <td id="matchwinp2"><?php echo getLangText("connecting", sessionGet($cookie, "lang"))?></td>
    </tr>

</table>

<script> 

function httpGet(theUrl)
{
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.open( "GET", theUrl, false ); // false for synchronous request
    xmlHttp.send( null );
    return xmlHttp.responseText;
}



var match = "<?php echo $_GET["match"] ?>";
var link = "/server.php?match="+match;
var data = httpGet(link);
var myObj = JSON.parse(data);
var board = myObj["board"];
var turn = 0;
var maxTurn = Object.keys(board).length;
var auto = 0;
//document.getElementById("turn").innerHTML = "Turn: " + turn + "/" + maxTurn;

var stats = myObj["stats"];
for (const [key, value] of Object.entries(stats)) {
    console.log(key, value);
    document.getElementById(key).innerHTML = value;
}

if (myObj["p1symbol"] == "null") {
            document.getElementById("p1name").innerHTML = myObj["player1name"];
        } else {
            document.getElementById("p1name").innerHTML = myObj["player1name"] + " (" + myObj["p1symbol"] + ")";
        }

        if (myObj["p2symbol"] == "null") {
            document.getElementById("p2name").innerHTML = myObj["player2name"];
        } else {
            document.getElementById("p2name").innerHTML = myObj["player2name"] + " (" + myObj["p2symbol"] + ")";
        }

function update() {
    document.getElementById("turn").innerHTML = "<?php echo getLangText("turn", sessionGet($cookie, "lang"))?>: " + turn + "/" + maxTurn;

    // reset board
    for (let i = 0; i < 15; i++) {
        for (let j = 0; j < 15; j++) {
            document.getElementById(i+"/"+j).innerHTML = " ";
        }
    }


    var index = 1;
    for (const [key, value] of Object.entries(board)) {
        if (index <= turn) {
            //console.log(key, value);
            document.getElementById(key).innerHTML = value;
        }
        index++;
        
    } }

function forward() {
    if (turn < maxTurn) {
        turn++;
        update();
    } else if (auto == 1) {
        togglePlay();
    }
}

function back() {
    if (turn > 0) {
        turn--;
        update();
    }
}

function toStart() {
    turn = 0;
    update();
}

function toEnd() {
    turn = maxTurn;
    update();
}

function togglePlay() {
    if (auto == 0) {

        auto = 1;
        autoplay = setInterval(forward, 1000);
        document.getElementById("toggleplay").innerHTML = "⏸️";
        
    } else {
        auto = 0;
        clearInterval(autoplay);
        document.getElementById("toggleplay").innerHTML = "▶️";
    }
}

</script>

</body>
</html>