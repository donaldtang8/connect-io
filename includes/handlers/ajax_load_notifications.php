<?php
include("../../config/config.php");
include("../classes/User.php");
include("../classes/Notification.php");

$limit = 7;     //number of notifications to load

$notification = new Notification($con, $_REQUEST['userLoggedIn']); // request param comes from ajax call
echo $notification->getNotificationsDropdown($_REQUEST, $limit);
