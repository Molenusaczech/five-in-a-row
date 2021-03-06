<?php 

session_start();
error_reporting(0);

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
$token = session_id();

if ($decoded[$_GET["match"]]["player2"] == "null" && $token !== $decoded[$_GET["match"]]["player1"])  {
    $decoded[$_GET["match"]]["player2"] = $token;
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
    <title>Document</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    line-height: 50px;
}

.field {
    width: 60px;
    height: 60px;
    background-color: #E1ECF9;
    border: 1px solid #091D34;
    float: left;
}

.field:hover {
    background-color: yellow;
}

.field {
    font-size: 40px;
    text-align: center;
    vertical-align: middle;
}

#choose {
    width: 200px;
    height: 125px;
    font-size: 20px;
    text-align: center;
    vertical-align: middle;
    background-color: #609CE1;
    border: 1px solid #091D34;
    display: none;
    font-family: 'Roboto Slab', serif;
    float: left;
    color: #091D34;
}

.symbolChoose {
    border: 1px solid #091D34;
    width: 40%;
    height: 45px;
    text-align: center;
    vertical-align: middle;
    float: left;
    margin-left: 7%;
    font-size: 40px;
    line-height: normal;
    font-family: 'Roboto Slab', serif;
    color: #091D34;
}

.symbolChoose:hover {
    background-color: yellow;
}

#dialog {
    width: 200px;
    height: 125px;
    font-size: 20px;
    text-align: center;
    vertical-align: middle;
    background-color: #ccc;
    border: 1px solid #000;
    display: none;
    position: absolute;
    left: 45%;
    top: 45%;
    z-index: 100;
    font-family: 'Roboto Slab', serif;
    
}

#main {
    float: left;
    
    /*top: 10%;
    left: 22vw;*/

    
    line-height: 60px;

}

header {
    width: 100%;
    height: 50px;
    background-color: #609CE1;
    float: left;
    text-align: center;
    vertical-align: middle;
    color: #133863;
    font-family: 'Roboto Slab', serif;
    font-size: 30px;
}

#status {
    color: #133863;
    font-family: 'Roboto Slab', serif;
    font-size: 30px;
}

#rematch {
    border: 1px solid #091D34;
    font-size: 20px;
    line-height: normal;
    background-color: white;
    height: 50px;
}

#rematch:hover {
    background-color: yellow;
}

</style>

</head>
<body>


    
    <script>

    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    const token = "<?php echo session_id();?>";
    
    

    setInterval(function() {
       
        var match = urlParams.get('match');
        //console.log("match: " + match);
        var link = "/server.php?match="+match+"&token="+token;
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

        /*if (status == "redirect") {
            var link = myObj["redirect"];
            console.log("redirect: " + link);
        }*/

        if (typeof myObj["redirect"] === 'undefined') {} else {
            var link = myObj["redirect"];
            console.log("redirect: " + link);
            window.location.href = link;

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

</body>
</html>