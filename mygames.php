<?php 
//session_start();
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
    //$sessiondata = Encrypted($sessiondata);
    //file_put_contents("sessions.json", $sessiondata);

    $finalJson = json_encode($sessiondata);
    $finalJson = Encrypted($finalJson);
    $myfile = fopen("sessions.json", "w") or die("Unable to open file!");

    fwrite($myfile, $finalJson);
    fclose($myfile);
}
/*
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = session_id();   
}*/

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

function randomName() {
    $adjectives = ["Awesome", "Epic", "Great", "Tryhard", "Lame", "Cool", "Lame", "Loud", "Anonymous"];
    $names = ["Guy", "Dude", "Bro", "Beast", "Mole", "Rabbit", "Human", "Person", "Turtle"];
    $randomNumber = rand(1000, 9999);
    return $adjectives[rand(0, count($adjectives) - 1)] . $names[rand(0, count($names) - 1)] . $randomNumber;
}

function getLangText($id, $lang) {
    $text = file_get_contents("lang/$lang.json");
    $text = json_decode($text, true);
    return $text[$id];
}

/*
if (!isset($_SESSION['login'])) {
    $_SESSION['login'] = randomName();   
}*/

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

if (sessionGet($cookie, "lang") == null) {
    header("Location: launguage.php");
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
    <title>Mole's Five-In-a-Row</title>
    <link rel="stylesheet" href="style.css">

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
    
<table id="mygames"> 

    <tr> 
        <th><?php echo getLangText("gameId", sessionGet($cookie, "lang"))?></th> 
        <th><?php echo getLangText("status", sessionGet($cookie, "lang"))?></th> 
        <th><?php echo getLangText("opponent", sessionGet($cookie, "lang"))?></th> 
    </tr>

    <?php 
    
    $games = file_get_contents("games.json");
    $games = Decrypted($games);
    $games = json_decode($games, true);

    foreach ($games as $key => $value) {
        $login = sessionGet($cookie, "login");
        
        if ($value["player1name"] == $login || $value["player2name"] == $login) {
            $id = $key;
            $status = $value["status"];
            if ($value["player1name"] == $login) {
                $opponent = $value["player2name"];
                $player = 1;
            } else {
                $opponent = $value["player1name"];
                $player = 2;
            }

            if ($status == "turn1") {
                if ($player == 1) {
                    $status = getLangText("yourTurn", sessionGet($cookie, "lang"));
                    $class = "badge badge-blue";
                } else {
                    $status = getLangText("opponentTurn", sessionGet($cookie, "lang"));
                    $class = "badge badge-yellow";
                }
            }

            if ($status == "turn2") {
                if ($player == 2) {
                    $status = getLangText("yourTurn", sessionGet($cookie, "lang"));
                    $class = "badge badge-blue";
                } else {
                    $status = getLangText("opponentTurn", sessionGet($cookie, "lang"));
                    $class = "badge badge-yellow";
                }
            }
            
            if ($status == "redirect" || $status == "win1" || $status == "win2" || $status == "draw" || $status == "rematch1" || $status == "rematch2") {
                $redirect = "replay.php?match=$id";
            } else {
                $redirect = "game.php?match=$id";
            }

            if ($status == "win1" || $value["winner"] == "win1") {
                if ($player == 1) {
                    $status = getLangText("youWon", sessionGet($cookie, "lang"));
                    $class = "badge badge-green";
                } else {
                    $status = getLangText("youLost", sessionGet($cookie, "lang"));
                    $class = "badge badge-red";
                }
            } else if ($status == "win2" || $value["winner"] == "win2") {
                if ($player == 2) {
                    $status = getLangText("youWon", sessionGet($cookie, "lang"));
                    $class = "badge badge-green";
                } else {
                    $status = getLangText("youLost", sessionGet($cookie, "lang"));
                    $class = "badge badge-red";
                }
            } else if ($status == "draw" || $value["winner"] == "draw") {
                $status = getLangText("draw", sessionGet($cookie, "lang"));
                $class = "badge badge-grey";
            } else if ($status == "redirect") {
                $status = getLangText("gameEnded", sessionGet($cookie, "lang"));
                $class = "badge badge-grey";
            }

            if ($status == "waiting") {
                $status = getLangText("waitingForOpponent", sessionGet($cookie, "lang"));
                $class = "badge badge-grey";
            }

            if ($status == "swap1" || $status == "swap2" || $status == "swap3") {
                if ($player == 1) {
                    $status = getLangText("waitingForSwap", sessionGet($cookie, "lang"));
                    $class = "badge badge-yellow";
                } else {
                    $status = getLangText("waitingForYourSwap", sessionGet($cookie, "lang"));
                    $class = "badge badge-blue";
                }
            }

            if ($status == "choose") {
                if ($player == 1) {
                    $status = getLangText("waitingForChoose", sessionGet($cookie, "lang"));
                    $class = "badge badge-yellow";
                } else {
                    $status = getLangText("waitingForYourSymbol", sessionGet($cookie, "lang"));
                    $class = "badge badge-blue";
                }
            }

            echo <<<END
            <tr> 
                <td><a href="$redirect">$id</a></td> 
                <td><span class="$class">$status</span></td> 
                <td><a href="/profile.php?user=$opponent">$opponent</a></td>
            </tr>
            END;
        }
    }
    
    ?>
</table>

</body>
</html>