<?php 
session_start();
error_reporting(0);

if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = session_id();   
}

function randomName() {
    $adjectives = ["Awesome", "Epic", "Great", "Tryhard", "Lame", "Cool", "Lame", "Loud", "Anonymous"];
    $names = ["Guy", "Dude", "Bro", "Beast", "Mole", "Rabbit", "Human", "Person", "Turtle"];
    $randomNumber = rand(1000, 9999);
    return $adjectives[rand(0, count($adjectives) - 1)] . $names[rand(0, count($names) - 1)] . $randomNumber;
}


if (!isset($_SESSION['login'])) {
    $_SESSION['login'] = randomName();   
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
    <header>Mole's Five-In-Row</header>

    <?php 
    if ($_SESSION["authed"] == true) {
        echo "<p class='authed'>You are logged in as " . $_SESSION["login"] . " <a href='logout.php'>Logout</a></p> ";
    } else {
        echo "<p class='authed'>You are not logged in (Guest Username: ".$_SESSION['login'].") <a href='login.php'>Login</a></p>";
    }
    ?>

    <p>Click to create a private game, send your friend a link!</p>
    <a class="hrefbutton" href="creategame.php">Create Game</a>
</body>

</html>