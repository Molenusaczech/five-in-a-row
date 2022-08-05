<?php 
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

function getLangText($id, $lang) {
    $text = file_get_contents("lang/$lang.json");
    $text = json_decode($text, true);
    return $text[$id];
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

if (sessionGet($cookie, "lang") == null) {
    header("Location: launguage.php?redirect=profile.php");
    die();
}
if (sessionGet($cookie, "token") == null) {
    sessionSet($cookie, "token", $cookie); 
}

if (sessionGet($cookie, "login") == null) {
    //$_SESSION['login'] = randomName();   
    sessionSet($cookie, "login", randomName());
}

$stats = file_get_contents("stats.json");
$stats = json_decode($stats, true);

if ($_GET["user"] == "" || $_GET["user"] == null || !isset($stats[$_GET["user"]])) {
    if (sessionGet($cookie, "login") == null) {
        header("Location: index.php");
        die();
    } else {
        $user = sessionGet($cookie, "login");
    }
} else {
    $user = $_GET["user"];
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
</head>
<body>
    <header><?php echo getLangText("name", sessionGet($cookie, "lang"))?></header>
    <div id="stats">
    <h1><?php echo $user; ?> - <?php echo getLangText("userProfile", sessionGet($cookie, "lang")); ?><h1>
    <p> <?php echo getLangText("playedMatches", sessionGet($cookie, "lang")); ?>: <?php 
    if (isset($stats[$user]["plays"])) {
        echo $stats[$user]["plays"]; 
    } else {
        echo "0";
    }
    ?></p>
    <p> <?php echo getLangText("wins", sessionGet($cookie, "lang")); ?>: <?php 
    if (isset($stats[$user]["wins"])) {
        echo $stats[$user]["wins"]; 
    } else {
        echo "0";
    }
    ?></p>
    <p> <?php echo getLangText("losses", sessionGet($cookie, "lang")); ?>: <?php 
    if (isset($stats[$user]["losses"])) {
        echo $stats[$user]["losses"]; 
    } else {
        echo "0";
    }
    ?></p>
    <p> <?php echo getLangText("draws", sessionGet($cookie, "lang")); ?>: <?php 
    if (isset($stats[$user]["draws"])) {
        echo $stats[$user]["draws"]; 
    } else {
        echo "0";
    }
    ?></p>
    <p> <?php echo getLangText("winPercent", sessionGet($cookie, "lang")); ?>: <?php
    
        if ($stats[$user]["plays"] == 0) {
            echo "0";
        } else {
            echo round(($stats[$user]["wins"] / $stats[$user]["plays"]) * 100, 2);
        }
    
    
    ?>%</p>

    <h2> <?php echo getLangText("matchupStats", sessionGet($cookie, "lang")); ?> </h2>
    <table>

        <tr>
            <th><?php echo getLangText("opponent", sessionGet($cookie, "lang")); ?></th>
            <th><?php echo getLangText("wins", sessionGet($cookie, "lang")); ?></th>
            <th><?php echo getLangText("losses", sessionGet($cookie, "lang")); ?></th>
            <th><?php echo getLangText("draws", sessionGet($cookie, "lang")); ?></th>
            <th><?php echo getLangText("winPercent", sessionGet($cookie, "lang")); ?></th>
        </tr>
        <?php
        $userMatchups = $stats[$user]["matchups"];
        foreach ($userMatchups as $key => $value) {
           
                echo "<tr>";
                echo "<td><a href=/profile.php?user=" . $key . ">" . $key . "</a></td>";
                if (!isset($value["wins"])) {
                    echo "<td>0</td>";
                } else {
                    echo "<td>" . $value["wins"] . "</td>";
                }
                if (!isset($value["losses"])) {
                    echo "<td>0</td>";
                } else {
                    echo "<td>" . $value["losses"] . "</td>";
                }
                
                if (!isset($value["draws"])) {
                    echo "<td>0</td>";
                } else {
                    echo "<td>" . $value["draws"] . "</td>";
                }

                echo "<td>" . round($value["wins"] / $value["plays"] * 100, 2) . "%</td>";
                echo "</tr>";
            
        }
        ?>

    </table>
</div>

Achievments:
Coming soon

</body>

</html>