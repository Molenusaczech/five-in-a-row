<?php 
error_reporting(0);
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

if (isset($_POST["lang"])) {
    sessionSet($cookie, "lang", $_POST["lang"]);

    if (isset($_GET["match"])) {
        $redirect = "/game.php?match=" . $_GET["match"];
    } else if (isset($_GET["redirect"])) {
        $redirect = $_GET["redirect"];
    } else {
        $redirect = "/index.php";
    }

    header("Location: " . $redirect);
    die();

}

// find request url

if (isset($_GET["match"])) {
    $link = "/launguage.php?match=" . $_GET["match"];
} else if (isset($_GET["redirect"])) {
    $link = "/launguage.php?redirect=" . $_GET["redirect"];
} else {
    $link = "/launguage.php";
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
    <title>Mole's Five-In-A-Row</title>
    <link rel="stylesheet" href="style.css">

    <style>
body {
    line-height: 30px;
}

.authWindow {
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-shadow: 0 0 10px #ccc;
    padding: 10px;
    position: absolute;
    top: 50%;
    left: 50%;
    margin-left: -200px;
    margin-top: -100px;
    width: 400px;
    height: 200px;
    z-index: 9999;
    font-family: 'Roboto Slab', serif;
}

.authWindow p {
    font-size: 40px;
    font-weight: bold;
}

.authWindow input {
    width: 100%;
    height: 40px;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 0 10px;
    margin-top: 10px;
}

.authWindow select {
    width: 100%;
    height: 40px;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 0 10px;
    margin-top: 10px;
}

.authWindow .error {
    color: red;
    font-size: 12px;
}

.authWindow .success {
    color: greenyellow;
    font-size: 12px;
}
</style>


</head>
<body>
<div class="authWindow"> 
    Choose your language:
    <form action="<?php echo $link; ?>" method="POST">
        <select name="lang">
            <option value="en">English</option>
            <option value="cz">Čeština</option>
        </select>
        <input type="submit" value="Submit">
    </form>
</body>
</html>