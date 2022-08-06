<?php 

//session_start();
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

if (!isset($_COOKIE["token"])) {
    $random = random_str();
    setcookie("token", $random, time() + (86400 * 30), "/");
    $cookie = $random;
} else {
    $cookie = $_COOKIE["token"];
}

if (sessionGet($cookie, "token") == null) {
    sessionSet($cookie, "token", $cookie); 
}

if (sessionGet($cookie, "login") == null) {
    //$_SESSION['login'] = randomName();   
    sessionSet($cookie, "login", randomName());
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

//if there is not p2, then set p2 to the userToken
$json_data = file_get_contents('games.json');
$json_data = Decrypted($json_data);
$decoded = json_decode($json_data, true);
//$token = $_SESSION['token'];
$token = sessionGet($cookie, "token");
$login = sessionGet($cookie, "login");

if ($decoded[$_GET["match"]]["player2"] == "null" && $token !== $decoded[$_GET["match"]]["player1"] && !_bot_detected()) {
    $decoded[$_GET["match"]]["player2"] = $token;
    $decoded[$_GET["match"]]["player2name"] = $login;
    $decoded[$_GET["match"]]["status"] = "swap1"; 

    $finalJson = json_encode($decoded);
    $finalJson = Encrypted($finalJson);
    $myfile = fopen("games.json", "w") or die("Unable to open file!");

    fwrite($myfile, $finalJson);
    fclose($myfile);
}

if (sessionGet($cookie, "lang") == null) {
    header("Location: launguage.php?match=" . $_GET["match"]);
    die();
}

/* 
colors: 
#E1ECF9

 
#609CE1

 
#236AB9

 
#133863

 
#091D34
*/

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

<style>


</style>

</head>
<body>


    
    <script>

    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    const token = "<?php echo $token;?>";
    const login = "<?php echo $login;?>";
    

    setInterval(function() {
       
        var match = urlParams.get('match');
        //console.log("match: " + match);
        var link = "/server.php?match="+match+"&token="+token+"&login="+login+"&lang="+"<?php echo sessionGet($cookie, "lang");?>";
        var data = httpGet(link);
        var myObj = JSON.parse(data);
        var board = myObj["board"];
        if (typeof last == 'undefined') {
            last = myObj["lastmove"];
        }

        if (myObj["lastmove"] !== last) {
            document.getElementById(myObj["lastlastmove"]).style.backgroundColor = "#E1ECF9";
            document.getElementById(myObj["lastmove"]).style.backgroundColor = "orange";
        }
        
        console.log(board);
        for (const [key, value] of Object.entries(board)) {
            console.log(key, value);
            document.getElementById(key).innerHTML = value;
        }

        var status = myObj["status"];

        document.getElementById("status").innerHTML = myObj["userStatus"];

        if (status == "choose") {
            document.getElementById("choose").style.display = "block";
        } else {
            document.getElementById("choose").style.display = "none";
        }

        console.log("status: " + status);
        if (status == "win") {
            console.log("win");
            document.getElementById("dialog").style.display = "block";
            document.getElementById("dialogText").innerHTML = "<?php echo getLangText("youWon", sessionGet($cookie, "lang"))?>";
        } else if (status == "draw") {
            console.log("draw");
            document.getElementById("dialog").style.display = "block";
            document.getElementById("dialogText").innerHTML = "<?php echo getLangText("draw", sessionGet($cookie, "lang"))?>";
        } else if (status == "lose") {
            console.log("lose");
            document.getElementById("dialog").style.display = "block";
            document.getElementById("dialogText").innerHTML = "<?php echo getLangText("youLost", sessionGet($cookie, "lang"))?>";
        } else if (status == "rematchoffered" || status == "waitingforrematch") {
            document.getElementById("dialog").style.display = "block";
        } else {
            document.getElementById("dialog").style.display = "none";
        }

        if (status == "waitingforrematch") {
            document.getElementById("rematch").innerHTML = "<?php echo getLangText("waitingForAccept", sessionGet($cookie, "lang"))?>";
        } else if (status == "rematchoffered") {
            document.getElementById("rematch").innerHTML = "<?php echo getLangText("acceptRematch", sessionGet($cookie, "lang"))?>";
        }

        /*document.getElementById("p1name").innerHTML = myObj["player1name"];
        document.getElementById("p2name").innerHTML = myObj["player2name"];*/

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

        document.getElementById("p1name").href = "/profile.php?user="+myObj["player1name"];
        if (myObj["player2name"] !== "<?php echo getLangText("waiting", sessionGet($cookie, "lang"))?>") {
            document.getElementById("p2name").href = "/profile.php?user="+myObj["player2name"];
        } 

        if (myObj["highlight"] == "1") {
            document.getElementById("p1name").style.fontWeight = "bold";
        } else {
            document.getElementById("p1name").style.fontWeight = "normal";
        }

        if (myObj["highlight"] == "2") {
            document.getElementById("p2name").style.fontWeight = "bold";
        } else {
            document.getElementById("p2name").style.fontWeight = "normal";
        }

        /*if (status == "redirect") {
            var link = myObj["redirect"];
            console.log("redirect: " + link);
        }*/

        if (typeof myObj["redirect"] === 'undefined') {} else {
            var link = myObj["redirect"];
            console.log("redirect: " + link);
            window.location.href = link;

        }

        var stats = myObj["stats"];
        for (const [key, value] of Object.entries(stats)) {
            console.log(key, value);
            document.getElementById(key).innerHTML = value;
        }


    }, 1000)

    function myFunction(x, y) {
        console.log(x, y);
        var match = urlParams.get('match');
        console.log("match: " + match);
        var link = "/server.php?x="+x+"&y="+y+"&match="+match+"&token="+token;
        var data = httpGet(link);
        var myObj = JSON.parse(data);
        var board = myObj["board"];
        /*
        console.log(board);
        for (const [key, value] of Object.entries(board)) {
            console.log(key, value);
            document.getElementById(key).innerHTML = value;
        }*/

    }

    function httpGet(theUrl)
{
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.open( "GET", theUrl, false ); // false for synchronous request
    xmlHttp.send( null );
    return xmlHttp.responseText;
}

    function chooseSymbol(symbol) {
        console.log(symbol);
        var match = urlParams.get('match');
        var link = "/server.php?symbol="+symbol+"&token="+token+"&match="+match;
        var data = httpGet(link);
    }

    function rematch() {
        var match = urlParams.get('match');
        var link = "/server.php?rematch=true"+"&token="+token+"&match="+match;
        var data = httpGet(link);
    }

    //console.log(httpGet('/server.php'));


    </script>

    <header><?php echo getLangText("name", sessionGet($cookie, "lang"))?></header>

    <div id="main">
    <?php 
    
    
    for ($i=0; $i < 15; $i++) {
        //echo $i;

        for ($j=0; $j < 15; $j++) {
            $func = "click($i, $j)";
            echo <<<END
                <div class="field" onclick="myFunction($i, $j)" id="$i/$j"> </div>
            END;
        }
        echo "<br>";
    }

    
    ?>
    </div>

    <p id="status"><?php echo getLangText("connecting", sessionGet($cookie, "lang"))?></p>
    <div id="choose">
        <?php echo getLangText("chooseYourSymbol", sessionGet($cookie, "lang"))?>
        <div class="symbolChoose" onclick="chooseSymbol('x')"> X </div>
        <div class="symbolChoose" onclick="chooseSymbol('o')"> O </div>
    </div>

    <div id="dialog">
        <p id="dialogText"><?php echo getLangText("youWon", sessionGet($cookie, "lang"))?></p>
        <div id="rematch" onclick="rematch()"><?php echo getLangText("challengeOpponentToRematch", sessionGet($cookie, "lang"))?></div>
    </div>
    

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
    <!--
    <div id="matchdata">
        <div id="player1">
            <p id="p1name">Connecting...</p>
        </div>

        <div id="player2">
            <p id="p2name">Connecting...</p>
        </div>
    </div>
    -->
</body>
</html>