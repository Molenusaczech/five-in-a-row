<?php 

function Decrypted($text) {
    $key = getenv('key');
    $string = $text;
    $pass = $key;
    $method = 'aes128';
    return openssl_decrypt($string, $method, $pass);
}

function Encrypted($text) {
    $key = getenv('key');
    $string = $text;
    $pass = $key;
    $method = 'aes128';
    return openssl_encrypt($string, $method, $pass);

}
//session_start();
error_reporting(0);

//session_reset();
//session_unset();
$cookie = $_COOKIE["token"];

$sessiondata = file_get_contents("sessions.json");
$sessiondata = Decrypted($sessiondata);
$sessiondata = json_decode($sessiondata, true);
unset($sessiondata[$cookie]);
//$sessiondata = json_encode($sessiondata);
// $sessiondata = Encrypted($sessiondata);
//file_put_contents("sessions.json", $sessiondata);

$finalJson = json_encode($sessiondata);
$finalJson = Encrypted($finalJson);
$myfile = fopen("sessions.json", "w") or die("Unable to open file!");

fwrite($myfile, $finalJson);
fclose($myfile);

header("Location: index.php");
die();

?>
