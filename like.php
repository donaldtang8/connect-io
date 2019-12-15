<html>

<head>
    <title></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.css" integrity="sha256-PF6MatZtiJ8/c9O9HQ8uSUXr++R9KBYu4gbNG5511WE=" crossorigin="anonymous" />
    <link rel="stylesheet" type="text/css" href="assets/css/layout.css">
</head>

<body class="post_item-likeIFrameBody">
    <?php
    require 'config/config.php';
    include("includes/classes/User.php");
    include("includes/classes/Post.php");

    if (isset($_SESSION['username'])) {
        $userLoggedIn = $_SESSION['username'];
        $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$userLoggedIn'");
        $user = mysqli_fetch_array($user_details_query);
    } else {
        header("Location: register.php");
    }

    //Get id of post
    if (isset($_GET['post_id'])) {
        $post_id = $_GET['post_id'];
    }

    $get_likes = mysqli_query($con, "SELECT likes, added_by FROM posts WHERE id='$post_id'");
    $row = mysqli_fetch_array($get_likes);
    $total_likes = $row['likes'];
    $user_liked = $row['added_by'];

    $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$user_liked'");
    $row = mysqli_fetch_array($user_details_query);
    $total_user_likes = $row['num_likes'];

    // like button
    if (isset($_POST['like_button'])) {
        $total_likes++;
        // update posts table
        $query = mysqli_query($con, "UPDATE posts SET likes='$total_likes' WHERE id='$post_id'");
        $total_user_likes++;
        // update users table
        $user_likes = mysqli_query($con, "UPDATE users SET num_likes='$total_user_likes' WHERE username='$user_liked'");
        // update likes table
        $insert_user = mysqli_query($con, "INSERT INTO likes VALUES('', '$userLoggedIn', '$post_id')");

        // insert notification
    }

    // dislike button
    if (isset($_POST['unlike_button'])) {
        $total_likes--;
        $query = mysqli_query($con, "UPDATE posts SET likes='$total_likes' WHERE id='$post_id'");
        $total_user_likes--;
        $user_likes = mysqli_query($con, "UPDATE users SET num_likes='$total_user_likes' WHERE username='$user_liked'");
        $insert_user = mysqli_query($con, "DELETE FROM likes WHERE username='$userLoggedIn'");

        // insert notification
    }

    // check for previous likes
    $check_query = mysqli_query($con, "SELECT * FROM likes WHERE username='$userLoggedIn' AND post_id='$post_id'");
    $num_rows = mysqli_num_rows($check_query);

    if ($num_rows > 0) {
        echo '<form class="post_like" action="like.php?post_id=' . $post_id . '" method="POST">
        <button type="submit" class="post_like-btn" name="unlike_button"><i class="fas fa-heart"></i><div class="post_item-numLikes">' . $total_likes . '</div></button>
        </form>
            ';
    } else {
        echo '<form class="post_like" action="like.php?post_id=' . $post_id . '" method="POST">
        <button type="submit" class="post_like-btn" name="like_button"><i class="fas fa-heart"></i><div class="post_item-numLikes">' . $total_likes . '</div></button>
        </form>
    ';
    }
    ?>


</body>

</html>