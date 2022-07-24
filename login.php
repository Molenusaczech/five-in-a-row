<?php 

error_reporting(0);
session_start();

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

if($_SESSION['authed'] == true) {
    header("Location: index.php");
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
        $response = "<p class='error'>Password is incorrect</p>";
    } else {
        // logged in
        //session_id($decoded[$login]['token']);
        $_SESSION['token'] = $decoded[$login]['token'];
        $_SESSION['login'] = $login;
        $_SESSION['authed'] = true;
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
            <input type="text" name="login" placeholder="Username"> <br>
            <input type="password" name="password" placeholder="Password"> <br>
            <input type="submit" value="Log in">
        </form>

        <a href="register.php">Not registered yet?</a>
        <?php echo $response; ?>

    </div>

</body>

</html>