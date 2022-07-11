<?php 
require "encryptionlib.php";

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

session_start();

$userToken = session_id();
$matchid = generateRandomString(16);

$json_data = file_get_contents('games.json');
$json_data = Decrypted($json_data);
$decoded = json_decode($json_data, true);

$decoded[$matchid]["player1"] = $userToken;
$decoded[$matchid]["player2"] = "null";
$decoded[$matchid]["status"] = "waiting";
$decoded[$matchid]["board"] = array();
$decoded[$matchid]["p1symbol"] = "null";
$decoded[$matchid]["p2symbol"] = "null";

$finalJson = json_encode($decoded);
$finalJson = Encrypted($finalJson);
$myfile = fopen("games.json", "w") or die("Unable to open file!");

fwrite($myfile, $finalJson);
fclose($myfile);

header("Location: game.php?match=".$matchid);
die();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

</body>

</html>