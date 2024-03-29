<?php 

error_reporting(0);
//session_start();

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

function getLangText($id, $lang) {
    $text = file_get_contents("lang/$lang.json");
    $text = json_decode($text, true);
    return $text[$id];
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


if(sessionGet($cookie, "authed") == true) {
    header("Location: index.php");
    die();
}

if (sessionGet($cookie, "lang") == null) {
    header("Location: launguage.php?redirect=login.php");
    die();
}

if ($_POST['login'] !== "" && $_POST['login'] !== null && $_POST['password'] !== "" && $_POST['password'] !== null) {

    $login = $_POST['login'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $email = $_POST['email'];

    $json_data = file_get_contents('users.json');
    $json_data = Decrypted($json_data);
    $decoded = json_decode($json_data, true);
  
    if (!isset($decoded[$login])) {
        $response = "<p class='error'> </p>";
    } else if ($decoded[$login]['password'] != hash("sha256", $password)) {
        $response = "<p class='error'>". getLangText("wrongPassword", sessionGet($cookie, "lang")) ."</p>";
    } else {
        // logged in
        //session_id($decoded[$login]['token']);
        /*
        $_SESSION['token'] = $decoded[$login]['token'];
        $_SESSION['login'] = $login;
        $_SESSION['authed'] = true;*/
        sessionSet($cookie, "token", $decoded[$login]['token']);	
        sessionSet($cookie, "login", $login);
        sessionSet($cookie, "authed", true);
        header("Location: index.php");
        die();
    }
        
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
        <form action="login.php" method="post">
            <input type="text" name="login" placeholder="<?php echo getLangText("username", sessionGet($cookie, "lang"))?>"> <br>
            <input type="password" name="password" placeholder="<?php echo getLangText("password", sessionGet($cookie, "lang"))?>"> <br>
            <input type="submit" value="<?php echo getLangText("loginButton", sessionGet($cookie, "lang"))?>">
        </form>

        <a href="register.php"><?php echo getLangText("notRegisteredYet", sessionGet($cookie, "lang"))?></a>
        <?php echo $response; ?>

    </div>

</body>

</html>