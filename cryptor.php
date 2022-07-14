<?php 

error_reporting(0);

$key = getenv('key');

if ($_POST["method"] == "encrypt") {
    $string = file_get_contents("games.json");
    $pass = $key;
    $method = 'aes128';

    $encrypted = openssl_encrypt($string, $method, $pass);

    $myfile = fopen("games.json", "w") or die("Unable to open file!");
    fwrite($myfile, $encrypted);
    fclose($myfile);

} 

if ($_POST["method"] == "decrypt") {
    $string = file_get_contents("games.json");
    $pass = $key;
    $method = 'aes128';

    $decrypted = openssl_decrypt($string, $method, $pass);

    $myfile = fopen("games.json", "w") or die("Unable to open file!");
    fwrite($myfile, $decrypted);
    fclose($myfile);

} 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>

    <form action="cryptor.php" method="post">
        <input type="hidden" name="method" value="encrypt">
        <input type="submit" value="Encrypt">
    </form>
    
    <form action="cryptor.php" method="post">
        <input type="hidden" name="method" value="decrypt">
        <input type="submit" value="Decrypt">
    </form>

</body>
</html>