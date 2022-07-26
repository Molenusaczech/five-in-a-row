<?php 

session_start();
error_reporting(0);

function randomName() {
    $adjectives = ["Awesome", "Epic", "Great", "Tryhard", "Lame", "Cool", "Lame", "Loud", "Anonymous"];
    $names = ["Guy", "Dude", "Bro", "Beast", "Mole", "Rabbit", "Human", "Person", "Turtle"];
    $randomNumber = rand(1000, 9999);
    return $adjectives[rand(0, count($adjectives) - 1)] . $names[rand(0, count($names) - 1)] . $randomNumber;
}

if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = session_id();   
}

if (!isset($_SESSION['login'])) {
    $_SESSION['login'] = randomName();   
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

if ($_GET["match"] == "" or $_GET["match"] == null) {
    header("Location: index.php");
    die();
}

//if there is not p2, then set p2 to the userToken
$json_data = file_get_contents('games.json');
$json_data = Decrypted($json_data);
$decoded = json_decode($json_data, true);
$token = $_SESSION['token'];

if ($decoded[$_GET["match"]]["player2"] == "null" && $token !== $decoded[$_GET["match"]]["player1"])  {
    $decoded[$_GET["match"]]["player2"] = $token;
    $decoded[$_GET["match"]]["player2name"] = $_SESSION['login'];
    $decoded[$_GET["match"]]["status"] = "swap1"; 

    $finalJson = json_encode($decoded);
    $finalJson = Encrypted($finalJson);
    $myfile = fopen("games.json", "w") or die("Unable to open file!");

    fwrite($myfile, $finalJson);
    fclose($myfile);
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
    const token = "<?php echo $_SESSION['token'];?>";
    const login = "<?php echo $_SESSION['login'];?>";
    

    setInterval(function() {
       
        var match = urlParams.get('match');
        //console.log("match: " + match);
        var link = "/server.php?match="+match+"&token="+token+"&login="+login;
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
            document.getElementById("dialogText").innerHTML = "You win!";
        } else if (status == "draw") {
            console.log("draw");
            document.getElementById("dialog").style.display = "block";
            document.getElementById("dialogText").innerHTML = "Draw!";
        } else if (status == "lose") {
            console.log("lose");
            document.getElementById("dialog").style.display = "block";
            document.getElementById("dialogText").innerHTML = "You lose!";
        } else if (status == "rematchoffered" || status == "waitingforrematch") {
            document.getElementById("dialog").style.display = "block";
        } else {
            document.getElementById("dialog").style.display = "none";
        }

        if (status == "waitingforrematch") {
            document.getElementById("rematch").innerHTML = "Waiting for rematch!";
        } else if (status == "rematchoffered") {
            document.getElementById("rematch").innerHTML = "Click to accept rematch!";
        }

        document.getElementById("p1name").innerHTML = myObj["player1name"];
        document.getElementById("p2name").innerHTML = myObj["player2name"];

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
        
        console.log(board);
        for (const [key, value] of Object.entries(board)) {
            console.log(key, value);
            document.getElementById(key).innerHTML = value;
        }

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

    <header>Mole's Five-In-Row</header>

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

    <p id="status">Connecting</p>
    <div id="choose">
        Choose your symbol:
        <div class="symbolChoose" onclick="chooseSymbol('x')"> X </div>
        <div class="symbolChoose" onclick="chooseSymbol('o')"> O </div>
    </div>

    <div id="dialog">
        <p id="dialogText">You won!</p>
        <div id="rematch" onclick="rematch()">Challenge opponent to rematch</div>
    </div>
    

    <table id="matchdata">
        <tr>
            <td id="p1name">Connecting...</td>
            <td class="mid"> vs </td>
            <td id="p2name">Connecting...</td>
        </tr>

        <tr>
            <td id="matches1">Connecting...</td>
            <td class="mid">Matches</td>
            <td id="matches2">Connecting...</td>
        </tr>

        <tr>
            <td id="wins1">Connecting...</td>
            <td class="mid">Wins</td>
            <td id="wins2">Connecting...</td>
        </tr>

        <tr>
            <td id="loses1">Connecting...</td>
            <td class="mid">Loses</td>
            <td id="loses2">Connecting...</td>
        </tr>

        <tr>
            <td id="draws1">Connecting...</td>
            <td class="mid">Draws</td>
            <td id="draws2">Connecting...</td>
        </tr>

        <tr>
            <td id="winp1">Connecting...</td>
            <td class="mid">Win %</td>
            <td id="winp2">Connecting...</td>
        </tr>

        <tr>
            <td class="statTitle" colspan="3">Matchup Stats</td>
        </tr>

        <tr>
            <td id="matchwins1">Connecting...</td>
            <td class="mid">Wins</td>
            <td id="matchwins2">Connecting...</td>
        </tr>

        <tr>
            <td id="matchloses1">Connecting...</td>
            <td class="mid">Loses</td>
            <td id="matchloses2">Connecting...</td>
        </tr>

        <tr>
            <td id="matchdraw1">Connecting...</td>
            <td class="mid">Draws</td>
            <td id="matchdraw2">Connecting...</td>
        </tr>

        <tr>
            <td id="matchwinp1">Connecting...</td>
            <td class="mid">Win %</td>
            <td id="matchwinp2">Connecting...</td>
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