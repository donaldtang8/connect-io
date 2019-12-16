<?php
require '../../config/config.php';
include("../classes/User.php");
include("../classes/Post.php");
include("../classes/Notification.php");

if (isset($_POST['profile_post-body'])) {
    $post = new Post($con, $_POST['user_from']);
    $post->submitPost($_POST['profile_post-body'], $_POST['user_to']);
}
