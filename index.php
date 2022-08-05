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

<style>
body {
    line-height: 30px;
}
</style>

</head>

<body>
    <header><?php echo getLangText("name", sessionGet($cookie, "lang"))?></header>

    <?php 
    if (sessionGet($cookie, "authed") == true) {
        echo "<p class='authed'>". getLangText("loggedIn", sessionGet($cookie, "lang")) ."<a href='profile.php'> " . sessionGet($cookie, "login") . "</a> <a href='logout.php'>". getLangText("logout", sessionGet($cookie, "lang")) ."</a></p> ";
    } else {
        echo "<p class='authed'>". getLangText("loggedOut", sessionGet($cookie, "lang")) .sessionGet($cookie, "login").") <a href='login.php'>". getLangText("login", sessionGet($cookie, "lang")) ."</a></p>";
    }
    ?>

    <p><?php echo getLangText("createGameLabel", sessionGet($cookie, "lang"))?></p>
    <a class="hrefbutton" href="creategame.php"><?php echo getLangText("createGame", sessionGet($cookie, "lang"))?></a>
</body>

</html>