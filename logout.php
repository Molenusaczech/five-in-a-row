<?php 
session_start();
error_reporting(0);

//session_reset();
session_unset();

header("Location: index.php");
die();

?>
