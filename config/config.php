<?php
ob_start(); // turns on output buffering
session_start();
$timezone = date_default_timezone_set("America/New_York");
// mysqli_connect is a function that will connect the php file to the database
// it takes 4 parameters - host, username, password, and database name
$con = mysqli_connect("localhost", "root", "", "social");

if (mysqli_connect_errno()) {
    echo "Failed to connect: " . mysqli_connect_errno();
}
?>